<?php

namespace App\Controllers\auth;

use App\Models\SupportMessageModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class SuspendedController extends Controller
{
    protected $helpers = ['auth', 'form', 'url', 'filesystem'];

    /**
     * Display the suspended user quarantine page
     */
    public function index()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        // If the user is active and not suspended, route to dashboard
        if ((int)$user->active === 1 && $user->status !== 'suspended') {
            if ($user->inGroup('superadmin')) {
                return redirect()->to('/superadmin/home');
            } elseif ($user->inGroup('admin')) {
                return redirect()->to('/admin/dashboard');
            }
            return redirect()->to('/home');
        }

        $supportMessages = new SupportMessageModel();
        $userModel       = new UserModel();

        // Mark messages from admins as read by the user
        $supportMessages->markAsRead($user->id, $user->id);

        $thread = $supportMessages->getThread($user->id);
        foreach ($thread as &$msg) {
            unset($msg['id']);
            if (!empty($msg['attachment'])) {
                $msg['attachment'] = 'account/suspended/chat/attachment/' . basename($msg['attachment']);
            }
        }

        $adminsStatus = $userModel->getAdminsOnlineStatus();

        // User profile & suspension details
        $db = \Config\Database::connect();
        $profile = $db->table('user_profiles')->where('user_id', $user->id)->get()->getRowArray();

        $suspensionReason = $user->status_message ?: 'Administrative suspension due to terms compliance or security audit review.';

        return view('auth/suspended', [
            'user'             => $user,
            'profile'          => $profile,
            'suspensionReason' => $suspensionReason,
            'thread'           => $thread,
            'adminsStatus'     => $adminsStatus,
        ]);
    }

    /**
     * Handle incoming appeal message from suspended user
     */
    public function sendMessage()
    {
        if (!auth()->loggedIn()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Unauthorized'])->setStatusCode(401);
        }

        $user = auth()->user();
        $userId = $user->id;

        $messageText = trim($this->request->getPost('message') ?? '');
        $attachment  = $this->request->getFile('attachment');
        $attachmentPath = null;

        if (empty($messageText) && (empty($attachment) || !$attachment->isValid())) {
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'Please provide a message or attachment.',
            ])->setStatusCode(400);
        }

        // Handle attachment
        if ($attachment && $attachment->isValid() && !$attachment->hasMoved()) {
            $validationRule = [
                'attachment' => [
                    'label' => 'Evidence or Document File',
                    'rules' => [
                        'uploaded[attachment]',
                        'ext_in[attachment,png,jpg,jpeg,gif,pdf]',
                        'mime_in[attachment,image/jpg,image/jpeg,image/png,image/gif,application/pdf]',
                        'max_size[attachment,5120]', // 5MB max
                    ],
                ],
            ];

            if (!$this->validate($validationRule)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error'   => $this->validator->getError('attachment'),
                ])->setStatusCode(400);
            }

            $newName = $attachment->getRandomName();
            $uploadDir = WRITEPATH . 'uploads/chat_attachments';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $attachment->move($uploadDir, $newName);
            $attachmentPath = 'uploads/chat_attachments/' . $newName;
        }

        $supportMessages = new SupportMessageModel();

        // Check if first message in appeal
        $existingCount = $supportMessages->where('client_id', $userId)->countAllResults();
        $finalMessage = $messageText;
        if ($existingCount === 0 && !str_starts_with($finalMessage, '[SUSPENDED ACCOUNT APPEAL]')) {
            $finalMessage = "[SUSPENDED ACCOUNT APPEAL] " . $finalMessage;
        }

        $supportMessages->insert([
            'uuid'       => bin2hex(random_bytes(16)),
            'client_id'  => $userId,
            'sender_id'  => $userId,
            'message'    => $finalMessage,
            'attachment' => $attachmentPath,
            'is_read'    => 0,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Your appeal message has been submitted to administrators.',
        ]);
    }

    /**
     * Poll newest messages in the appeal thread
     */
    public function poll()
    {
        if (!auth()->loggedIn()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Unauthorized'])->setStatusCode(401);
        }

        $user = auth()->user();
        $supportMessages = new SupportMessageModel();

        // Mark unread messages as read
        $supportMessages->markAsRead($user->id, $user->id);

        $thread = $supportMessages->getThread($user->id);
        foreach ($thread as &$msg) {
            unset($msg['id']);
            if (!empty($msg['attachment'])) {
                $msg['attachment'] = 'account/suspended/chat/attachment/' . basename($msg['attachment']);
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'thread'  => $thread,
            'unread'  => 0,
        ]);
    }

    /**
     * Serve appeal attachment securely
     */
    public function attachment(string $filename)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();
        $safeFilename = basename($filename);
        $filepath = WRITEPATH . 'uploads/chat_attachments/' . $safeFilename;

        if (!file_exists($filepath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Attachment not found');
        }

        // Verify attachment belongs to this user or user is admin
        $supportMessages = new SupportMessageModel();
        $msg = $supportMessages->where('client_id', $user->id)
            ->like('attachment', $safeFilename)
            ->first();

        if (!$msg && !$user->inGroup('admin') && !$user->inGroup('superadmin')) {
            throw new \CodeIgniter\Exceptions\SecurityException('Unauthorized access to attachment');
        }

        $mime = mime_content_type($filepath) ?: 'application/octet-stream';
        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $safeFilename . '"')
            ->setBody(file_get_contents($filepath));
    }
}

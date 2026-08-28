<?php

namespace App\Controllers\clients;

use App\Models\SupportMessageModel;
use App\Models\UserModel;

class SupportChatController extends BaseClientController
{
    protected SupportMessageModel $supportMessages;
    protected UserModel $users;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface            $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->supportMessages = new SupportMessageModel();
        $this->users           = new UserModel();
    }

    public function index(): string
    {
        // Mark all support messages in this thread as read by the client
        $this->supportMessages->markAsRead($this->userId, $this->userId);

        $thread = $this->supportMessages->getThread($this->userId);
        foreach ($thread as &$msg) {
            unset($msg['id']);
            if (!empty($msg['attachment'])) {
                $msg['attachment'] = 'support/attachment/' . basename($msg['attachment']);
            }
        }

        $adminsStatus = $this->users->getAdminsOnlineStatus();

        return $this->renderUserView('users/support/chat', [
            'pag'           => 'support_chat',
            'thread'        => $thread,
            'admins_status' => $adminsStatus,
        ]);
    }

    /**
     * POST /support/chat/send
     */
    public function sendMessage()
    {
        $messageText = $this->request->getPost('message');
        $attachment  = $this->request->getFile('attachment');
        $attachmentPath = null;

        // Validation: Must have either a message or an attachment
        if (empty($messageText) && (empty($attachment) || !$attachment->isValid())) {
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'Cannot send an empty message.'
            ])->setStatusCode(400);
        }

        // Handle attachment upload
        if ($attachment && $attachment->isValid() && !$attachment->hasMoved()) {
            $validationRule = [
                'attachment' => [
                    'label' => 'Image or PDF File',
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
                    'error'   => $this->validator->getError('attachment')
                ])->setStatusCode(400);
            }

            // Secure file name and move it to writable private uploads folder
            $newName = $attachment->getRandomName();
            if (!is_dir(WRITEPATH . 'uploads/chat_attachments')) {
                mkdir(WRITEPATH . 'uploads/chat_attachments', 0755, true);
            }
            $attachment->move(WRITEPATH . 'uploads/chat_attachments', $newName);
            $attachmentPath = 'uploads/chat_attachments/' . $newName;
        }

        // Determine if we should notify admins via email (only if the thread is currently "fully read" or empty, to avoid spamming)
        $lastMsg = $this->supportMessages->where('client_id', $this->userId)
            ->orderBy('id', 'DESC')
            ->first();
        $shouldNotify = true;
        if ($lastMsg && $lastMsg['sender_id'] === $this->userId) {
            // Already sent a previous message that admins haven't replied to yet
            $shouldNotify = false;
        }

        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        $messageData = [
            'uuid'       => $uuid,
            'client_id'  => $this->userId,
            'sender_id'  => $this->userId,
            'message'    => $messageText,
            'attachment' => $attachmentPath,
            'is_read'    => 0
        ];

        if ($this->supportMessages->save($messageData)) {
            $msgId = $this->supportMessages->getInsertID();
            $savedMessage = $this->supportMessages->find($msgId);
            $userRow = $this->users->select('users.username, user_profiles.profile_image')
                ->join('user_profiles', 'user_profiles.user_id = users.id', 'left')
                ->where('users.id', $this->userId)
                ->first();
            $savedMessage['username'] = $userRow['username'] ?? 'User';
            $savedMessage['profile_image'] = $userRow['profile_image'] ?? null;

            // Send email notification to admins
            if ($shouldNotify) {
                try {
                    helper('email');
                    $clientName = $this->userData['username'] ?? 'Client';
                    $subject = '[Support] New message from ' . ucwords($clientName);
                    $emailData = [
                        'clientUsername' => $clientName,
                        'messageText'    => $messageText ?: '(Image attachment sent)',
                        'hasAttachment'  => !empty($attachmentPath),
                        'sentAt'         => date('M d, Y H:i:s'),
                    ];
                    send_admin_notification($subject, 'email/admin/new_support_message', $emailData);
                    send_superadmin_notification($subject, 'email/admin/new_support_message', $emailData);
                } catch (\Throwable $e) {
                    log_message('error', 'Failed sending admin chat email alert: ' . $e->getMessage());
                }
            }

            unset($savedMessage['id']);
            if (!empty($savedMessage['attachment'])) {
                $savedMessage['attachment'] = 'support/attachment/' . basename($savedMessage['attachment']);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $savedMessage
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'error'   => 'Failed to save message.'
        ])->setStatusCode(500);
    }

    /**
     * GET /api/v1/support/chat/poll
     * AJAX polling endpoint — returns new messages and admin status.
     * Called every 5 seconds from the chat view.
     */
    public function poll()
    {
        $lastSeenUuid = $this->request->getGet('last_seen_uuid') ?? '';
        $supportModel = new SupportMessageModel();

        $lastSeenId = 0;
        if (!empty($lastSeenUuid)) {
            $msg = $supportModel->where('uuid', $lastSeenUuid)->first();
            if ($msg) {
                $lastSeenId = (int)$msg['id'];
            }
        }

        // Retrieve new messages in the thread
        $newMessages = $supportModel
            ->select('support_messages.*, users.username, user_profiles.profile_image')
            ->join('users', 'users.id = support_messages.sender_id', 'left')
            ->join('user_profiles', 'user_profiles.user_id = support_messages.sender_id', 'left')
            ->where('support_messages.client_id', $this->userId)
            ->where('support_messages.id >', $lastSeenId)
            ->orderBy('support_messages.id', 'ASC')
            ->findAll();

        if (!empty($newMessages)) {
            // Mark incoming admin messages as read now that the client is actively polling
            $this->supportMessages->markAsRead($this->userId, $this->userId);
        }

        // Sanitize returned messages to hide auto-increment IDs and expose secure attachment route
        foreach ($newMessages as &$msg) {
            unset($msg['id']);
            if (!empty($msg['attachment'])) {
                $msg['attachment'] = 'support/attachment/' . basename($msg['attachment']);
            }
        }

        // Fetch latest admin online status
        $adminsStatus = $this->users->getAdminsOnlineStatus();

        // Unread count: messages sent by admins that client hasn't read yet
        $unreadCount = $supportModel
            ->where('client_id', $this->userId)
            ->where('sender_id !=', $this->userId)
            ->where('is_read', 0)
            ->countAllResults();

        return $this->response->setJSON([
            'messages'      => $newMessages,
            'admins_status' => $adminsStatus,
            'unread_count'  => $unreadCount,
        ]);
    }

    /**
     * GET /api/v1/support/unread-count
     * Lightweight endpoint for the global navbar badge poller.
     * Only returns the unread count — used on non-chat pages.
     */
    public function unreadCount()
    {
        $unreadCount = $this->supportMessages
            ->where('client_id', $this->userId)
            ->where('sender_id !=', $this->userId)
            ->where('is_read', 0)
            ->countAllResults();

        return $this->response->setJSON(['unread_count' => $unreadCount]);
    }

    /**
     * GET /support/attachment/{filename}
     * Serves chat attachments securely after checking client ownership.
     */
    public function attachment(string $filename)
    {
        $filename = basename($filename);
        $attachmentPath = 'uploads/chat_attachments/' . $filename;
        $fullPath = WRITEPATH . $attachmentPath;

        if (!file_exists($fullPath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Query database to verify ownership/access
        $db = \Config\Database::connect();
        $message = $db->table('support_messages')
            ->where('attachment', $attachmentPath)
            ->get()
            ->getRowArray();

        if (!$message) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Ownership check: must be client_id
        if ((int)$message['client_id'] !== $this->userId) {
            return $this->response->setStatusCode(403)->setBody('Access Denied');
        }

        $mime = mime_content_type($fullPath);
        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody(file_get_contents($fullPath));
    }
}

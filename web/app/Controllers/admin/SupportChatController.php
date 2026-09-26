<?php

namespace App\Controllers\admin;

use App\Models\SupportMessageModel;
use App\Models\UserModel;

class SupportChatController extends BaseAdminController
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

    /**
     * GET /admin/support
     */
    public function index(): string
    {
        $conversations = $this->supportMessages->getConversationsList();

        return $this->renderView('admin/support/list', [
            'pag'           => 'admin-support',
            'conversations' => $conversations,
        ]);
    }

    /**
     * GET /admin/support/tickets
     */
    public function tickets(): string
    {
        $conversations = $this->supportMessages->getConversationsList();

        return $this->renderView('admin/support/list', [
            'pag'           => 'admin-support-tickets',
            'conversations' => $conversations,
        ]);
    }

    /**
     * GET /admin/support/thread/{clientId}
     */
    public function thread(int $clientId): string
    {
        // Mark all client messages in this thread as read by the admin
        $this->supportMessages->markAsRead($clientId, $this->userId);

        $thread = $this->supportMessages->getThread($clientId);
        foreach ($thread as &$msg) {
            unset($msg['id']);
            if (!empty($msg['attachment'])) {
                $msg['attachment'] = 'admin/support/attachment/' . basename($msg['attachment']);
            }
        }

        $client = $this->users->find($clientId);

        if (!$client) {
            session()->setFlashdata('error', 'Client not found.');
            return redirect()->to(base_url('admin/support'));
        }

        $clientStatus = $this->users->getUserOnlineStatus($clientId);

        return $this->renderView('admin/support/chat', [
            'pag'           => 'admin-support',
            'thread'        => $thread,
            'client'        => $client,
            'client_status' => $clientStatus,
        ]);
    }

    /**
     * POST /admin/support/reply
     */
    public function reply()
    {
        $clientId    = (int) $this->request->getPost('client_id');
        $messageText = $this->request->getPost('message');
        $attachment  = $this->request->getFile('attachment');
        $attachmentPath = null;

        // Validation: Verify client exists
        $client = $this->users->find($clientId);
        if (!$client) {
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'Client not found.'
            ])->setStatusCode(404);
        }

        // Validation: Must have either message or attachment
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

            // Secure file name and move it to private uploads folder
            $newName = $attachment->getRandomName();
            if (!is_dir(WRITEPATH . 'uploads/chat_attachments')) {
                mkdir(WRITEPATH . 'uploads/chat_attachments', 0755, true);
            }
            $attachment->move(WRITEPATH . 'uploads/chat_attachments', $newName);
            $attachmentPath = 'uploads/chat_attachments/' . $newName;
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
            'client_id'  => $clientId,
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
            $savedMessage['username'] = $userRow['username'] ?? 'Support';
            $savedMessage['profile_image'] = $userRow['profile_image'] ?? null;

            // Send notification email to the client
            try {
                // Resolve client email
                $db = \Config\Database::connect();
                $identity = $db->table('auth_identities')
                    ->where('user_id', $clientId)
                    ->where('type', 'email_password')
                    ->get()
                    ->getRowArray();
                $clientEmail = $identity['secret'] ?? null;

                if ($clientEmail) {
                    helper('email');
                    $clientUsername = $client['username'] ?? 'User';
                    $subject = '[Support] New reply from the support team';
                    $emailData = [
                        'username'      => $clientUsername,
                        'messageText'   => $messageText ?: '(Image attachment sent)',
                        'hasAttachment' => !empty($attachmentPath),
                        'sentAt'         => date('M d, Y H:i:s'),
                    ];
                    send_templated_email($clientEmail, $subject, 'email/user/new_support_reply', $emailData);
                }
            } catch (\Throwable $e) {
                log_message('error', 'Failed sending client chat email alert: ' . $e->getMessage());
            }

            unset($savedMessage['id']);
            if (!empty($savedMessage['attachment'])) {
                $savedMessage['attachment'] = 'admin/support/attachment/' . basename($savedMessage['attachment']);
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
     * GET /api/v1/admin/support/poll/{clientId}
     * AJAX polling endpoint — returns new messages and client status.
     * Called every 5 seconds from the admin chat view.
     */
    public function poll(int $clientId)
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
            ->where('support_messages.client_id', $clientId)
            ->where('support_messages.id >', $lastSeenId)
            ->orderBy('support_messages.id', 'ASC')
            ->findAll();

        if (!empty($newMessages)) {
            // Mark incoming client messages as read
            $this->supportMessages->markAsRead($clientId, $this->userId);
        }

        // Sanitize returned messages to hide auto-increment IDs and expose secure attachment route
        foreach ($newMessages as &$msg) {
            unset($msg['id']);
            if (!empty($msg['attachment'])) {
                $msg['attachment'] = 'admin/support/attachment/' . basename($msg['attachment']);
            }
        }

        // Fetch latest client online status
        $clientStatus = $this->users->getUserOnlineStatus($clientId);

        // Unread count: all unread messages from non-admin senders
        $db = \Config\Database::connect();
        $unreadCount = $db->table('support_messages')
            ->where('is_read', 0)
            ->whereNotIn('sender_id', function (\CodeIgniter\Database\BaseBuilder $b) {
                return $b->select('user_id')->from('auth_groups_users')->whereIn('group', ['admin', 'superadmin']);
            })
            ->countAllResults();

        return $this->response->setJSON([
            'messages'      => $newMessages,
            'client_status' => $clientStatus,
            'unread_count'  => $unreadCount,
        ]);
    }

    /**
     * GET /api/v1/admin/support/unread-count
     * Lightweight endpoint for the global navbar badge poller.
     */
    public function unreadCount()
    {
        $db = \Config\Database::connect();
        $unreadCount = $db->table('support_messages')
            ->where('is_read', 0)
            ->whereNotIn('sender_id', function (\CodeIgniter\Database\BaseBuilder $b) {
                return $b->select('user_id')->from('auth_groups_users')->whereIn('group', ['admin', 'superadmin']);
            })
            ->countAllResults();

        return $this->response->setJSON(['unread_count' => $unreadCount]);
    }

    /**
     * GET /admin/support/attachment/{filename}
     * Serves chat attachments securely to authenticated admins/superadmins.
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

        $mime = mime_content_type($fullPath);
        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody(file_get_contents($fullPath));
    }
}

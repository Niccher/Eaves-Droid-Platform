<?php

namespace App\Models;

use CodeIgniter\Model;

class SupportMessageModel extends Model
{
    protected $table            = 'support_messages';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'uuid', 'client_id', 'sender_id', 'message', 'attachment', 'is_read', 'read_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Fetch all messages in a thread between user and support.
     *
     * @param int $clientId
     * @return array
     */
    public function getThread(int $clientId): array
    {
        return $this->select('support_messages.*, users.username, user_profiles.profile_image')
            ->join('users', 'users.id = support_messages.sender_id', 'left')
            ->join('user_profiles', 'user_profiles.user_id = support_messages.sender_id', 'left')
            ->where('support_messages.client_id', $clientId)
            ->orderBy('support_messages.created_at', 'ASC')
            ->findAll();
    }

    /**
     * Get the count of unread messages in a thread for a specific viewer.
     *
     * @param int $clientId
     * @param int $viewerId
     * @return int
     */
    public function getUnreadCount(int $clientId, int $viewerId): int
    {
        return $this->where('client_id', $clientId)
            ->where('sender_id !=', $viewerId)
            ->where('is_read', 0)
            ->countAllResults();
    }

    /**
     * Mark messages in a thread as read when viewed.
     *
     * @param int $clientId
     * @param int $viewerId
     * @return bool|int|string
     */
    public function markAsRead(int $clientId, int $viewerId)
    {
        return $this->where('client_id', $clientId)
            ->where('sender_id !=', $viewerId)
            ->where('is_read', 0)
            ->set([
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s')
            ])
            ->update();
    }

    /**
     * Get list of unique client conversations for administrators.
     * Includes latest message, sender details, and client unread counts.
     *
     * @return array
     */
    public function getConversationsList(): array
    {
        $db = \Config\Database::connect();

        // Subquery to find the latest message ID per client_id
        $subQuery = $db->table('support_messages sm1')
            ->select('sm1.client_id, MAX(sm1.id) as max_id')
            ->groupBy('sm1.client_id');

        // Main query joining user and latest message details
        $builder = $db->table('support_messages sm')
            ->select('sm.*, u.username, u.active, u.status, up.profile_image')
            ->join('users u', 'u.id = sm.client_id')
            ->join('user_profiles up', 'up.user_id = sm.client_id', 'left')
            ->join('(SELECT client_id, MAX(id) as max_id FROM support_messages GROUP BY client_id) latest', 'latest.client_id = sm.client_id AND latest.max_id = sm.id')
            ->orderBy('sm.created_at', 'DESC');

        $conversations = $builder->get()->getResultArray();

        // Calculate unread counts (where sender is the client themselves)
        foreach ($conversations as &$conv) {
            $conv['unread_count'] = $this->where('client_id', $conv['client_id'])
                ->where('sender_id', $conv['client_id'])
                ->where('is_read', 0)
                ->countAllResults();
        }

        return $conversations;
    }
}

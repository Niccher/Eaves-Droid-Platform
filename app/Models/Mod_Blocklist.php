<?php

namespace App\Models;

use CodeIgniter\Model;

class Mod_Blocklist extends Model
{
    protected $table = 'tbl_blocklist';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'owner_id', 
        'category', 
        'identifier', 
        'description',
        'created_at',
        'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Add a new block rule
     */
    public function addBlock(int $userId, string $category, string $identifier, ?string $description = null): bool
    {
        // Check if it already exists
        $exists = $this->where('owner_id', $userId)
                       ->where('category', $category)
                       ->where('identifier', $identifier)
                       ->first();
                       
        if ($exists) {
            return true; // Already blocked
        }

        return $this->insert([
            'owner_id' => $userId,
            'category' => $category,
            'identifier' => $identifier,
            'description' => $description
        ]) !== false;
    }

    /**
     * Remove a block rule
     */
    public function removeBlock(int $blockId, int $userId): bool
    {
        return $this->where('id', $blockId)
                    ->where('owner_id', $userId)
                    ->delete();
    }

    /**
     * Get all block rules for a user, optionally filtered by category
     */
    public function getUserBlocks(int $userId, ?string $category = null): array
    {
        $builder = $this->where('owner_id', $userId);
        if ($category) {
            $builder->where('category', $category);
        }
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Get a flat array of blocked identifiers for a specific category
     * Used for SQL NOT IN queries
     */
    public function getBlockedIdentifiers(int $userId, string $category): array
    {
        $blocks = $this->select('identifier')
                       ->where('owner_id', $userId)
                       ->where('category', $category)
                       ->findAll();
                       
        return array_column($blocks, 'identifier');
    }
}

<?php

namespace App\Libraries;

use CodeIgniter\Session\Handlers\DatabaseHandler;
use Predis\Client;
use Config\Session;

class ResilientSessionHandler extends DatabaseHandler
{
    protected ?Client $redis = null;
    protected int $redisTtl;

    public function __construct(Session $config, string $ipAddress)
    {
        parent::__construct($config, $ipAddress);
        
        $this->redisTtl = $config->expiration > 0 ? $config->expiration : 7200; // default 2 hours if 0

        // We assume the standard Redis host environment variable set by Railway
        $redisUrl = env('REDIS_URL', 'tcp://127.0.0.1:6379');

        try {
            $this->redis = new Client($redisUrl, [
                'timeout'            => 1.0, // 1 second connect timeout
                'read_write_timeout' => 1.0  // 1 second read/write timeout
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to initialize ResilientSessionHandler Redis: ' . $e->getMessage());
            $this->redis = null;
        }
    }

    public function read(string $id): string|false
    {
        // For CodeIgniter's DatabaseHandler, calling parent::read($id) ensures the DB row gets locked 
        // (if matchIP/matchFingerprint requires it) and populates internal state like $this->rowExists.
        // Doing only Redis read and skipping parent::read() might cause issues on session write() later
        // where it issues an INSERT instead of UPDATE, triggering a Duplicate Key exception in MySQL.
        // Therefore, we use Redis for fast reading, but we must still ensure the DB is prepared.
        // To maximize speed while preserving safety, we'll try Redis first.
        $redisData = null;

        if ($this->redis) {
            try {
                $redisData = $this->redis->get("session:{$id}");
                if ($redisData !== null) {
                    $this->redis->expire("session:{$id}", $this->redisTtl);
                }
            } catch (\Throwable $e) {
                log_message('error', 'ResilientSessionHandler Redis read failed: ' . $e->getMessage());
                $this->redis = null;
            }
        }

        $dbData = parent::read($id);

        // If Redis had the data, return it (faster). 
        // Otherwise, return what the DB found (and we'll cache it in Redis on next write).
        return ($redisData !== null) ? $redisData : $dbData;
    }

    public function write(string $id, string $data): bool
    {
        if ($this->redis) {
            try {
                $this->redis->setex("session:{$id}", $this->redisTtl, $data);
            } catch (\Throwable $e) {
                log_message('error', 'ResilientSessionHandler Redis write failed: ' . $e->getMessage());
                $this->redis = null;
            }
        }

        return parent::write($id, $data);
    }

    public function destroy(string $id): bool
    {
        if ($this->redis) {
            try {
                $this->redis->del("session:{$id}");
            } catch (\Throwable $e) {
                log_message('error', 'ResilientSessionHandler Redis destroy failed: ' . $e->getMessage());
            }
        }

        return parent::destroy($id);
    }

    public function gc(int $max_lifetime): int|false
    {
        return parent::gc($max_lifetime);
    }
}

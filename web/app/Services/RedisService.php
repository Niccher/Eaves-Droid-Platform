<?php

namespace App\Services;

use Predis\Client;

class RedisService
{
    protected ?Client $client = null;

    public function __construct()
    {
        $redisHost = env('REDIS_HOST', '127.0.0.1');
        $redisPort = env('REDIS_PORT', 6379);
        $redisUrl = env('REDIS_URL', "tcp://{$redisHost}:{$redisPort}");

        try {
            $this->client = new Client($redisUrl, [
                'timeout'            => 1.0,
                'read_write_timeout' => 2.0,
                'retry_interval'     => 100, // retry every 100ms
            ]);
            // Attempt to connect immediately to catch errors early
            $this->client->connect();
        } catch (\Throwable $e) {
            log_message('error', 'RedisService connection failed: ' . $e->getMessage());
            $this->client = null;
        }
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function get(string $key): ?string
    {
        if (!$this->client) return null;
        try {
            return $this->client->get($key);
        } catch (\Throwable $e) {
            log_message('error', "Redis get failed for key {$key}: " . $e->getMessage());
            $this->client = null;
            return null;
        }
    }

    public function setex(string $key, int $ttl, string $value): bool
    {
        if (!$this->client) return false;
        try {
            $this->client->setex($key, $ttl, $value);
            return true;
        } catch (\Throwable $e) {
            log_message('error', "Redis setex failed for key {$key}: " . $e->getMessage());
            $this->client = null;
            return false;
        }
    }

    public function del(string $key): bool
    {
        if (!$this->client) return false;
        try {
            $this->client->del($key);
            return true;
        } catch (\Throwable $e) {
            log_message('error', "Redis del failed for key {$key}: " . $e->getMessage());
            $this->client = null;
            return false;
        }
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @param string $key
     * @param int $ttl
     * @param callable $callback
     * @return mixed
     */
    public function remember(string $key, int $ttl, callable $callback)
    {
        if (!$this->client) {
            return $callback();
        }

        $cached = $this->get($key);
        if ($cached !== null) {
            $decoded = json_decode($cached, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $cached;
        }

        $value = $callback();
        if ($value !== null) {
            $encoded = is_string($value) ? $value : json_encode($value);
            $this->setex($key, $ttl, $encoded);
        }

        return $value;
    }
}

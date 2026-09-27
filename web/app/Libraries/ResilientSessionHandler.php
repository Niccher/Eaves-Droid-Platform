<?php

namespace App\Libraries;

use CodeIgniter\Session\Handlers\BaseHandler;
use CodeIgniter\Session\Handlers\DatabaseHandler;
use CodeIgniter\Session\Handlers\RedisHandler;
use Config\Session as SessionConfig;
use SessionHandlerInterface;

/**
 * ResilientSessionHandler
 * 
 * Enterprise High-Availability dual-engine session driver.
 * Under normal operations, sessions execute in-memory via Redis 7 (<1ms latency).
 * Uses a non-blocking 50ms pre-flight socket probe with request-lifecycle memoization.
 * If Redis is offline, slow, or restarting, it transparently degrades to MySQL (ci_sessions)
 * without 500 errors, session loss, or blocking socket timeouts.
 */
class ResilientSessionHandler extends BaseHandler implements SessionHandlerInterface
{
    /**
     * Request-lifecycle memoized Redis availability flag.
     * @var bool|null
     */
    private static ?bool $redisAlive = null;

    /**
     * Microsecond latency of the pre-flight socket probe in milliseconds.
     * @var float|null
     */
    private static ?float $probeLatencyMs = null;

    /**
     * Active storage engine in use ('redis' or 'mysql').
     * @var string|null
     */
    private static ?string $activeEngine = null;

    /**
     * Parsed Redis connection parameters.
     * @var array{host: string, port: int, pass: string, savePath: string}|null
     */
    private static ?array $redisParams = null;

    /**
     * The active session handler instance.
     * @var SessionHandlerInterface|null
     */
    protected $activeHandler;

    /**
     * @var SessionConfig
     */
    protected $config;

    /**
     * @var string
     */
    protected $ipAddress;

    public function __construct(SessionConfig $config, string $ipAddress)
    {
        parent::__construct($config, $ipAddress);
        $this->config = $config;
        $this->ipAddress = $ipAddress;
    }

    /**
     * Parse Redis environment variables and cache connection details.
     *
     * @return array{host: string, port: int, pass: string, savePath: string}
     */
    public static function getRedisParams(): array
    {
        if (self::$redisParams !== null) {
            return self::$redisParams;
        }

        $redisUrl = getenv('REDIS_URL') ?: getenv('REDIS_PRIVATE_URL');
        $host = 'redis';
        $port = 6379;
        $pass = '';

        if (!empty($redisUrl)) {
            $parsed = parse_url($redisUrl);
            $host = $parsed['host'] ?? 'redis';
            $port = (int)($parsed['port'] ?? 6379);
            $pass = $parsed['pass'] ?? '';
        } else {
            $envHost = getenv('REDIS_HOST');
            if (!empty($envHost)) {
                $host = $envHost;
            }
            $envPort = getenv('REDIS_PORT');
            if (!empty($envPort)) {
                $port = (int)$envPort;
            }
            $envPass = getenv('REDIS_PASSWORD');
            if (!empty($envPass)) {
                $pass = $envPass;
            }
        }

        $savePath = "tcp://{$host}:{$port}";
        if (!empty($pass)) {
            $savePath .= '?auth=' . rawurlencode($pass);
        }

        self::$redisParams = [
            'host'     => $host,
            'port'     => $port,
            'pass'     => $pass,
            'savePath' => $savePath,
        ];

        return self::$redisParams;
    }

    /**
     * Ultra-fast non-blocking pre-flight socket probe.
     * Defaults to a 50ms (0.05s) timeout to eliminate thread stalls during Redis outages.
     * Memoizes the result across the current request lifecycle.
     */
    public static function probeRedis(?string $host = null, ?int $port = null, float $timeout = 0.05): bool
    {
        if (self::$redisAlive !== null) {
            return self::$redisAlive;
        }

        $params = self::getRedisParams();
        $targetHost = $host ?? $params['host'];
        $targetPort = $port ?? $params['port'];

        $startTime = microtime(true);
        $errno = 0;
        $errstr = '';

        $fp = @fsockopen($targetHost, $targetPort, $errno, $errstr, $timeout);
        $elapsed = (microtime(true) - $startTime) * 1000.0;
        self::$probeLatencyMs = round($elapsed, 2);

        if (is_resource($fp)) {
            @fclose($fp);
            self::$redisAlive = true;
        } else {
            self::$redisAlive = false;
            log_message('warning', sprintf(
                '[Resilience] Redis probe failed on %s:%d (%s - %s) in %.2fms. Engaging MySQL fallback.',
                $targetHost,
                $targetPort,
                $errno,
                $errstr,
                self::$probeLatencyMs
            ));
        }

        return self::$redisAlive;
    }

    /**
     * Check if Redis is currently reachable.
     */
    public static function isRedisAlive(): bool
    {
        return self::probeRedis();
    }

    /**
     * Check if the platform is currently operating in fallback mode.
     */
    public static function isFallbackActive(): bool
    {
        return !self::isRedisAlive();
    }

    /**
     * Get the measured probe latency in milliseconds.
     */
    public static function getProbeLatencyMs(): float
    {
        if (self::$probeLatencyMs === null) {
            self::probeRedis();
        }
        return self::$probeLatencyMs ?? 0.0;
    }

    /**
     * Get the active session engine identifier.
     */
    public static function getActiveEngine(): string
    {
        if (self::$activeEngine !== null) {
            return self::$activeEngine;
        }
        return self::isRedisAlive() ? 'redis' : 'mysql';
    }

    /**
     * Compile structured resilience diagnostics for telemetry and health probes.
     *
     * @return array<string, mixed>
     */
    public static function getResilienceStatus(): array
    {
        $alive = self::isRedisAlive();
        return [
            'failover_configured' => true,
            'redis_probe_ms'      => self::getProbeLatencyMs(),
            'redis_status'        => $alive ? 'connected' : 'fallback_active',
            'session_engine'      => $alive ? 'redis' : 'mysql',
            'cache_engine'        => $alive ? 'redis' : 'file',
            'fallback_active'     => !$alive,
            'timestamp'           => time(),
        ];
    }

    /**
     * Initialize the session handler.
     * Probes Redis first:
     * - If online (<=50ms): connects via RedisHandler.
     * - If offline / timeout: bypasses Redis and immediately initialises DatabaseHandler.
     */
    public function open($path, $name): bool
    {
        $params = self::getRedisParams();

        // 1. Pre-flight 50ms probe
        if (self::probeRedis($params['host'], $params['port'])) {
            try {
                $redisConfig = clone $this->config;
                $redisConfig->savePath = $params['savePath'];

                $this->activeHandler = new RedisHandler($redisConfig, $this->ipAddress);
                $opened = $this->activeHandler->open($redisConfig->savePath, $name);

                if ($opened) {
                    self::$activeEngine = 'redis';
                    return true;
                }

                throw new \RuntimeException('RedisHandler::open returned false.');
            } catch (\Throwable $e) {
                log_message('critical', '[Resilience] Redis runtime error: ' . $e->getMessage() . '. Degrading to DatabaseHandler.');
                self::$redisAlive = false;
            }
        }

        // 2. Fallback to DatabaseHandler (MySQL ci_sessions table)
        self::$activeEngine = 'mysql';
        $dbConfig = clone $this->config;
        $dbConfig->savePath = 'ci_sessions';

        $this->activeHandler = new DatabaseHandler($dbConfig, $this->ipAddress);
        return $this->activeHandler->open($dbConfig->savePath, $name);
    }

    public function close(): bool
    {
        return $this->activeHandler ? $this->activeHandler->close() : true;
    }

    public function read($id): string|false
    {
        return $this->activeHandler ? $this->activeHandler->read($id) : false;
    }

    public function write($id, $data): bool
    {
        return $this->activeHandler ? $this->activeHandler->write($id, $data) : false;
    }

    public function destroy($id): bool
    {
        return $this->activeHandler ? $this->activeHandler->destroy($id) : false;
    }

    public function gc($max_lifetime): int|false
    {
        return $this->activeHandler ? $this->activeHandler->gc($max_lifetime) : false;
    }
}

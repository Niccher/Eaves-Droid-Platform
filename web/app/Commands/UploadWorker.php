<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class UploadWorker extends BaseCommand
{
    protected $group       = 'Worker';
    protected $name        = 'worker:upload';
    protected $description = 'Runs continuously to process the Redis upload stream.';

    public function run(array $params)
    {
        $redisSvc = new \App\Services\RedisService();
        $redis = $redisSvc->getClient();

        if (!$redis) {
            CLI::write('Could not connect to Redis.', 'red');
            return;
        }

        // Create consumer group if not exists
        try {
            $redis->xgroup('CREATE', 'upload_queue', 'upload_group', '0', true);
        } catch (\Throwable $e) {
            // Group already exists, ignore
        }

        $consumerName = 'worker-' . gethostname() . '-' . getmypid();
        CLI::write("Upload worker {$consumerName} started. Listening for files...", 'green');

        $telemetrySvc = new \App\Services\TelemetryReceiverService();
        $queueModel = new \App\Models\UploadQueueModel();

        $backoff = 2;

        while (true) {
            try {
                // Read from stream, blocking for up to 5 seconds
                $messages = $redis->xreadgroup(
                    'upload_group',
                    $consumerName,
                    ['upload_queue' => '>'],
                    10,
                    5000
                );

                if ($messages) {
                    foreach ($messages as $stream => $streamMessages) {
                        foreach ($streamMessages as $id => $message) {
                            $queueId = $message['queue_id'] ?? null;
                            if ($queueId) {
                                CLI::write("Processing queue ID {$queueId} (Stream ID {$id})");
                                
                                $queueModel->markProcessing($queueId);
                                
                                // Process
                                $item = $queueModel->find($queueId);
                                if ($item) {
                                    $result = $telemetrySvc->processQueueItemInline(
                                        $queueId,
                                        $queueModel,
                                        $item['file_category'],
                                        $item['owner_id'],
                                        $item['stored_filename'],
                                        $item['device_print_id']
                                    );
                                    
                                    if ($result['success']) {
                                        $queueModel->markCompleted($queueId);
                                        // Acknowledge the message so it's removed from PEL
                                        $redis->xack('upload_queue', 'upload_group', [$id]);
                                        // Optionally delete from stream to save memory
                                        $redis->xdel('upload_queue', [$id]);
                                        CLI::write("Success: queue ID {$queueId}", 'green');
                                    } else {
                                        // Failed - we do not XACK, so it stays in Pending Entries List (PEL) for retry
                                        CLI::write("Failed: queue ID {$queueId} - " . ($result['error'] ?? 'Unknown error'), 'red');
                                    }
                                } else {
                                    // Item disappeared from DB, just ACK it
                                    $redis->xack('upload_queue', 'upload_group', [$id]);
                                }
                            }
                        }
                    }
                }
                
                // Reset backoff on successful loop iteration (even if no messages)
                $backoff = 2;
            } catch (\Throwable $e) {
                CLI::write('Worker error: ' . $e->getMessage(), 'red');
                sleep($backoff);
                $backoff = min($backoff * 2, 60); // Exponential backoff, cap at 60s
            }
        }
    }
}

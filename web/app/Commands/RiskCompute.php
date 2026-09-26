<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\RiskScoreService;

class RiskCompute extends BaseCommand
{
    protected $group = 'risk';
    protected $name = 'risk:compute';
    protected $description = 'Compute device risk scores from ML findings';
    
    protected $usage = 'risk:compute [--user-id=ID] [--device-id=ID] [--window-days=N]';
    protected $arguments = [];
    protected $options = [
        'user-id'     => 'Compute for specific user',
        'device-id'   => 'Compute for specific device',
        'window-days' => 'Lookback window in days (default: 30)',
    ];

    public function run(array $params)
    {
        $userId = $params['user-id'] ?? null;
        $deviceId = $params['device-id'] ?? null;
        $windowDays = (int)($params['window-days'] ?? 30);
        
        CLI::write("=== risk:compute started ===", 'green');
        CLI::write("User: " . ($userId ?? 'ALL') . ", Device: " . ($deviceId ?? 'ALL'));
        CLI::write("Window: {$windowDays} days");
        
        $service = new RiskScoreService();
        $computed = $service->computeAll($userId, $deviceId, $windowDays);
        
        CLI::write("\nRisk scores computed: $computed", 'green');
        CLI::write("=== risk:compute completed ===", 'green');
    }
}
<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?php
// Process USB records from snapshots
$connectedDevices = [];
$adbEnabledGlobal = false;
$storageConnectedGlobal = false;

$speedLabels = [
    1 => 'Low Speed (1.5 Mbps)',
    2 => 'Full Speed (12 Mbps)',
    3 => 'High Speed (480 Mbps) [USB 2.0]',
    4 => 'SuperSpeed (5 Gbps) [USB 3.0]',
    5 => 'SuperSpeed+ (10 Gbps) [USB 3.1]'
];

$classLabels = [
    0 => 'Interface Specific',
    1 => 'Audio System',
    2 => 'Communications',
    3 => 'Human Interface (HID) [Keyboard/Mouse]',
    5 => 'Physical Device',
    6 => 'Image / Camera',
    7 => 'Printer',
    8 => 'Mass Storage [Flash Drive]',
    9 => 'USB Hub',
    10 => 'CDC Data Link',
    11 => 'Smart Card Reader',
    13 => 'Security Token / Dongle',
    14 => 'Video / Webcam',
    15 => 'Personal Health Device',
    16 => 'Diagnostic Device',
    224 => 'Wireless Controller [Bluetooth/Wi-Fi]',
    225 => 'Application Specific',
    239 => 'Vendor Specific Protocol',
    255 => 'Vendor Specific'
];

foreach ($rows as $r) {
    $speedId = (int)($r['speed'] ?? 0);
    $speedName = $speedLabels[$speedId] ?? ($r['speed'] ?? 'Unknown Speed');
    
    $classId = (int)($r['device_class'] ?? 0);
    $className = $classLabels[$classId] ?? ($r['device_class'] ?? 'Other Interface');

    $isAdb = !empty($r['is_adb']) || !empty($r['is_debug_accessory']);
    $isMassStorage = ($classId === 8);

    if ($isAdb) {
        $adbEnabledGlobal = true;
    }
    if ($isMassStorage) {
        $storageConnectedGlobal = true;
    }

    $connectedDevices[] = [
        'product' => $r['product_name'] ?? $r['usb_device_id'] ?? 'Unknown Peripheral',
        'manufacturer' => $r['manufacturer_name'] ?? 'Generic',
        'vid' => $r['vendor_id'] ?? 'N/A',
        'pid' => $r['product_id'] ?? 'N/A',
        'serial' => $r['serial_number'] ?? 'N/A',
        'class' => $className,
        'class_id' => $classId,
        'speed' => $speedName,
        'speed_id' => $speedId,
        'power' => (int)($r['power_ma'] ?? 0),
        'is_adb' => $isAdb,
        'is_charging' => !empty($r['is_charging']),
        'is_midi' => !empty($r['is_midi']),
        'bytes_transferred' => (float)($r['total_bytes_transferred'] ?? 0),
        'connected_time' => $r['connected_time'] ?? '—',
        'disconnected_time' => $r['disconnected_time'] ?? '—',
        'extracted' => !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—'
    ];
}

// Helper to format bytes
if (!function_exists('format_bytes_usb')) {
    function format_bytes_usb(float $bytes): string {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB'];
        $u = 0;
        while ($bytes >= 1024 && $u < count($units) - 1) {
            $bytes /= 1024;
            $u++;
        }
        return round($bytes, 2) . ' ' . $units[$u];
    }
}
?>

<style>
.usb-dashboard {
    margin-bottom: 20px;
}
.usb-card {
    border-radius: 8px;
    background: #ffffff;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-left: 5px solid #adb5bd;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.usb-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.usb-card.class-adb {
    border-left-color: #dc3545; /* Red warning for debugger connections */
}
.usb-card.class-storage {
    border-left-color: #ffc107; /* Warning yellow for storage exfiltration */
}
.usb-card.class-standard {
    border-left-color: #28a745; /* Clean green for safe power profiles */
}
.callout-usb {
    border-left: 5px solid #17a2b8;
    background: #fdfdfd;
    border-radius: 4px;
}
.usb-badge-pill {
    font-size: 11px;
    margin-right: 3px;
    margin-bottom: 3px;
}
</style>

<div class="content-wrapper">
    <!-- Keep standard content-header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="h3 mb-0 font-weight-bold text-dark">
                        <i class="fas fa-usb text-secondary mr-2"></i>USB Connection Diagnostics
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <?= $nav_urls ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Educational Callout box -->
            <div class="callout callout-info callout-usb shadow-sm p-3 mb-4">
                <h5 class="font-weight-bold text-info"><i class="fas fa-usb mr-2"></i> USB Peripherals Audit</h5>
                <p class="text-secondary mb-2" style="font-size: 14px;">
                    This dashboard audits external hardware peripherals connected to the device's physical USB port. Monitoring connections is a critical security step to identify physical data exfiltration methods and debugging bridge connections.
                </p>
                <div class="row mt-2" style="font-size: 12px;">
                    <div class="col-md-6 border-right">
                        <span class="font-weight-bold text-dark d-block mb-1">Critical Security Threats:</span>
                        <ul class="pl-3 mb-0 text-muted">
                            <li><b class="text-danger">ADB Debugging Bridge:</b> Active debug connections allow full device terminal control and manual file extraction bypass.</li>
                            <li><b class="text-warning">Mass Storage (OTG):</b> Direct file copying to flash drives without network logs.</li>
                        </ul>
                    </div>
                    <div class="col-md-6 pl-md-3 mt-2 mt-md-0">
                        <span class="font-weight-bold text-dark d-block mb-1">Diagnostic Telemetry:</span>
                        <ul class="pl-3 mb-0 text-muted">
                            <li>Exposes device current drain (mA) and speed caps (USB 2.0 vs 3.0) to identify standard charger connections vs active host devices.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Global security warnings -->
            <?php if ($adbEnabledGlobal): ?>
                <div class="alert alert-danger shadow-sm mb-4">
                    <h5><i class="icon fas fa-shield-alt"></i> Severe Alert: ADB debugging connection detected!</h5>
                    <span>An active Android Debug Bridge (ADB) session was logged. This allows remote administrative control over the device system. Confirm connection origins immediately.</span>
                </div>
            <?php endif; ?>

            <?php if ($storageConnectedGlobal): ?>
                <div class="alert alert-warning shadow-sm mb-4">
                    <h5><i class="icon fas fa-folder-open"></i> Warning: OTG Mass Storage Connected</h5>
                    <span>An external storage unit (Flash Drive / Hard Drive) was mounted physically to the device USB port. Check file audit logs for transfer indicators.</span>
                </div>
            <?php endif; ?>

            <!-- USB Cards Grid (Replaces boring table) -->
            <div class="row">
                <?php if (empty($connectedDevices)): ?>
                    <div class="col-12 text-center py-5">
                        <div class="empty-state">
                            <i class="fas fa-usb fa-3x text-muted mb-3"></i>
                            <h4 class="text-secondary">No USB Devices Found</h4>
                            <p class="text-muted">USB diagnostic log entries will appear here once retrieved.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($connectedDevices as $d): 
                        $cardClass = 'class-standard';
                        if ($d['is_adb']) { $cardClass = 'class-outline class-adb'; }
                        elseif ($d['class_id'] === 8) { $cardClass = 'class-outline class-storage'; }
                        ?>
                        <div class="col-md-6 mb-4">
                            <div class="card usb-card <?= $cardClass ?> h-100 shadow-sm">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="font-weight-bold mb-0">
                                                <i class="fas fa-plug text-primary mr-2" style="font-size: 15px;"></i>
                                                <?= esc($d['product']) ?>
                                            </h5>
                                            <small class="text-muted d-block mt-1">
                                                Brand: <b><?= esc($d['manufacturer']) ?></b> · VID: <code><?= esc($d['vid']) ?></code> PID: <code><?= esc($d['pid']) ?></code>
                                            </small>
                                        </div>
                                        <div class="text-right">
                                            <?php if ($d['is_adb']): ?>
                                                <span class="badge badge-danger px-2 py-1"><i class="fas fa-bug mr-1"></i>ADB DEBUG</span>
                                            <?php elseif ($d['class_id'] === 8): ?>
                                                <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-hdd mr-1"></i>STORAGE</span>
                                            <?php else: ?>
                                                <span class="badge badge-success px-2 py-1"><i class="fas fa-plug mr-1"></i>PERIPHERAL</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="row align-items-center mb-3">
                                        <div class="col-7 border-right">
                                            <span class="font-weight-bold text-secondary d-block mb-1" style="font-size: 11px;">Interface Protocol:</span>
                                            <span class="text-dark font-weight-bold" style="font-size: 13px;"><?= esc($d['class']) ?></span>
                                            <span class="text-muted d-block mt-1" style="font-size: 12px;"><i class="fas fa-tachometer-alt mr-1"></i>Speed: <?= esc($d['speed']) ?></span>
                                        </div>
                                        <div class="col-5 pl-3">
                                            <span class="font-weight-bold text-secondary d-block mb-1" style="font-size: 11px;">Haptic Current Draw:</span>
                                            <h4 class="font-weight-bold text-dark mb-0">
                                                <?= $d['power'] ?> <span style="font-size: 12px; color:#6c757d;">mA</span>
                                            </h4>
                                        </div>
                                    </div>

                                    <!-- Byte transmission display -->
                                    <?php if ($d['bytes_transferred'] > 0): ?>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-1" style="font-size: 12px;">
                                                <span class="font-weight-bold text-secondary">Data Volume Transferred:</span>
                                                <span class="text-primary font-weight-bold"><?= format_bytes_usb($d['bytes_transferred']) ?></span>
                                            </div>
                                            <div class="progress progress-xs" style="border-radius: 4px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 75%"></div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="border-top pt-2">
                                        <span class="font-weight-bold text-secondary d-block mb-1" style="font-size: 11px;">Device Parameters:</span>
                                        <div class="d-flex flex-wrap">
                                            <span class="badge usb-badge-pill badge-<?= $d['is_charging'] ? 'success' : 'secondary' ?>">
                                                Power Supply: <?= $d['is_charging'] ? 'Charging' : 'Idle' ?>
                                            </span>
                                            <span class="badge usb-badge-pill badge-<?= $d['is_midi'] ? 'success' : 'secondary' ?>">
                                                MIDI: <?= $d['is_midi'] ? 'Yes' : 'No' ?>
                                            </span>
                                            <span class="badge usb-badge-pill badge-light border text-muted">
                                                SN: <?= esc($d['serial']) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="border-top pt-2 mt-3 text-muted d-flex justify-content-between align-items-center" style="font-size: 11px;">
                                        <span>
                                            <i class="fas fa-clock mr-1"></i>Connected: <?= esc($d['connected_time']) ?>
                                        </span>
                                        <span>
                                            <i class="fas fa-calendar-alt mr-1"></i>Seen: <?= $d['extracted'] ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </section>
</div>
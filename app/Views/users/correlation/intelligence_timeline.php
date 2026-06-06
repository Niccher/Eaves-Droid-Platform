<?php
/** @var array $timeline */

$typeCounts = [
    'sms'          => $total_sms ?? 0,
    'call'         => $total_calls ?? 0,
    'notification' => $total_notifications ?? 0,
    'app_usage'    => $total_app_usage ?? 0,
    'activity'     => $total_activities ?? 0,
    'location'     => $total_locations ?? 0,
    'upload'       => $total_media ?? 0,
    'financial'    => 0,
    'other'        => 0
];

$typeConfig = [
    'sms'          => ['cls'=>'ev-sms',    'icon'=>'fas fa-envelope',       'bg'=>'bg-primary', 'label'=>'SMS'],
    'call'         => ['cls'=>'ev-call',   'icon'=>'fas fa-phone',          'bg'=>'bg-success', 'label'=>'Calls'],
    'notification' => ['cls'=>'ev-notif',  'icon'=>'fas fa-bell',           'bg'=>'bg-warning', 'label'=>'Alerts'],
    'app_usage'    => ['cls'=>'ev-app',    'icon'=>'fas fa-mobile-alt',     'bg'=>'bg-indigo',  'label'=>'Apps'],
    'activity'     => ['cls'=>'ev-activ',  'icon'=>'fas fa-running',        'bg'=>'bg-info',    'label'=>'Activity'],
    'location'     => ['cls'=>'ev-loc',    'icon'=>'fas fa-map-marker-alt', 'bg'=>'bg-danger',  'label'=>'Location'],
    'upload'       => ['cls'=>'ev-upload', 'icon'=>'fas fa-upload',         'bg'=>'bg-teal',    'label'=>'Uploads'],
    'financial'    => ['cls'=>'ev-fin',    'icon'=>'fas fa-coins',          'bg'=>'bg-green',   'label'=>'Financial'],
    'other'        => ['cls'=>'ev-other',  'icon'=>'fas fa-info-circle',    'bg'=>'bg-secondary','label'=>'Other'],
];
?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-brain text-warning mr-2"></i> Intelligence Timeline</h1>
                    <p class="text-muted mb-0">Unified correlated event stream — <?= number_format(count($timeline)) ?> events</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= base_url('analysis/advanced_timeline') ?>" class="btn btn-info btn-sm">
                        <i class="fas fa-stream mr-1"></i> View Device Timeline
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (empty($timeline)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-brain fa-3x text-muted mb-3 d-block"></i>
                    <h4 class="text-muted">No events yet</h4>
                    <p class="text-muted">Intelligence events will appear once data is extracted from the device.</p>
                </div>
            <?php else: ?>
                <div class="timeline" id="itl-container">
                    <?php
                    usort($timeline, fn($a,$b) => ($b['time'] ?? 0) <=> ($a['time'] ?? 0));
                    $lastDate = '';

                    foreach ($timeline as $ev):
                        $rawTime  = (int)($ev['time'] ?? 0);
                        $ts       = $rawTime > 9999999999 ? (int)($rawTime / 1000) : $rawTime;
                        $dateStr  = date('d M. Y', $ts);
                        $timeStr  = date('H:i', $ts);
                        $type     = $ev['type'] ?? 'other';
                        $cfg      = $typeConfig[$type] ?? $typeConfig['other'];
                        $icon     = $ev['icon'] ?? $cfg['icon'];
                        $bgClass  = $cfg['bg'];
                        $title    = $ev['title'] ?? 'Event';
                        $body     = $ev['body']  ?? '';

                        if ($type === 'upload') {
                            $cat = strtolower($ev['subtitle'] ?? 'other');
                            if (in_array($cat, ['bluetooth', 'bt'])) { $icon = 'fab fa-bluetooth'; $bgClass = 'bg-primary'; }
                            elseif (in_array($cat, ['files', 'file'])) { $icon = 'fas fa-file-archive'; $bgClass = 'bg-success'; }
                            elseif (in_array($cat, ['apps', 'app'])) { $icon = 'fas fa-mobile-alt'; $bgClass = 'bg-indigo'; }
                            elseif (in_array($cat, ['contacts', 'contact'])) { $icon = 'fas fa-address-book'; $bgClass = 'bg-warning'; }
                            elseif ($cat === 'sms') { $icon = 'fas fa-comments'; $bgClass = 'bg-danger'; }
                            elseif (in_array($cat, ['logs', 'call'])) { $icon = 'fas fa-phone-square'; $bgClass = 'bg-info'; }
                            elseif ($cat === 'location') { $icon = 'fas fa-map-pin'; $bgClass = 'bg-maroon'; }
                            else { $icon = 'fas fa-upload'; $bgClass = 'bg-secondary'; }
                        }

                        if ($type === 'call' && stripos($title, 'missed') !== false) {
                            $bgClass = 'bg-danger';
                        }

                        if ($dateStr !== $lastDate):
                            $lastDate = $dateStr;
                    ?>
                        <!-- timeline time label -->
                        <div class="time-label itl-date-divider">
                            <span class="bg-dark"><?= esc($dateStr) ?></span>
                        </div>
                    <?php endif; ?>
                        
                        <!-- timeline item -->
                        <div class="itl-event" data-type="<?= esc($type) ?>">
                            <i class="<?= esc($icon) ?> <?= $bgClass ?>"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> <?= $timeStr ?></span>
                                <h3 class="timeline-header font-weight-bold"><?= htmlspecialchars($title) ?></h3>
                                <?php if (!empty($body)): ?>
                                    <div class="timeline-body">
                                        <?= htmlspecialchars(mb_strimwidth($body, 0, 220, '…')) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

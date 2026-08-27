<?php
/** @var array $timeline */
/** @var array $pivot */
/** @var string $plan */
/** @var int $timeline_limit */

$catStyles = [
    'calls' => [
        'bg' => '#e2f0d9',
        'fg' => '#2e7d32',
        'label' => 'CALL',
        'icon' => 'fas fa-phone'
    ],
    'sms' => [
        'bg' => '#e8eef8',
        'fg' => '#1a73e8',
        'label' => 'SMS',
        'icon' => 'fas fa-envelope'
    ],
    'apps' => [
        'bg' => '#f1eef6',
        'fg' => '#673ab7',
        'label' => 'APP',
        'icon' => 'fas fa-mobile-alt'
    ],
    'movement' => [
        'bg' => '#fff2cc',
        'fg' => '#e65100',
        'label' => 'MOVEMENT',
        'icon' => 'fas fa-map-marker-alt'
    ],
    'security' => [
        'bg' => '#fce4d6',
        'fg' => '#c00000',
        'label' => 'SECURITY',
        'icon' => 'fas fa-shield-alt'
    ],
    'health' => [
        'bg' => '#fde9d9',
        'fg' => '#e91e63',
        'label' => 'HEALTH',
        'icon' => 'fas fa-heartbeat'
    ],
    'other' => [
        'bg' => '#f5f5f5',
        'fg' => '#616161',
        'label' => 'SYSTEM',
        'icon' => 'fas fa-info-circle'
    ]
];

function tl_ts(int $raw): int {
    return $raw > 9_999_999_999 ? (int)($raw / 1000) : $raw;
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-md-12">
                    <div class="d-flex align-items-center">
                        <h1 class="h2 mb-0">
                            <i class="fas fa-stream text-primary mr-2"></i>
                            Device Timeline
                        </h1>
                        <div class="ml-3">
                            <span class="badge badge-primary border p-2">
                                <i class="fas fa-layer-group mr-1"></i> Intelligence
                            </span>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">Unified chronological event stream across SMS, Calls, Apps, Location safety, Health tracker, and Screen/Keyguard actions.</p>
                    <div class="mt-2">
                        <span class="badge badge-info border p-2 mr-1"><i class="fas fa-history mr-1"></i> Window: <?= esc($history_label ?? '7 days') ?> (<?= esc(ucfirst($plan ?? 'free')) ?>)</span>
                        <span class="badge badge-light border p-2"><i class="fas fa-filter mr-1"></i> Cap: Max <?= esc($timeline_limit) ?> total events</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                
                <!-- Left Column: Unified Timeline Feed -->
                <div class="col-md-8">
                    <!-- Main Timeline Card -->
                    <div class="card card-primary card-outline shadow-sm">
                        <div class="card-header border-0 pb-0">
                            <!-- Category filter buttons -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 10px;">
                                <div class="d-flex flex-wrap align-items-center" style="gap: 6px;" id="filter-buttons">
                                    <button class="btn btn-sm btn-primary active filter-btn" data-filter="all" style="border-radius: 8px; font-weight: 600;">
                                        <i class="fas fa-list mr-1"></i> All
                                    </button>
                                    <button class="btn btn-sm btn-outline-success filter-btn" data-filter="calls" style="border-radius: 8px; font-weight: 600;">
                                        <i class="fas fa-phone mr-1"></i> Calls
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary filter-btn" data-filter="sms" style="border-radius: 8px; font-weight: 600;">
                                        <i class="fas fa-envelope mr-1"></i> SMS
                                    </button>
                                    <button class="btn btn-sm btn-outline-info style-purple filter-btn" data-filter="apps" style="border-radius: 8px; font-weight: 600; color: #673ab7; border-color: #673ab7;">
                                        <i class="fas fa-mobile-alt mr-1"></i> Apps
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning filter-btn" data-filter="movement" style="border-radius: 8px; font-weight: 600; color: #e65100; border-color: #e65100;">
                                        <i class="fas fa-map-marker-alt mr-1"></i> Movement
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger filter-btn" data-filter="security" style="border-radius: 8px; font-weight: 600; color: #c00000; border-color: #c00000;">
                                        <i class="fas fa-shield-alt mr-1"></i> Security
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary filter-btn" data-filter="health" style="border-radius: 8px; font-weight: 600; color: #e91e63; border-color: #e91e63;">
                                        <i class="fas fa-heartbeat mr-1"></i> Health
                                    </button>
                                </div>
                                <div class="input-group input-group-sm" style="max-width: 240px;">
                                    <div class="input-group-prepend"><span class="input-group-text bg-white"><i class="fas fa-search"></i></span></div>
                                    <input type="text" id="timeline-search" class="form-control" placeholder="Search events...">
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <?php if (empty($timeline)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-stream fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No timeline events extracted</h5>
                                    <p class="text-muted mb-0">Chronological records will appear here once the device uploads logs.</p>
                                </div>
                            <?php else: ?>
                                <div class="timeline" id="unified-timeline">
                                    <?php 
                                    $lastDate = ''; 
                                    foreach ($timeline as $ev): 
                                        $ts = tl_ts((int)($ev['time'] ?? 0));
                                        $date = date('D, j M Y', $ts);
                                        $time = date('h:i:s A', $ts);
                                        
                                        $type = $ev['type'] ?? 'other';
                                        $sub = strtolower($ev['subtitle'] ?? $ev['subtype'] ?? '');
                                        
                                        // Category mapping
                                        $cat = 'other';
                                        if ($type === 'call') {
                                            $cat = 'calls';
                                        } elseif ($type === 'sms') {
                                            $cat = 'sms';
                                        } elseif ($type === 'app_usage' || ($type === 'upload' && $sub === 'app')) {
                                            $cat = 'apps';
                                        } elseif ($type === 'activity' || $type === 'location') {
                                            $cat = 'movement';
                                        } elseif ($type === 'keyguard') {
                                            $cat = 'security';
                                        } elseif ($type === 'health') {
                                            $cat = 'health';
                                        }
                                        
                                        $style = $catStyles[$cat] ?? $catStyles['other'];
                                        $icon = $ev['icon'] ?? $style['icon'];
                                        
                                        if ($date !== $lastDate): 
                                            $lastDate = $date; 
                                    ?>
                                            <div class="time-label" data-date="<?= esc($date) ?>">
                                                <span class="bg-dark text-white shadow-sm" style="border-radius: 6px; font-size: 0.8rem;"><?= esc($date) ?></span>
                                            </div>
                                    <?php 
                                        endif; 
                                    ?>
                                        <div class="tl-ev" data-category="<?= esc($cat) ?>">
                                            <!-- Customized contrast-matched circle icon -->
                                            <i class="<?= esc($icon) ?>" style="background-color: <?= $style['bg'] ?> !important; color: <?= $style['fg'] ?> !important; width: 32px; height: 32px; line-height: 32px; text-align: center; border-radius: 50%; font-size: 0.85rem; position: absolute; left: 17px; top: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.15);"></i>
                                            
                                            <div class="timeline-item shadow-none border" style="border-radius: 8px; margin-left: 60px; margin-bottom: 20px; background-color: #fcfcfc;">
                                                <span class="time text-muted"><i class="fas fa-clock mr-1"></i> <?= esc($time) ?></span>
                                                
                                                <h3 class="timeline-header" style="border-bottom: 0; padding: 12px 15px 6px 15px; font-size: 0.95rem; font-weight: 600;">
                                                    <span class="badge mr-2" style="font-size: 0.65rem; background-color: <?= $style['bg'] ?>; color: <?= $style['fg'] ?>; border: 1px solid <?= $style['fg'] ?>30;"><?= esc($style['label']) ?></span>
                                                    <?= $ev['title'] ?? 'Event' ?>
                                                </h3>
                                                
                                                <?php if (!empty($ev['body'])): ?>
                                                    <div class="timeline-body text-muted pt-0 pb-3" style="font-size: 0.88rem; line-height: 1.5; padding: 0 15px;">
                                                        <?= $ev['body'] // output raw containing details and br tags ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($type === 'location' && preg_match('/Lat:\s*([\d.\-]+)\s*Lng:\s*([\d.\-]+)/', $ev['body'] ?? '', $m)): ?>
                                                    <div class="timeline-footer pt-0 pb-3" style="padding: 0 15px;">
                                                        <a href="https://www.google.com/maps?q=<?= esc(trim($m[1]), 'attr') ?>,<?= esc(trim($m[2]), 'attr') ?>" target="_blank" rel="noopener" class="btn btn-xs btn-primary border-radius-sm" style="border-radius: 6px;"><i class="fas fa-map-marked-alt mr-1"></i> Open in Google Maps</a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <div><i class="fas fa-clock bg-gray text-white" style="box-shadow: 0 1px 3px rgba(0,0,0,0.15); width: 32px; height: 32px; line-height: 32px; position: absolute; left: 17px; border-radius: 50%; text-align: center;"></i></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Sidebar Guide & Telemetry Legend -->
                <div class="col-md-4">
                    
                    <!-- Card 1: Active Subscription Gating -->
                    <div class="card card-outline card-primary shadow-sm mb-3">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold text-dark">
                                <i class="fas fa-layer-group text-primary mr-1"></i> Timeline Gating
                            </h3>
                        </div>
                        <div class="card-body py-3 px-3">
                            <p class="text-sm text-muted mb-2">
                                Your account is bound to the <strong class="text-primary"><?= esc(ucfirst($plan ?? 'free')) ?></strong> plan tier limits. Here is how your timeline visibility is currently gated:
                            </p>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between text-xs font-weight-bold mb-1">
                                    <span>Plan Category Capping</span>
                                    <span><?= esc(ucfirst($plan ?? 'free')) ?> Tier</span>
                                </div>
                                <table class="table table-sm table-bordered text-xs mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Plan Tier</th>
                                            <th>Cap Per Source</th>
                                            <th>Max Total Limit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="<?= strtolower($plan ?? '') === 'free' ? 'table-warning font-weight-bold' : '' ?>">
                                            <td>Free Core</td>
                                            <td>5 Events</td>
                                            <td>50 Events</td>
                                        </tr>
                                        <tr class="<?= strtolower($plan ?? '') === 'gold' ? 'table-warning font-weight-bold' : '' ?>">
                                            <td>Gold Advanced</td>
                                            <td>15 Events</td>
                                            <td>100 Events</td>
                                        </tr>
                                        <tr class="<?= strtolower($plan ?? '') === 'platinum' ? 'table-warning font-weight-bold' : '' ?>">
                                            <td>Platinum Deep</td>
                                            <td>25 Events</td>
                                            <td>150 Events</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <span class="text-xs text-muted font-italic"><i class="fas fa-info-circle mr-1"></i> Exceeded events are automatically truncated from oldest to newest.</span>
                        </div>
                    </div>

                    <!-- Card 2: Legend & Explanation -->
                    <div class="card card-outline card-secondary shadow-sm mb-3">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold text-dark">
                                <i class="fas fa-info-circle text-secondary mr-1"></i> Category Legend
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush text-xs">
                                <li class="list-group-item d-flex align-items-start py-2">
                                    <i class="fas fa-phone mr-3 mt-1 text-success" style="font-size: 1.1rem; width: 18px; text-align: center;"></i>
                                    <div>
                                        <strong class="text-dark d-block">Calls</strong>
                                        <span class="text-muted">Displays incoming, outgoing, and missed call durations synced directly from device call logs.</span>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-start py-2">
                                    <i class="fas fa-envelope mr-3 mt-1 text-primary" style="font-size: 1.1rem; width: 18px; text-align: center;"></i>
                                    <div>
                                        <strong class="text-dark d-block">SMS Logs</strong>
                                        <span class="text-muted">Shows incoming and outgoing SMS transmissions, short-codes, and text snippets.</span>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-start py-2">
                                    <i class="fas fa-mobile-alt mr-3 mt-1 text-indigo" style="font-size: 1.1rem; width: 18px; text-align: center;"></i>
                                    <div>
                                        <strong class="text-dark d-block">Apps Usage</strong>
                                        <span class="text-muted">Audits app opens, session durations, new app installs, and system updates.</span>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-start py-2">
                                    <i class="fas fa-map-marker-alt mr-3 mt-1 text-warning" style="font-size: 1.1rem; width: 18px; text-align: center;"></i>
                                    <div>
                                        <strong class="text-dark d-block">Movement</strong>
                                        <span class="text-muted">Matches location scans with active physical state (e.g. walking, vehicle) when they occur at the exact same sync timestamp.</span>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-start py-2">
                                    <i class="fas fa-shield-alt mr-3 mt-1 text-danger" style="font-size: 1.1rem; width: 18px; text-align: center;"></i>
                                    <div>
                                        <strong class="text-dark d-block">Security Logs</strong>
                                        <span class="text-muted">Tracks screen keyguard state transitions (lock, unlock, verification attempts).</span>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-start py-2">
                                    <i class="fas fa-heartbeat mr-3 mt-1 text-pink" style="font-size: 1.1rem; width: 18px; text-align: center; color: #e91e63;"></i>
                                    <div>
                                        <strong class="text-dark d-block">Health Metrics</strong>
                                        <span class="text-muted">Monitors fitness steps (converted to distance KM/Metres), sleeps, and pulse stats.</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Card 3: Telemetry Indicators -->
                    <div class="card card-outline card-info shadow-sm mb-3">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold text-dark">
                                <i class="fas fa-satellite text-info mr-1"></i> Telemetry Indicator Guide
                            </h3>
                        </div>
                        <div class="card-body text-xs py-3 px-3">
                            <ul class="pl-3 mb-0 text-muted" style="line-height:1.6;">
                                <li><strong>Accuracy:</strong> Radius margin of error for coordinates. Low values indicate sharp satellite locks.</li>
                                <li><strong>Speed:</strong> Speed logged at sync time, converted to KM/h.</li>
                                <li><strong>Bearing:</strong> Device heading direction relative to true North (0° - 360°).</li>
                                <li><strong>Altitude:</strong> Logged elevation height measured in Metres above the sea level.</li>
                            </ul>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>
</div>

<script>
$(function(){
    var $timeline = $('#unified-timeline');
    var $search = $('#timeline-search');

    function applyFilter() {
        var selectedFilter = $('#filter-buttons .filter-btn.active').data('filter') || 'all';
        var query = ($search.val() || '').toLowerCase().trim();

        $timeline.find('.tl-ev').each(function() {
            var $el = $(this);
            var cat = $el.data('category') || 'other';
            
            var matchCategory = (selectedFilter === 'all' || cat === selectedFilter);
            var matchSearch = (query === '' || $el.text().toLowerCase().includes(query));
            
            $el.toggle(matchCategory && matchSearch);
        });

        // Hide date label tags with no visible events under them
        $timeline.find('.time-label').each(function() {
            var $label = $(this);
            var $next = $label.next();
            var hasVisibleChildren = false;

            while ($next.length && !$next.is('.time-label')) {
                if ($next.is(':visible')) {
                    hasVisibleChildren = true;
                    break;
                }
                $next = $next.next();
            }
            $label.toggle(hasVisibleChildren);
        });
    }

    // Filter button triggers
    $('#filter-buttons .filter-btn').on('click', function() {
        $('#filter-buttons .filter-btn').removeClass('btn-primary btn-success btn-info btn-warning btn-danger btn-secondary active');
        
        var selected = $(this).data('filter');
        if (selected === 'all') {
            $(this).addClass('btn-primary active');
        } else if (selected === 'calls') {
            $(this).addClass('btn-success active');
        } else if (selected === 'sms') {
            $(this).addClass('btn-primary active');
        } else if (selected === 'apps') {
            $(this).addClass('btn-info active');
        } else if (selected === 'movement') {
            $(this).addClass('btn-warning active');
        } else if (selected === 'security') {
            $(this).addClass('btn-danger active');
        } else {
            $(this).addClass('btn-secondary active');
        }
        
        applyFilter();
    });

    $search.on('input', applyFilter);
    applyFilter();
});
</script>

<?php include __DIR__ . '/../advanced/_adv_style.php'; ?>
<?php include __DIR__ . '/../advanced/_adv_delete_script.php'; ?>
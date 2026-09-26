<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php helper('coalesce'); $rows = coalesce_snapshots($rows, 'device_id'); ?>

<?php
// 1. Process screen logs into daily/hourly slots for the heatmap and modal popups
$heatmapData = array_fill(0, 7, array_fill(0, 24, 0)); // 7 days (0=Sun, 6=Sat), 24 hours
$eventsBySlot = []; // Structured as [day_index][hour_index][] = event_details
$failedCountGlobal = 0;
$maxDensity = 0;

foreach ($rows as $row) {
    // Check for failed unlock attempts
    $unlockSuccess = isset($row['unlock_success']) ? (int)$row['unlock_success'] : 1;
    $failedAttempts = isset($row['failed_attempts']) ? (int)$row['failed_attempts'] : 0;
    if ($unlockSuccess === 0 || $failedAttempts > 0) {
        $failedCountGlobal++;
    }

    $timeMs = isset($row['timestamp']) ? (int)$row['timestamp'] : null;
    if ($timeMs) {
        $sec = floor($timeMs / 1000);
        $dayOfWeek = (int)date('w', $sec); // 0 (Sunday) to 6 (Saturday)
        $hour = (int)date('G', $sec);      // 0 to 23
        
        $heatmapData[$dayOfWeek][$hour]++;
        if ($heatmapData[$dayOfWeek][$hour] > $maxDensity) {
            $maxDensity = $heatmapData[$dayOfWeek][$hour];
        }

        // Store details for interactive modal
        $eventsBySlot[$dayOfWeek][$hour][] = [
            'time_formatted' => date('h:i:s A', $sec),
            'event' => $row['event_type'] ?? 'UNKNOWN',
            'method' => $row['unlock_method'] ?? 'N/A',
            'success' => $unlockSuccess,
            'failed_attempts' => $failedAttempts,
            'brightness' => isset($row['screen_brightness']) ? $row['screen_brightness'] : 'N/A',
            'battery' => isset($row['battery_level']) ? $row['battery_level'] . '%' : 'N/A',
            'strong_auth' => isset($row['strong_auth_required']) ? (int)$row['strong_auth_required'] : 0,
            'doze' => $row['doze_state'] ?? 'N/A',
        ];
    }
}

$daysOfWeekLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
?>

<style>
.heatmap-card {
    border-radius: 8px;
    background: #ffffff;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    margin-top: 15px;
}
.heatmap-wrapper {
    overflow-x: auto;
    padding: 20px;
    width: 100%;
}
.heatmap-grid {
    display: grid;
    grid-template-columns: 60px repeat(24, 1fr);
    gap: 4px;
    min-width: 760px;
}
.heatmap-header-cell {
    font-size: 11px;
    color: #6c757d;
    text-align: center;
    font-weight: 600;
}
.heatmap-row-label {
    font-size: 11px;
    color: #495057;
    font-weight: 700;
    display: flex;
    align-items: center;
}
.heatmap-cell {
    aspect-ratio: 1;
    background-color: #ebedf0;
    border-radius: 4px;
    min-height: 24px;
    transition: all 0.15s ease;
    cursor: pointer;
    position: relative;
    border: 1px solid rgba(0,0,0,0.03);
}
.heatmap-cell:hover {
    filter: brightness(0.9);
    transform: scale(1.15);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    z-index: 10;
}
/* Color intensities */
.intensity-0 { background-color: #ebedf0; }
.intensity-1 { background-color: #d1ecf1; } /* Info level light blue */
.intensity-2 { background-color: #7abaff; } /* Active blue */
.intensity-3 { background-color: #007bff; } /* Strong blue */
.intensity-4 { background-color: #004085; } /* Deep security blue */

.callout-info-custom {
    border-left: 5px solid #17a2b8;
    background: #fdfdfd;
    border-radius: 4px;
}
</style>

<div class="content-wrapper">
    <!-- Keep standard content-header at the top -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="h3 mb-0 font-weight-bold text-dark">
                        <i class="fas fa-desktop text-secondary mr-2"></i>Screen Activity Intelligence
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <?= $nav_urls ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Grid -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Dynamic Callout explanation box -->
            <div class="callout callout-info callout-info-custom shadow-sm p-3 mb-4">
                <h5 class="font-weight-bold text-info"><i class="fas fa-info-circle mr-2"></i> About Screen Activity Intelligence</h5>
                <p class="text-secondary mb-2" style="font-size: 14px;">
                    This dashboard maps device screen usage events (ON/OFF logs and lock/unlock metrics) over a 24-hour, 7-day routine matrix. Understanding device patterns helps identify unauthorized physical device handling or credential tampering.
                </p>
                <div class="row mt-2" style="font-size: 12px;">
                    <div class="col-md-6 border-right">
                        <span class="font-weight-bold text-dark d-block mb-1">Color Intensity Legend:</span>
                        <div class="d-flex align-items-center flex-wrap">
                            <span class="mr-2">No activity</span>
                            <div class="heatmap-cell intensity-0 mr-2" style="width: 14px; height: 14px; min-height: 14px; cursor: default;"></div>
                            <div class="heatmap-cell intensity-1 mr-2" style="width: 14px; height: 14px; min-height: 14px; cursor: default;"></div>
                            <div class="heatmap-cell intensity-2 mr-2" style="width: 14px; height: 14px; min-height: 14px; cursor: default;"></div>
                            <div class="heatmap-cell intensity-3 mr-2" style="width: 14px; height: 14px; min-height: 14px; cursor: default;"></div>
                            <div class="heatmap-cell intensity-4 mr-2" style="width: 14px; height: 14px; min-height: 14px; cursor: default;"></div>
                            <span>Maximum activity</span>
                        </div>
                    </div>
                    <div class="col-md-6 pl-md-3 mt-2 mt-md-0">
                        <span class="font-weight-bold text-dark d-block mb-1">Interactive Operations:</span>
                        <ul class="pl-3 mb-0 text-muted">
                            <li>Hover over a block to view activity event count.</li>
                            <li><b>Click on any colored cell block</b> to view detailed timeline entries for that specific hour.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Global Security warning banner -->
            <?php if ($failedCountGlobal > 0): ?>
                <div class="alert alert-danger shadow-sm mb-4">
                    <h5><i class="icon fas fa-shield-alt"></i> Security Caution: credential failures detected!</h5>
                    <span>There are <b><?= $failedCountGlobal ?></b> lockscreen failures logged in this dataset. Inspect high-activity hours to locate these attempts.</span>
                </div>
            <?php endif; ?>

            <!-- Heatmap Card (Replaces the generic table) -->
            <div class="card heatmap-card card-outline card-primary shadow-sm">
                <div class="card-header border-bottom-0">
                    <h3 class="card-title font-weight-bold text-secondary">
                        <i class="fas fa-clock mr-2"></i> Weekly Routine Heatmap
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="heatmap-wrapper">
                        <div class="heatmap-grid">
                            <!-- Hours column headers -->
                            <div></div>
                            <?php for ($h = 0; $h < 24; $h++): ?>
                                <div class="heatmap-header-cell" title="<?= $h ?>:00"><?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>h</div>
                            <?php endfor; ?>

                            <!-- Weekdays rows -->
                            <?php foreach ($daysOfWeekLabels as $dIdx => $dayName): ?>
                                <div class="heatmap-row-label"><?= $dayName ?></div>
                                <?php for ($h = 0; $h < 24; $h++): 
                                    $count = $heatmapData[$dIdx][$h];
                                    $intensity = 0;
                                    if ($count > 0 && $maxDensity > 0) {
                                        $pct = $count / $maxDensity;
                                        if ($pct > 0.75) $intensity = 4;
                                        elseif ($pct > 0.50) $intensity = 3;
                                        elseif ($pct > 0.25) $intensity = 2;
                                        else $intensity = 1;
                                    }
                                    
                                    // Serialize the events for this slot to pass to client-side modal
                                    $slotEventsJson = isset($eventsBySlot[$dIdx][$h]) ? json_encode($eventsBySlot[$dIdx][$h]) : '[]';
                                    ?>
                                    <div class="heatmap-cell intensity-<?= $intensity ?>" 
                                         title="<?= $dayName ?> at <?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>:00 (<?= $count ?> events)"
                                         data-day="<?= $dayName ?>"
                                         data-hour="<?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>:00"
                                         data-events='<?= htmlspecialchars($slotEventsJson, ENT_QUOTES, 'UTF-8') ?>'
                                         onclick="showSlotDetails(this)">
                                    </div>
                                <?php endfor; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- Interactive Detail Modal -->
<div class="modal fade" id="slotDetailModal" tabindex="-1" role="dialog" aria-labelledby="slotDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title font-weight-bold" id="slotDetailModalLabel">
                    <i class="fas fa-history mr-2"></i> Activity Log: <span id="modalDateTime"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Time</th>
                                <th>Event Type</th>
                                <th>Method</th>
                                <th>Result</th>
                                <th>Brightness</th>
                                <th>Battery</th>
                                <th>Doze State</th>
                            </tr>
                        </thead>
                        <tbody id="modalTableBody">
                            <!-- Populated dynamically via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showSlotDetails(element) {
    const day = element.getAttribute('data-day');
    const hour = element.getAttribute('data-hour');
    const events = JSON.parse(element.getAttribute('data-events') || '[]');

    document.getElementById('modalDateTime').textContent = `${day}s at ${hour}`;
    const tbody = document.getElementById('modalTableBody');
    tbody.innerHTML = '';

    if (events.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                    <i class="fas fa-info-circle mr-1"></i> No events captured in this hour slot.
                </td>
            </tr>
        `;
    } else {
        events.forEach(ev => {
            const resultBadge = ev.success == 1 
                ? '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Success</span>' 
                : `<span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i>Failed (${ev.failed_attempts} attempts)</span>`;

            const eventBadge = ev.event.includes('OFF') || ev.event.includes('off')
                ? '<span class="badge badge-danger">SCREEN_OFF</span>'
                : '<span class="badge badge-success">SCREEN_ON</span>';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="font-weight-bold">${ev.time_formatted}</td>
                <td>${eventBadge}</td>
                <td><span class="badge badge-info">${ev.method}</span></td>
                <td>${resultBadge}</td>
                <td><i class="fas fa-sun text-warning mr-1"></i>${ev.brightness}</td>
                <td><i class="fas fa-battery-half text-success mr-1"></i>${ev.battery}</td>
                <td><span class="text-muted">${ev.doze}</span></td>
            `;
            tbody.appendChild(tr);
        });
    }

    $('#slotDetailModal').modal('show');
}
</script>

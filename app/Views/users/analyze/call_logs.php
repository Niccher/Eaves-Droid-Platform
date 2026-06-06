<?php date_default_timezone_set('Africa/Nairobi'); ?>
<div class="content-wrapper" style="background:#f0f2f5;">
    <!-- Header -->
    <section class="content-header" style="background:#fff;border-bottom:1px solid #e5e7eb;padding:14px 20px;">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:10px;">
                <div class="d-flex align-items-center" style="gap:12px;">
                    <a href="<?= base_url('contacts') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div class="d-flex align-items-center" style="gap:10px;">
                        <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#059669,#34d399);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;font-weight:700;flex-shrink:0;">
                            <?= strtoupper(substr($log_saved ?? 'U', 0, 1)) ?>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:1rem;color:#111;"><?= htmlspecialchars($log_saved ?? 'Unknown') ?></div>
                            <div style="font-size:0.78rem;color:#6b7280;"><?= htmlspecialchars($log_person ?? '') ?></div>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center" style="gap:8px;">
                    <!-- Stats -->
                    <?php
                    $incoming = 0; $outgoing = 0; $missed = 0; $totalSecs = 0;
                    foreach ($log_thread as $l) {
                        $t = $l['Type'] ?? '';
                        if ($t === 'Incoming') $incoming++;
                        elseif ($t === 'Outgoing') $outgoing++;
                        elseif (in_array($t, ['Missed','Rejected','Blocked'])) $missed++;
                        $totalSecs += (int)($l['Durations'] ?? 0);
                    }
                    $totalDur = sprintf('%dh %dm', floor($totalSecs/3600), floor(($totalSecs%3600)/60));
                    ?>
                    <span class="badge" style="background:#d1fae5;color:#065f46;padding:6px 12px;border-radius:20px;font-size:0.8rem;">
                        <i class="fas fa-phone-alt mr-1"></i> <?= count($log_thread) ?> calls
                    </span>
                    <?php if (!empty($number_variants)): ?>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" style="border-radius:8px;font-size:0.78rem;">
                            <i class="fas fa-phone mr-1"></i> Number variants
                        </button>
                        <div class="dropdown-menu dropdown-menu-right p-2" style="min-width:200px;">
                            <?php foreach ($number_variants as $v): ?>
                                <div class="d-flex align-items-center py-1 px-2" style="font-size:0.82rem;font-family:monospace;color:#374151;">
                                    <i class="fas fa-check-circle text-success mr-2" style="font-size:0.7rem;"></i><?= htmlspecialchars($v) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="content" style="padding:20px;">
        <div class="container-fluid">

            <?php if (empty($log_thread)): ?>
            <div class="text-center py-5">
                <div style="width:72px;height:72px;border-radius:50%;background:#f3f4f6;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="fas fa-phone-slash" style="font-size:1.8rem;color:#9ca3af;"></i>
                </div>
                <h5 style="color:#374151;">No call logs found</h5>
                <p class="text-muted" style="font-size:0.88rem;">No calls found for any of the number variants searched.</p>
            </div>
            <?php else: ?>

            <!-- Summary cards -->
            <div class="row mb-3" style="max-width:760px;margin:0 auto 16px;">
                <div class="col-6 col-md-3 mb-2">
                    <div style="background:#fff;border-radius:12px;padding:12px 14px;box-shadow:0 1px 3px rgba(0,0,0,0.07);text-align:center;">
                        <div style="color:#059669;font-size:1.2rem;"><i class="fas fa-arrow-circle-down"></i></div>
                        <div style="font-weight:700;font-size:1.1rem;color:#111;"><?= $incoming ?></div>
                        <div style="font-size:0.72rem;color:#6b7280;">Incoming</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div style="background:#fff;border-radius:12px;padding:12px 14px;box-shadow:0 1px 3px rgba(0,0,0,0.07);text-align:center;">
                        <div style="color:#6366f1;font-size:1.2rem;"><i class="fas fa-arrow-circle-up"></i></div>
                        <div style="font-weight:700;font-size:1.1rem;color:#111;"><?= $outgoing ?></div>
                        <div style="font-size:0.72rem;color:#6b7280;">Outgoing</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div style="background:#fff;border-radius:12px;padding:12px 14px;box-shadow:0 1px 3px rgba(0,0,0,0.07);text-align:center;">
                        <div style="color:#ef4444;font-size:1.2rem;"><i class="fas fa-phone-missed"></i></div>
                        <div style="font-weight:700;font-size:1.1rem;color:#111;"><?= $missed ?></div>
                        <div style="font-size:0.72rem;color:#6b7280;">Missed/Rejected</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div style="background:#fff;border-radius:12px;padding:12px 14px;box-shadow:0 1px 3px rgba(0,0,0,0.07);text-align:center;">
                        <div style="color:#f59e0b;font-size:1.2rem;"><i class="fas fa-clock"></i></div>
                        <div style="font-weight:700;font-size:0.9rem;color:#111;"><?= $totalDur ?></div>
                        <div style="font-size:0.72rem;color:#6b7280;">Total Duration</div>
                    </div>
                </div>
            </div>

            <!-- Call log list -->
            <div style="max-width:760px;margin:0 auto;">
                <?php
                $prevDate = '';
                foreach ($log_thread as $log):
                    $callDate = date('D, d M Y', $log['Timestamp'] / 1000);
                    $callTime = date('H:i', $log['Timestamp'] / 1000);
                    $type     = $log['Type'] ?? 'Unknown';
                    $duration = (int)($log['Durations'] ?? 0);
                    $durStr   = $duration > 0 ? sprintf('%d:%02d', floor($duration / 60), $duration % 60) : '—';

                    $typeConfig = match($type) {
                        'Incoming' => ['icon' => 'fa-arrow-circle-down', 'color' => '#059669', 'bg' => '#d1fae5', 'label' => 'Incoming'],
                        'Outgoing' => ['icon' => 'fa-arrow-circle-up',   'color' => '#6366f1', 'bg' => '#ede9fe', 'label' => 'Outgoing'],
                        'Missed'   => ['icon' => 'fa-phone-missed',      'color' => '#ef4444', 'bg' => '#fee2e2', 'label' => 'Missed'],
                        'Rejected' => ['icon' => 'fa-times-circle',      'color' => '#f59e0b', 'bg' => '#fef3c7', 'label' => 'Rejected'],
                        'Blocked'  => ['icon' => 'fa-ban',               'color' => '#6b7280', 'bg' => '#f3f4f6', 'label' => 'Blocked'],
                        default    => ['icon' => 'fa-phone',             'color' => '#6b7280', 'bg' => '#f3f4f6', 'label' => $type],
                    };
                ?>
                    <?php if ($callDate !== $prevDate): $prevDate = $callDate; ?>
                    <div class="text-center my-3">
                        <span style="background:#e5e7eb;color:#6b7280;font-size:0.75rem;padding:4px 14px;border-radius:20px;"><?= $callDate ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex align-items-center mb-2" style="background:#fff;border-radius:12px;padding:12px 16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
                        <div style="width:40px;height:40px;border-radius:50%;background:<?= $typeConfig['bg'] ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-right:14px;">
                            <i class="fas <?= $typeConfig['icon'] ?>" style="color:<?= $typeConfig['color'] ?>;font-size:1rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between">
                                <span style="font-weight:600;font-size:0.9rem;color:#111;"><?= htmlspecialchars($log['Caller'] ?? '') ?></span>
                                <span style="font-size:0.75rem;color:#9ca3af;"><?= $callTime ?></span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-1">
                                <span style="font-size:0.78rem;background:<?= $typeConfig['bg'] ?>;color:<?= $typeConfig['color'] ?>;padding:2px 8px;border-radius:20px;">
                                    <?= $typeConfig['label'] ?>
                                </span>
                                <?php if ($duration > 0): ?>
                                <span style="font-size:0.78rem;color:#6b7280;"><i class="fas fa-clock mr-1"></i><?= $durStr ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="text-center my-4">
                    <span style="background:#f3f4f6;color:#9ca3af;font-size:0.72rem;padding:4px 14px;border-radius:20px;">
                        <i class="fas fa-lock mr-1"></i> End of call history
                    </span>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </section>
</div>

<style>
.content-wrapper { min-height: 100vh; }
</style>

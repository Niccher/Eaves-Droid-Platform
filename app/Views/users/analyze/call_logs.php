<?php date_default_timezone_set('Africa/Nairobi'); ?>
<div class="content-wrapper">
    <!-- Header -->
    <section class="content-header">
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

            <!-- Stats row -->
            <div class="row mb-3">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info shadow-sm">
                        <div class="inner">
                            <h3><?= count($log_thread) ?></h3>
                            <p>Total Calls</p>
                        </div>
                        <div class="icon"><i class="fas fa-phone-alt"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success shadow-sm">
                        <div class="inner">
                            <h3><?= $incoming ?></h3>
                            <p>Incoming Calls</p>
                        </div>
                        <div class="icon"><i class="fas fa-arrow-circle-down"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary shadow-sm">
                        <div class="inner">
                            <h3><?= $outgoing ?></h3>
                            <p>Outgoing Calls</p>
                        </div>
                        <div class="icon"><i class="fas fa-arrow-circle-up"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning shadow-sm">
                        <div class="inner">
                            <h3><?= $totalDur ?></h3>
                            <p>Total Duration</p>
                        </div>
                        <div class="icon"><i class="fas fa-clock"></i></div>
                    </div>
                </div>
            </div>

            <!-- 2-Column layout wrapper -->
            <div class="row">
                <div class="col-md-8">
                    <!-- Call log list card -->
                    <div class="card card-secondary card-outline shadow-sm" style="border-radius:12px; overflow:hidden;">
                        <div class="card-body" style="background:#f0f2f5; padding: 20px; min-height: 500px;">
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

                    <div class="d-flex mb-2 <?= ($type === 'Outgoing') ? 'justify-content-end' : 'justify-content-start' ?>">
                        <?php if ($type !== 'Outgoing'): ?>
                        <!-- Contact avatar on left for incoming/missed -->
                        <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#059669,#34d399);display:flex;align-items:center;justify-content:center;color:#fff;font-size:0.75rem;font-weight:700;flex-shrink:0;align-self:flex-end;margin-right:8px;">
                            <?= strtoupper(substr($log_saved ?? 'U', 0, 1)) ?>
                        </div>
                        <?php endif; ?>

                        <div style="max-width:72%; min-width: 260px;">
                            <div style="
                                background: <?= ($type === 'Outgoing') ? 'linear-gradient(135deg,#6366f1,#818cf8)' : '#fff' ?>;
                                color: <?= ($type === 'Outgoing') ? '#fff' : '#111' ?>;
                                padding: 10px 14px;
                                border-radius: <?= ($type === 'Outgoing') ? '18px 4px 18px 18px' : '4px 18px 18px 18px' ?>;
                                box-shadow: 0 1px 3px rgba(0,0,0,0.08);
                                font-size: 0.9rem;
                                line-height: 1.4;
                            ">
                                <div class="d-flex align-items-center justify-content-between mb-1" style="gap: 15px;">
                                    <span style="font-weight:700; font-size:0.85rem; color: <?= ($type === 'Outgoing') ? '#fff' : '#374151' ?>;">
                                        <i class="fas <?= $typeConfig['icon'] ?> mr-1" style="color: <?= ($type === 'Outgoing') ? '#fff' : $typeConfig['color'] ?>;"></i>
                                        <?= $typeConfig['label'] ?>
                                    </span>
                                    <span style="font-size:0.75rem; color: <?= ($type === 'Outgoing') ? '#e0e7ff' : '#9ca3af' ?>;"><?= $callTime ?></span>
                                </div>
                                <div style="font-size:0.82rem; color: <?= ($type === 'Outgoing') ? '#f3f4f6' : '#6b7280' ?>;">
                                    <?= htmlspecialchars($log['Caller'] ?? '') ?>
                                </div>
                                <?php if ($duration > 0): ?>
                                <div class="mt-1" style="font-size:0.8rem; font-weight: 600; color: <?= ($type === 'Outgoing') ? '#fff' : '#10b981' ?>;">
                                    <i class="fas fa-clock mr-1"></i><?= $durStr ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($type === 'Outgoing'): ?>
                        <!-- Self/Owner avatar on right for outgoing calls -->
                        <div style="width:30px;height:30px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;color:#6b7280;font-size:0.75rem;flex-shrink:0;align-self:flex-end;margin-left:8px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <div class="text-center my-4">
                    <span style="background:#f3f4f6;color:#9ca3af;font-size:0.72rem;padding:4px 14px;border-radius:20px;">
                        <i class="fas fa-lock mr-1"></i> End of call history
                    </span>
                </div>
                        </div>
                    </div>
                </div> <!-- col-md-8 -->

                <!-- Contact profile column -->
                <div class="col-md-4">
                    <div class="card card-primary card-outline shadow-sm" style="border-radius:12px;">
                        <div class="card-body">
                            <div class="text-center pb-3 border-bottom mb-3">
                                <div style="width:70px;height:70px;border-radius:50%;background:linear-gradient(135deg,#059669,#34d399);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.8rem;font-weight:700;margin:0 auto 12px;box-shadow:0 2px 5px rgba(0,0,0,0.1);">
                                    <?= strtoupper(substr($contact['Name'] ?? 'U', 0, 1)) ?>
                                </div>
                                <h5 class="font-weight-bold mb-1"><?= htmlspecialchars($contact['Name'] ?? 'Unknown') ?></h5>
                                <p class="text-muted small mb-0"><i class="fas fa-phone mr-1"></i><?= htmlspecialchars($contact['Number'] ?? '') ?></p>
                                <?php if (!empty($contact['is_favorite'])): ?>
                                    <span class="badge badge-warning mt-2"><i class="fas fa-star mr-1"></i> Favorite</span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <h6 class="text-xs text-uppercase text-muted font-weight-bold mb-2">Forensic Summary</h6>
                                <div class="list-group list-group-unbordered">
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-0 border-top-0" style="font-size:0.85rem;">
                                        <span class="text-muted"><i class="fas fa-sms mr-2 text-info"></i>Total SMS</span>
                                        <span class="badge badge-info font-weight-bold"><?= count($sms_thread) ?></span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-0" style="font-size:0.85rem;">
                                        <span class="text-muted"><i class="fas fa-phone-alt mr-2 text-success"></i>Total Calls</span>
                                        <span class="badge badge-success font-weight-bold"><?= count($log_thread) ?></span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-0" style="font-size:0.85rem;">
                                        <span class="text-muted"><i class="fas fa-chart-line mr-2 text-primary"></i>Interaction Index</span>
                                        <span class="badge badge-primary font-weight-bold"><?= (int)($contact['contact_frequency'] ?? (count($sms_thread) + count($log_thread))) ?></span>
                                    </div>
                                    <?php if (!empty($contact['last_contacted'])): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-0" style="font-size:0.85rem;">
                                        <span class="text-muted"><i class="fas fa-history mr-2 text-warning"></i>Last Contacted</span>
                                        <span class="small text-muted font-weight-bold"><?= date('d M Y, H:i', strtotime($contact['last_contacted'])) ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <h6 class="text-xs text-uppercase text-muted font-weight-bold mb-2">Identity Details</h6>
                                <div style="font-size:0.85rem;">
                                    <?php if (!empty($contact['nickname'])): ?>
                                        <div class="mb-2"><strong>Nickname:</strong> <span class="text-muted"><?= htmlspecialchars($contact['nickname']) ?></span></div>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    $emails = !empty($contact['emails']) ? json_decode($contact['emails'], true) : [];
                                    if (!empty($emails)): ?>
                                        <div class="mb-2"><strong>Emails:</strong> 
                                            <ul class="pl-3 mb-0 text-muted">
                                                <?php foreach ($emails as $em): $eVal = is_array($em) ? ($em['address'] ?? '') : $em; ?>
                                                    <?php if ($eVal): ?><li><?= htmlspecialchars($eVal) ?></li><?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <?php 
                                    $companies = !empty($contact['companies']) ? json_decode($contact['companies'], true) : [];
                                    if (!empty($companies)): ?>
                                        <div class="mb-2"><strong>Organization:</strong>
                                            <ul class="pl-3 mb-0 text-muted">
                                                <?php foreach ($companies as $c): $cName = is_array($c) ? ($c['company'] ?? '') : $c; ?>
                                                    <?php if ($cName): ?><li><?= htmlspecialchars($cName) ?></li><?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <?php 
                                    $addresses = !empty($contact['addresses']) ? json_decode($contact['addresses'], true) : [];
                                    if (!empty($addresses)): ?>
                                        <div class="mb-2"><strong>Address:</strong>
                                            <ul class="pl-3 mb-0 text-muted">
                                                <?php foreach ($addresses as $a): $aVal = is_array($a) ? ($a['formatted_address'] ?? '') : $a; ?>
                                                    <?php if ($aVal): ?><li><?= htmlspecialchars($aVal) ?></li><?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($contact['notes'])): ?>
                                        <div class="mb-2"><strong>Notes:</strong> <p class="text-muted mb-0 bg-light p-2 rounded" style="font-size:0.8rem;"><?= htmlspecialchars($contact['notes']) ?></p></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div>
                                <h6 class="text-xs text-uppercase text-muted font-weight-bold mb-2">Linked Accounts</h6>
                                <?php 
                                $accounts = !empty($contact['raw_contact_account_name']) ? json_decode($contact['raw_contact_account_name'], true) : [];
                                if (!empty($accounts)): ?>
                                    <div class="d-flex flex-wrap" style="gap:5px;">
                                        <?php foreach ($accounts as $acc): ?>
                                            <span class="badge badge-light border px-2 py-1" style="font-size:0.75rem;"><i class="fas fa-network-wired mr-1 text-muted"></i><?= htmlspecialchars($acc) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">No linked account metadata</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- row -->
            <?php endif; ?>

        </div>
    </section>
</div>

<style>
.content-wrapper { min-height: 100vh; }
</style>

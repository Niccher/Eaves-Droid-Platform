<?php
/** @var array $basic_timeline */
/** @var array $advanced_timeline */
/** @var string $adv_filter */
/** @var int $adv_total */
/** @var int $adv_page */
/** @var string $default_tab */

$tlColors = [
    'sms'       => '#3b82f6',
    'call'      => '#10b981',
    'activity'  => '#06b6d4',
    'location'  => '#f59e0b',
    'app_usage' => '#8b5cf6',
    'upload'    => '#14b8a6',
    'file'      => '#64748b',
    'health'    => '#ec4899',
    'keyguard'  => '#334155',
    'other'     => '#6b7280',
];
$tlIcons = [
    'sms'       => 'fas fa-envelope',
    'call'      => 'fas fa-phone',
    'activity'  => 'fas fa-running',
    'location'  => 'fas fa-map-marker-alt',
    'app_usage' => 'fas fa-mobile-alt',
    'upload'    => 'fas fa-upload',
    'file'      => 'fas fa-file-alt',
    'health'    => 'fas fa-heartbeat',
    'keyguard'  => 'fas fa-shield-alt',
    'other'     => 'fas fa-info-circle',
];
$tlLabels = [
    'sms'       => 'SMS',
    'call'      => 'Call',
    'activity'  => 'Activity',
    'location'  => 'Location',
    'app_usage' => 'App',
    'upload'    => 'Upload',
    'file'      => 'File',
    'health'    => 'Health',
    'keyguard'  => 'Keyguard',
    'other'     => 'Event',
];

function tl_ts(int $raw): int { return $raw > 9_999_999_999 ? (int)($raw / 1000) : $raw; }

$bSms    = count(array_filter($basic_timeline ?? [], fn($e) => $e['type'] === 'sms'));
$bCalls  = count(array_filter($basic_timeline ?? [], fn($e) => $e['type'] === 'call'));
$bMissed = count(array_filter($basic_timeline ?? [], fn($e) => $e['type'] === 'call' && ($e['subtype'] ?? '') === 'missed'));

// AdvancedController type counts from current page data
$aCounts = [];
$advExclude = ['sms', 'call'];
if (!empty($advanced_timeline)) {
    foreach ($advanced_timeline as $ev) {
        $t = $ev['type'] ?? 'other';
        if (!in_array($t, $advExclude)) {
            $aCounts[$t] = ($aCounts[$t] ?? 0) + 1;
        }
    }
    arsort($aCounts);
}
$aTotal = $adv_total ?? count($advanced_timeline ?? []);
?>
<style>
.bg-purple { background: #8b5cf6 !important; }
.bg-teal   { background: #14b8a6 !important; }
.bg-indigo { background: #4f46e5 !important; }
.bg-maroon { background: #b45309 !important; color: #fff; }
.info-box-sm { min-height: 70px; border-radius: 8px; margin-bottom: 4px; }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
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
                    <p class="text-muted mt-2 mb-0">Chronological event stream across SMS, Calls, Apps, Locations, Health, and Keyguard state.</p>
                    <div class="mt-2">
                        <span class="badge badge-info border p-2 mr-1"><i class="fas fa-history mr-1"></i> Window: <?= esc($history_label ?? '7 days') ?> (<?= esc(ucfirst($plan ?? 'free')) ?>)</span>
                        <span class="badge badge-light border p-2"><i class="fas fa-th mr-1"></i> Crisis-mode pivot included</span>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 text-right">
                    <!-- reserved for future actions -->
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-secondary card-outline shadow-sm">
                <div class="card-header">
                    <ul class="nav nav-pills" id="tl-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link <?= $default_tab === 'basic' ? 'active' : '' ?>" id="tab-basic" data-toggle="pill" href="#pane-basic" role="tab">
                                <i class="fas fa-comment-alt mr-1"></i> Basic Timeline
                                <span class="badge badge-light ml-1"><?= number_format($bSms + $bCalls) ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $default_tab === 'advanced' ? 'active' : '' ?>" id="tab-advanced" data-toggle="pill" href="#pane-advanced" role="tab">
                                <i class="fas fa-layer-group mr-1"></i> AdvancedController Timeline
                                <span class="badge badge-dark ml-1"><?= number_format($aTotal) ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-pivot" data-toggle="pill" href="#pane-pivot" role="tab">
                                <i class="fas fa-th mr-1"></i> Daily Pivot
                                <span class="badge badge-light ml-1"><?= number_format(count($pivot['rows'] ?? [])) ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="tl-panes">

                        <!-- ═══════ BASIC: SMS + Calls ═══════ -->
                        <div class="tab-pane fade <?= $default_tab === 'basic' ? 'show active' : '' ?>" id="pane-basic" role="tabpanel">
                            <div class="mb-3 d-flex align-items-center flex-wrap" style="gap:6px;">
                                <small class="text-muted font-weight-bold mr-1"><i class="fas fa-filter mr-1"></i>Filter:</small>
                                <button class="btn btn-sm bf-btn active" data-f="all" style="border-radius:8px; font-weight:600;">
                                    <i class="fas fa-list mr-1"></i> All
                                </button>
                                <button class="btn btn-sm bf-btn" data-f="sms" style="border-radius:8px; font-weight:600; color:<?= $tlColors['sms'] ?>;">
                                    <i class="fas fa-envelope mr-1"></i> SMS <span class="badge ml-1" style="background:<?= $tlColors['sms'] ?>; color:#fff;"><?= $bSms ?></span>
                                </button>
                                <button class="btn btn-sm bf-btn" data-f="call" style="border-radius:8px; font-weight:600; color:<?= $tlColors['call'] ?>;">
                                    <i class="fas fa-phone mr-1"></i> Calls <span class="badge ml-1" style="background:<?= $tlColors['call'] ?>; color:#fff;"><?= $bCalls ?></span>
                                </button>
                                <button class="btn btn-sm bf-btn" data-f="missed" style="border-radius:8px; font-weight:600; color:#dc2626;">
                                    <i class="fas fa-phone-slash mr-1"></i> Missed <span class="badge ml-1" style="background:#dc2626; color:#fff;"><?= $bMissed ?></span>
                                </button>
                                <div class="input-group input-group-sm ml-auto" style="max-width:260px;">
                                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
                                    <input type="text" id="bsrch" class="form-control" placeholder="Search events...">
                                </div>
                            </div>

                            <?php if (empty($basic_timeline)): ?>
                            <div class="text-center py-5"><i class="fas fa-comment-slash fa-3x text-muted mb-3 d-block"></i><h5 class="text-muted">No communication events yet</h5><p class="text-muted">SMS and call records will appear once data is extracted.</p></div>
                            <?php else: ?>
                            <div class="timeline" id="basic-tl">
                                <?php $lastBDate = ''; foreach ($basic_timeline as $ev): $ts = tl_ts((int)($ev['time'] ?? 0)); $date = date('D, j M Y', $ts); $time = date('H:i', $ts); $type = $ev['type'] ?? 'other'; $sub = $ev['subtype'] ?? $type; $icon = $ev['icon'] ?? $tlIcons[$type] ?? 'fas fa-info-circle'; $bgColor = $tlColors[$type] ?? '#6b7280'; $tit = $ev['title'] ?? 'Event'; $bod = $ev['body'] ?? ''; $cont = $ev['meta'] ?? ''; $flt = $sub === 'missed' ? 'missed' : $type; if ($date !== $lastBDate): $lastBDate = $date; ?>
                                <div class="time-label" data-filters="<?= esc($type . ' ' . $sub) ?>"><span class="bg-dark"><?= esc($date) ?></span></div>
                                <?php endif; ?>
                                <div class="tl-ev" data-filters="<?= esc($flt) ?>">
                                    <i class="<?= esc($icon) ?>" style="background:<?= $bgColor ?>; color:#fff; padding:8px; border-radius:50%; font-size:0.85rem;"></i>
                                    <div class="timeline-item">
                                        <span class="time"><i class="fas fa-clock"></i> <?= $time ?> <?php if(!empty($cont)): ?><span class="ml-2 text-muted" style="font-size:0.73rem;"><i class="fas fa-user mr-1"></i><?= esc($cont) ?></span><?php endif; ?></span>
                                        <h3 class="timeline-header">
                                            <span class="badge" style="font-size:0.68rem; background:<?= $bgColor ?>; color:#fff;"><?= esc(strtoupper($sub)) ?></span>
                                            <?= htmlspecialchars($tit) ?>
                                        </h3>
                                        <?php if (!empty($bod)): ?>
                                        <div class="timeline-body text-muted" style="font-size:0.85rem;"><?= htmlspecialchars(mb_strimwidth($bod, 0, 300, '…')) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <div><i class="fas fa-clock bg-gray"></i></div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- ═══════ ADVANCED TIMELINE ═══════ -->
                        <div class="tab-pane fade <?= $default_tab === 'advanced' ? 'show active' : '' ?>" id="pane-advanced" role="tabpanel">
                            <div class="mb-3 d-flex flex-wrap align-items-center" style="gap:6px;">
                                <small class="text-muted font-weight-bold mr-1"><i class="fas fa-filter mr-1"></i>Type:</small>
                                <button class="btn btn-sm af-btn active" data-tp="all" style="border-radius:8px; font-weight:600;">
                                    <i class="fas fa-layer-group mr-1"></i> All <span class="badge badge-light ml-1"><?= number_format($aTotal) ?></span>
                                </button>
                                <?php foreach ($aCounts as $tKey => $tCnt): $c = $tlColors[$tKey] ?? '#6b7280'; $icn = $tlIcons[$tKey] ?? 'fas fa-info-circle'; $lbl = $tlLabels[$tKey] ?? 'Event'; $isActive = ($adv_filter === $tKey); ?>
                                <button class="btn btn-sm af-btn <?= $isActive ? 'active' : '' ?>" data-tp="<?= esc($tKey) ?>" style="font-weight:600; border-radius:8px; color:<?= $c ?>;">
                                    <i class="<?= esc($icn) ?> mr-1"></i> <?= esc($lbl) ?>
                                    <span class="badge ml-1" style="background:<?= $c ?>; color:#fff;"><?= $tCnt ?></span>
                                </button>
                                <?php endforeach; ?>
                            </div>

                            <?php if (empty($advanced_timeline)): ?>
                            <div class="text-center py-5"><i class="fas fa-layer-group fa-3x text-muted mb-3 d-block"></i><h5 class="text-muted">No events found</h5><p class="text-muted">Try a different type filter or check back after more data is imported.</p></div>
                            <?php else: ?>
                            <div class="timeline" id="adv-tl">
                                <?php $lastADate = ''; foreach ($advanced_timeline as $ev): $tsA = tl_ts((int)($ev['time'] ?? 0)); $dateA = date('D, j M Y', $tsA); $timeA = date('H:i:s', $tsA); $tyA = $ev['type'] ?? 'other'; $suA = $ev['subtitle'] ?? ($ev['subtype'] ?? ''); $cA = $tlColors[$tyA] ?? '#6b7280'; $ioA = $ev['icon'] ?? $tlIcons[$tyA] ?? 'fas fa-info-circle'; $lbA = $ev['subtitle'] ?: ($tlLabels[$tyA] ?? 'Event'); $tiA = $ev['title'] ?? 'Event'; $boA = $ev['body'] ?? '';
                                    // upload subtype overrides
                                    if ($tyA === 'upload' && !empty($suA)) {
                                        $cat = strtolower($suA);
                                        if (in_array($cat, ['bluetooth','bt']))       { $ioA = 'fab fa-bluetooth'; }
                                        elseif (in_array($cat, ['files','file']))     { $ioA = 'fas fa-file-archive'; }
                                        elseif (in_array($cat, ['apps','app']))       { $ioA = 'fas fa-mobile-alt'; }
                                        elseif ($cat === 'contacts')                 { $ioA = 'fas fa-address-book'; }
                                        elseif ($cat === 'sms')                      { $ioA = 'fas fa-comments'; }
                                        elseif (in_array($cat, ['logs','call']))     { $ioA = 'fas fa-phone-square'; }
                                        elseif ($cat === 'location')                 { $ioA = 'fas fa-map-pin'; }
                                    }
                                    if ($dateA !== $lastADate): $lastADate = $dateA; ?>
                                <div class="time-label"><span class="bg-dark"><?= esc($dateA) ?></span></div>
                                <?php endif; ?>
                                <div class="adv-ev" data-tp="<?= esc($tyA) ?>">
                                    <i class="<?= esc($ioA) ?>" style="background:<?= $cA ?>; color:#fff; padding:8px; border-radius:50%; font-size:0.82rem;"></i>
                                    <div class="timeline-item">
                                        <span class="time"><i class="fas fa-clock"></i> <?= $timeA ?>
                                            <span class="badge ml-2" style="font-size:0.65rem; background:<?= $cA ?>; color:#fff;"><?= esc(strtoupper($lbA)) ?></span>
                                        </span>
                                        <h3 class="timeline-header"><?= htmlspecialchars($tiA) ?></h3>
                                        <?php if (!empty($boA)): ?><div class="timeline-body text-muted" style="font-size:0.85rem; line-height:1.5;"><?= htmlspecialchars(mb_strimwidth($boA, 0, 300, '…')) ?></div><?php endif; ?>
                                        <?php if ($tyA === 'location' && preg_match('/Lat:\s*([\d.\-]+)\s*Lng:\s*([\d.\-]+)/', $boA, $m)): ?>
                                        <div class="timeline-footer">
                                            <a href="https://www.google.com/maps?q=<?= esc(trim($m[1]), 'attr') ?>,<?= esc(trim($m[2]), 'attr') ?>" target="_blank" rel="noopener" class="btn btn-xs btn-primary"><i class="fas fa-map-marked-alt mr-1"></i> Open in Google Maps</a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <div><i class="fas fa-clock bg-gray"></i></div>
                            </div>
                            <?php endif; ?>

                            <?php if ($aTotal > 0): ?>
                            <div class="card-footer clearfix">
                                <?php
                                $totalPages = (int)ceil($aTotal / $adv_per_page);
                                $curP = $adv_page;
                                $typeQ = $adv_filter === 'all' ? '' : '&type=' . urlencode($adv_filter);
                                $baseUrl = '/analysis/timeline?p=' . ($curP + 1) . $typeQ;
                                ?>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0">
                                        <li class="page-item <?= $curP <= 1 ? 'disabled' : '' ?>">
                                            <a class="page-link" href="/analysis/timeline?p=<?= max(1, $curP - 1) . $typeQ ?>"><i class="fas fa-angle-left"></i></a>
                                        </li>
                                        <?php
                                        $start = max(1, $curP - 2);
                                        $end = min($totalPages, $start + 4);
                                        $start = max(1, $end - 4);
                                        for ($pi = $start; $pi <= $end; $pi++): ?>
                                        <li class="page-item <?= $pi == $curP ? 'active' : '' ?>">
                                            <a class="page-link" href="/analysis/timeline?p=<?= $pi . $typeQ ?>"><?= $pi ?></a>
                                        </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?= $curP >= $totalPages ? 'disabled' : '' ?>">
                                            <a class="page-link" href="/analysis/timeline?p=<?= min($totalPages, $curP + 1) . $typeQ ?>"><i class="fas fa-angle-right"></i></a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- ═══════ DAILY PIVOT ═══════ -->
                        <div class="tab-pane fade" id="pane-pivot" role="tabpanel">
                            <?php $pivotRows = $pivot['rows'] ?? []; $pivotCats = $pivot['categories'] ?? []; ?>
                            <?php if (empty($pivotRows)): ?>
                                <div class="text-center py-5"><i class="fas fa-th fa-3x text-muted mb-3 d-block"></i><h5 class="text-muted">No activity in this window</h5><p class="text-muted">Data will appear here once events are extracted.</p></div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-valign-middle mb-0">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Date</th>
                                            <th class="text-center">Total</th>
                                            <?php foreach ($pivotCats as $cat): ?>
                                                <th class="text-center"><i class="<?= esc($tlIcons[$cat] ?? 'fas fa-circle') ?> mr-1"></i><?= esc($tlLabels[$cat] ?? ucfirst($cat)) ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pivotRows as $row): ?>
                                        <tr>
                                            <td class="font-weight-bold"><?= esc(date('D, j M Y', strtotime($row['date']))) ?></td>
                                            <td class="text-center"><span class="badge badge-dark"><?= number_format($row['total']) ?></span></td>
                                            <?php foreach ($pivotCats as $cat): $c = $tlColors[$cat] ?? '#6b7280'; ?>
                                                <td class="text-center">
                                                    <span class="badge" style="background:<?= $c ?>; color:#fff;"><?= number_format($row[$cat] ?? 0) ?></span>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(function(){
    var $bT = $('#basic-tl'), $s = $('#bsrch');

    function applyBasic(){
        var active = $('#pane-basic .bf-btn.active');
        var f = active.data('f') || 'all';
        var q = ($s.val() || '').toLowerCase().trim();
        $bT.find('.tl-ev').each(function(){
            var $e = $(this);
            var fs = ($e.data('filters') || '').split(' ');
            var matchFilter = (f === 'all' || fs.includes(f));
            var matchSearch = (q === '' || $e.text().toLowerCase().includes(q));
            $e.toggle(matchFilter && matchSearch);
        });
        hideOrphans($bT);
    }

    function hideOrphans($tl) {
        $tl.find('.time-label').each(function() {
            var $x = $(this), n = $x.next(), vis = false;
            while (n.length && !n.is('.time-label')) {
                if (n.is(':visible')) { vis = true; break; }
                n = n.next();
            }
            $x.toggle(vis);
        });
    }

    // Basic filter buttons
    $('#pane-basic .bf-btn').on('click', function() {
        $('#pane-basic .bf-btn').removeClass('btn-primary btn-outline-primary btn-success btn-outline-success btn-danger btn-outline-danger active');
        var f = $(this).data('f');
        if (f === 'all' || f === 'sms') $(this).addClass('btn-primary active');
        else if (f === 'call') $(this).addClass('btn-success active');
        else $(this).addClass('btn-danger active');
        applyBasic();
    });
    $s.on('input', applyBasic);
    hideOrphans($bT);

    // AdvancedController filter buttons (JS only, no reload)
    var $aT = $('#adv-tl');
    function applyAdvanced() {
        var activeBtn = $('#pane-advanced .af-btn.active');
        var tp = activeBtn.data('tp') || 'all';
        $aT.find('.adv-ev').each(function(){
            var $e = $(this);
            var t = $e.data('tp') || 'other';
            var match = (tp === 'all' || t === tp);
            $e.toggle(match);
        });
        hideOrphans($aT);
    }
    $('#pane-advanced .af-btn').on('click', function() {
        $('#pane-advanced .af-btn').removeClass('active');
        $(this).addClass('active');
        // update URL without reload
        var tp = $(this).data('tp');
        var newUrl = tp === 'all' ? '/analysis/timeline' : '/analysis/timeline?type=' + tp;
        history.replaceState({}, '', newUrl);
        applyAdvanced();
    });
    // initial filter based on URL ?type=
    var urlParams = new URLSearchParams(window.location.search);
    var initType = urlParams.get('type') || 'all';
    $('#pane-advanced .af-btn[data-tp="' + initType + '"]').addClass('active').siblings('.af-btn').removeClass('active');
    applyAdvanced();
});
</script>

<?php include __DIR__ . '/../advanced/_adv_style.php'; ?>
<?php include __DIR__ . '/../advanced/_adv_delete_script.php'; ?>
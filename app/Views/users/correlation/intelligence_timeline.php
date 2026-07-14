<?php
/** @var array $basic_timeline    SMS + Call events */
/** @var array $advanced_timeline All event types combined */

// ─── Type config (icon + badge colour) ────────────────────────────────────────
$typeConfig = [
    'sms'       => ['icon' => 'fas fa-envelope',          'bg' => 'bg-primary',   'label' => 'SMS',       'badge' => 'primary'],
    'call'      => ['icon' => 'fas fa-phone',              'bg' => 'bg-success',   'label' => 'Calls',     'badge' => 'success'],
    'activity'  => ['icon' => 'fas fa-running',            'bg' => 'bg-info',      'label' => 'Activity',  'badge' => 'info'],
    'location'  => ['icon' => 'fas fa-map-marker-alt',     'bg' => 'bg-warning',   'label' => 'Location',  'badge' => 'warning'],
    'app_usage' => ['icon' => 'fas fa-mobile-alt',         'bg' => 'bg-indigo',    'label' => 'App Use',   'badge' => 'secondary'],
    'upload'    => ['icon' => 'fas fa-upload',             'bg' => 'bg-teal',      'label' => 'Upload',    'badge' => 'info'],
    'file'      => ['icon' => 'fas fa-file-alt',           'bg' => 'bg-secondary', 'label' => 'File',      'badge' => 'secondary'],
    'financial' => ['icon' => 'fas fa-coins',              'bg' => 'bg-green',     'label' => 'Financial', 'badge' => 'success'],
    'other'     => ['icon' => 'fas fa-info-circle',        'bg' => 'bg-gray',      'label' => 'Other',     'badge' => 'secondary'],
];

// ─── Helper: normalise ms-epoch vs s-epoch ─────────────────────────────────────
function tl_ts(int $raw): int {
    return $raw > 9_999_999_999 ? (int) ($raw / 1000) : $raw;
}

// ─── Count basics ──────────────────────────────────────────────────────────────
$basicSms   = count(array_filter($basic_timeline ?? [],   fn($e) => $e['type'] === 'sms'));
$basicCalls = count(array_filter($basic_timeline ?? [],   fn($e) => $e['type'] === 'call'));
$advTotal   = count($advanced_timeline ?? []);
?>
<div class="content-wrapper">

    <!-- ── Page Header ─────────────────────────────────────────────────────── -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center mb-2">
                <div class="col-sm-7">
                    <h1 class="m-0">
                        <i class="fas fa-stream text-primary mr-2"></i>
                        Device Timeline
                    </h1>
                    <p class="text-muted mt-1 mb-0" style="font-size:.9rem;">
                        Chronological event stream correlated across all data sources
                    </p>
                </div>
                <div class="col-sm-5 text-right">
                    <span class="badge badge-primary px-3 py-2 mr-1" style="font-size:.8rem;">
                        <i class="fas fa-envelope mr-1"></i><?= number_format($basicSms) ?> SMS
                    </span>
                    <span class="badge badge-success px-3 py-2 mr-1" style="font-size:.8rem;">
                        <i class="fas fa-phone mr-1"></i><?= number_format($basicCalls) ?> Calls
                    </span>
                    <span class="badge badge-dark px-3 py-2" style="font-size:.8rem;">
                        <i class="fas fa-layer-group mr-1"></i><?= number_format($advTotal) ?> Advanced Events
                    </span>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <!-- ── Nav Pills ─────────────────────────────────────────────────── -->
            <div class="card shadow-sm mb-0">
                <div class="card-header pb-0 pt-3" style="border-bottom: 2px solid #dee2e6;">
                    <ul class="nav nav-pills" id="timeline-pills" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold" id="pill-basic" data-toggle="pill"
                               href="#pane-basic" role="tab">
                                <i class="fas fa-comment-alt mr-1"></i> Basic Timeline
                                <span class="badge badge-light ml-1"><?= number_format($basicSms + $basicCalls) ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="pill-advanced" data-toggle="pill"
                               href="#pane-advanced" role="tab">
                                <i class="fas fa-layer-group mr-1"></i> Advanced Timeline
                                <span class="badge badge-dark ml-1"><?= number_format($advTotal) ?></span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body pt-3">
                    <div class="tab-content" id="timeline-pills-content">

                        <!-- ════════════════════════════════════════════════════
                             PILL 1 — BASIC TIMELINE
                             SMS + Call events only
                        ════════════════════════════════════════════════════ -->
                        <div class="tab-pane fade show active" id="pane-basic" role="tabpanel">

                            <!-- Sub-filters -->
                            <div class="mb-3 d-flex align-items-center flex-wrap" style="gap:.4rem;">
                                <small class="text-muted mr-1 font-weight-bold">Filter:</small>
                                <button class="btn btn-sm btn-primary tl-filter-btn active" data-filter="all">
                                    <i class="fas fa-list mr-1"></i> All
                                </button>
                                <button class="btn btn-sm btn-outline-primary tl-filter-btn" data-filter="sms">
                                    <i class="fas fa-envelope mr-1"></i> SMS
                                    <span class="badge badge-primary ml-1"><?= $basicSms ?></span>
                                </button>
                                <button class="btn btn-sm btn-outline-success tl-filter-btn" data-filter="call">
                                    <i class="fas fa-phone mr-1"></i> Calls
                                    <span class="badge badge-success ml-1"><?= $basicCalls ?></span>
                                </button>
                                <button class="btn btn-sm btn-outline-danger tl-filter-btn" data-filter="missed">
                                    <i class="fas fa-phone-slash mr-1"></i> Missed
                                </button>
                            </div>

                            <?php if (empty($basic_timeline)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-comment-slash fa-3x text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">No communication events yet</h5>
                                <p class="text-muted">SMS messages and call records will appear once data is extracted.</p>
                            </div>
                            <?php else: ?>

                            <div class="timeline" id="basic-tl">
                                <?php
                                $lastDate = '';
                                foreach ($basic_timeline as $ev):
                                    $ts       = tl_ts((int)($ev['time'] ?? 0));
                                    $dateStr  = date('d M. Y', $ts);
                                    $timeStr  = date('H:i', $ts);
                                    $type     = $ev['type'] ?? 'other';
                                    $subtype  = $ev['subtype'] ?? $type;
                                    $cfg      = $typeConfig[$type] ?? $typeConfig['other'];
                                    $icon     = $ev['icon'] ?? $cfg['icon'];
                                    $bgClass  = $ev['color'] ?? $cfg['bg'];
                                    $title    = $ev['title'] ?? 'Event';
                                    $body     = $ev['body'] ?? '';
                                    $meta     = $ev['meta'] ?? '';

                                    if ($dateStr !== $lastDate):
                                        $lastDate = $dateStr;
                                ?>
                                <div class="time-label tl-date-sep" data-type="<?= esc($type) ?>" data-sub="<?= esc($subtype) ?>">
                                    <span class="bg-dark"><?= esc($dateStr) ?></span>
                                </div>
                                <?php endif; ?>

                                <div class="tl-event" data-type="<?= esc($type) ?>" data-sub="<?= esc($subtype) ?>">
                                    <i class="<?= esc($icon) ?> <?= $bgClass ?>"></i>
                                    <div class="timeline-item">
                                        <span class="time">
                                            <i class="fas fa-clock"></i> <?= $timeStr ?>
                                            <?php if ($meta): ?>
                                            <span class="ml-2 text-muted" style="font-size:.75rem;">
                                                <i class="fas fa-user mr-1"></i><?= esc($meta) ?>
                                            </span>
                                            <?php endif; ?>
                                        </span>
                                        <h3 class="timeline-header">
                                            <span class="badge badge-<?= $cfg['badge'] ?> mr-1" style="font-size:.7rem;">
                                                <?= esc(strtoupper($subtype)) ?>
                                            </span>
                                            <?= htmlspecialchars($title) ?>
                                        </h3>
                                        <?php if (!empty($body)): ?>
                                        <div class="timeline-body text-muted" style="font-size:.85rem;">
                                            <?= htmlspecialchars(mb_strimwidth($body, 0, 280, '…')) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <div><i class="fas fa-clock bg-gray"></i></div>
                            </div>

                            <?php endif; ?>
                        </div><!-- /#pane-basic -->


                        <!-- ════════════════════════════════════════════════════
                             PILL 2 — ADVANCED TIMELINE
                             All event types: files, app launches, system metadata, etc.
                        ════════════════════════════════════════════════════ -->
                        <div class="tab-pane fade" id="pane-advanced" role="tabpanel">

                            <?php
                            // Build type counts for the advanced filter bar
                            $advCounts = [];
                            foreach ($advanced_timeline ?? [] as $ev) {
                                $t = $ev['type'] ?? 'other';
                                $advCounts[$t] = ($advCounts[$t] ?? 0) + 1;
                            }
                            arsort($advCounts);
                            ?>

                            <!-- Sub-type filter pills -->
                            <div class="mb-3 d-flex align-items-center flex-wrap" style="gap:.4rem;">
                                <small class="text-muted mr-1 font-weight-bold">Filter:</small>
                                <button class="btn btn-sm btn-dark adv-filter-btn active" data-filter="all">
                                    <i class="fas fa-layer-group mr-1"></i> All
                                    <span class="badge badge-light ml-1"><?= $advTotal ?></span>
                                </button>
                                <?php foreach ($advCounts as $t => $cnt):
                                    $tcfg = $typeConfig[$t] ?? $typeConfig['other'];
                                    $bdg  = $tcfg['badge'];
                                ?>
                                <button class="btn btn-sm btn-outline-<?= $bdg ?> adv-filter-btn" data-filter="<?= esc($t) ?>">
                                    <i class="<?= $tcfg['icon'] ?> mr-1"></i>
                                    <?= esc($tcfg['label']) ?>
                                    <span class="badge badge-<?= $bdg ?> ml-1"><?= $cnt ?></span>
                                </button>
                                <?php endforeach; ?>
                            </div>

                            <!-- Legend -->
                            <div class="alert alert-light border py-2 px-3 mb-3" style="font-size:.8rem;">
                                <i class="fas fa-info-circle text-info mr-1"></i>
                                The Advanced Timeline merges <strong>SMS, Calls, Location check-ins, Physical Activity changes,
                                App Usage sessions, File uploads</strong> and other system-level metadata into a single
                                chronological stream — giving you complete context of what occurred on the device.
                            </div>

                            <?php if (empty($advanced_timeline)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-layer-group fa-3x text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">No advanced events yet</h5>
                                <p class="text-muted">Events will appear as device data is extracted and processed.</p>
                            </div>
                            <?php else: ?>

                            <div class="timeline" id="adv-tl">
                                <?php
                                usort($advanced_timeline, fn($a, $b) => ($b['time'] ?? 0) <=> ($a['time'] ?? 0));
                                $lastDateAdv = '';
                                foreach ($advanced_timeline as $ev):
                                    $ts      = tl_ts((int)($ev['time'] ?? 0));
                                    $dateStr = date('d M. Y', $ts);
                                    $timeStr = date('H:i:s', $ts);
                                    $type    = $ev['type'] ?? 'other';
                                    $subtype = $ev['subtitle'] ?? ($ev['subtype'] ?? '');
                                    $cfg     = $typeConfig[$type] ?? $typeConfig['other'];

                                    // Upload icon overrides
                                    $icon    = $ev['icon'] ?? $cfg['icon'];
                                    $bgClass = $ev['color'] ?? $cfg['bg'];
                                    if ($type === 'upload' && !empty($subtype)) {
                                        $cat = strtolower($subtype);
                                        if (in_array($cat, ['bluetooth','bt']))     { $icon = 'fab fa-bluetooth';       $bgClass = 'bg-primary'; }
                                        elseif (in_array($cat, ['files','file']))   { $icon = 'fas fa-file-archive';    $bgClass = 'bg-success'; }
                                        elseif (in_array($cat, ['apps','app']))     { $icon = 'fas fa-mobile-alt';      $bgClass = 'bg-indigo';  }
                                        elseif (in_array($cat, ['contacts']))       { $icon = 'fas fa-address-book';    $bgClass = 'bg-warning'; }
                                        elseif ($cat === 'sms')                     { $icon = 'fas fa-comments';        $bgClass = 'bg-danger';  }
                                        elseif (in_array($cat, ['logs','call']))    { $icon = 'fas fa-phone-square';    $bgClass = 'bg-info';    }
                                        elseif ($cat === 'location')                { $icon = 'fas fa-map-pin';         $bgClass = 'bg-maroon';  }
                                    }
                                    if ($type === 'call' && stripos($ev['title'] ?? '', 'missed') !== false) {
                                        $bgClass = 'bg-danger';
                                    }

                                    if ($dateStr !== $lastDateAdv):
                                        $lastDateAdv = $dateStr;
                                ?>
                                <div class="time-label adv-tl-date-sep" data-type="<?= esc($type) ?>">
                                    <span class="bg-dark"><?= esc($dateStr) ?></span>
                                </div>
                                <?php endif; ?>

                                <div class="adv-tl-event" data-type="<?= esc($type) ?>">
                                    <i class="<?= esc($icon) ?> <?= $bgClass ?>"></i>
                                    <div class="timeline-item">
                                        <span class="time">
                                            <i class="fas fa-clock"></i> <?= $timeStr ?>
                                            <span class="badge badge-<?= $cfg['badge'] ?> ml-2" style="font-size:.65rem;">
                                                <?= esc(strtoupper($cfg['label'])) ?>
                                            </span>
                                        </span>
                                        <h3 class="timeline-header">
                                            <?= htmlspecialchars($ev['title'] ?? 'Event') ?>
                                        </h3>
                                        <?php if (!empty($ev['body'])): ?>
                                        <div class="timeline-body text-muted" style="font-size:.83rem; line-height:1.5;">
                                            <?= htmlspecialchars(mb_strimwidth($ev['body'], 0, 320, '…')) ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($subtype)): ?>
                                        <div class="timeline-footer">
                                            <span class="text-muted" style="font-size:.75rem;">
                                                <i class="fas fa-tag mr-1"></i><?= esc($subtype) ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <div><i class="fas fa-clock bg-gray"></i></div>
                            </div>

                            <?php endif; ?>
                        </div><!-- /#pane-advanced -->

                    </div><!-- /.tab-content -->
                </div><!-- /.card-body -->
            </div><!-- /.card -->

        </div>
    </section>
</div>

<script>
// ── Basic Timeline filter ────────────────────────────────────────────────────
(function () {
    const btns = document.querySelectorAll('.tl-filter-btn');
    const tl   = document.getElementById('basic-tl');
    if (!tl) return;

    btns.forEach(btn => {
        btn.addEventListener('click', function () {
            btns.forEach(b => b.classList.remove('active', 'btn-primary','btn-success','btn-danger','btn-secondary'));
            btns.forEach(b => {
                const f = b.dataset.filter;
                if (f==='all')    b.classList.add('btn-outline-secondary');
                else if (f==='sms')  b.classList.add('btn-outline-primary');
                else if (f==='call') b.classList.add('btn-outline-success');
                else                 b.classList.add('btn-outline-danger');
            });
            this.classList.remove('btn-outline-primary','btn-outline-success','btn-outline-danger','btn-outline-secondary');
            const f = this.dataset.filter;
            if (f==='all')    this.classList.add('btn-primary');
            else if (f==='sms')  this.classList.add('btn-primary');
            else if (f==='call') this.classList.add('btn-success');
            else                 this.classList.add('btn-danger');

            const filter = this.dataset.filter;
            tl.querySelectorAll('.tl-event, .tl-date-sep').forEach(el => {
                const t = el.dataset.type;
                const s = el.dataset.sub || '';
                const show = filter === 'all'
                    || t === filter
                    || s === filter;
                el.style.display = show ? '' : 'none';
            });

            // Hide orphan date separators (no visible events after them)
            const items = [...tl.querySelectorAll('.tl-date-sep, .tl-event')];
            for (let i = 0; i < items.length; i++) {
                if (!items[i].classList.contains('tl-date-sep')) continue;
                const sep = items[i];
                let hasVisible = false;
                for (let j = i + 1; j < items.length; j++) {
                    if (items[j].classList.contains('tl-date-sep')) break;
                    if (items[j].style.display !== 'none') { hasVisible = true; break; }
                }
                sep.style.display = hasVisible ? '' : 'none';
            }
        });
    });
})();

// ── Advanced Timeline filter ─────────────────────────────────────────────────
(function () {
    const btns = document.querySelectorAll('.adv-filter-btn');
    const tl   = document.getElementById('adv-tl');
    if (!tl) return;

    btns.forEach(btn => {
        btn.addEventListener('click', function () {
            btns.forEach(b => {
                b.classList.remove('active');
                // Swap outline ↔ solid reset — let CSS handle it
            });
            this.classList.add('active');

            const filter = this.dataset.filter;
            tl.querySelectorAll('.adv-tl-event, .adv-tl-date-sep').forEach(el => {
                const t = el.dataset.type;
                el.style.display = (filter === 'all' || t === filter) ? '' : 'none';
            });

            // Hide orphan date separators
            const items = [...tl.querySelectorAll('.adv-tl-date-sep, .adv-tl-event')];
            for (let i = 0; i < items.length; i++) {
                if (!items[i].classList.contains('adv-tl-date-sep')) continue;
                const sep = items[i];
                let hasVisible = false;
                for (let j = i + 1; j < items.length; j++) {
                    if (items[j].classList.contains('adv-tl-date-sep')) break;
                    if (items[j].style.display !== 'none') { hasVisible = true; break; }
                }
                sep.style.display = hasVisible ? '' : 'none';
            }
        });
    });
})();
</script>

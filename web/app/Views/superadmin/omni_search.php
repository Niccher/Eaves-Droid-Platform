<?php $pag = $pag ?? 'superadmin-omni-search'; ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-search mr-2"></i>Omni Search</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Omni Search</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-search text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1">Cross-User Forensic Search</h5>
                        <p class="mb-0 small text-muted">Search across all users' SMS, calls, contacts, locations, accounts, files, and apps in one screen. Enter a phone number, name, email, address, package name, or any keyword.</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Search</h3>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('superadmin/omni-search') ?>" class="form-inline">
                        <div class="input-group input-group-lg flex-grow-1 mr-2" style="max-width: 600px;">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
                            <input type="text" name="q" class="form-control form-control-lg" placeholder="Search phone, name, email, address, package, message…" value="<?= htmlspecialchars($query ?? '') ?>">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-search mr-1"></i>Search</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($query !== null && $query !== ''): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-search mr-1"></i> Results</h3>
                    <div class="card-tools">
                        <span class="badge badge-primary badge-pill"><?= number_format($total) ?> match<?= $total !== 1 ? 'es' : '' ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-pills flex-column flex-md-row px-3 pt-3 pb-0" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="pill-results-tab" data-toggle="pill" href="#pill-results" role="tab">
                                <i class="fas fa-chart-pie mr-1"></i>Summary
                            </a>
                        </li>
                        <?php foreach ($catMeta as $key => $meta): ?>
                        <?php if ($categoryCounts[$key] === 0) continue; ?>
                        <li class="nav-item">
                            <a class="nav-link" id="pill-<?= $key ?>-tab" data-toggle="pill" href="#pill-<?= $key ?>" role="tab">
                                <i class="fas <?= $meta['icon'] ?> mr-1"></i><?= $meta['label'] ?>
                                <span class="badge badge-pill bg-<?= $meta['color'] ?> ml-1"><?= number_format($categoryCounts[$key]) ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="tab-content p-3">
                        <div class="tab-pane fade show active" id="pill-results" role="tabpanel">
                            <?php if ($total === 0): ?>
                            <div class="text-center py-5">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;">
                                    <i class="fas fa-search fa-2x text-muted"></i>
                                </div>
                                <h4 class="text-muted">No results found</h4>
                                <p class="text-muted">No matches for "<strong><?= htmlspecialchars($query) ?></strong>" across any data category.</p>
                            </div>
                            <?php else: ?>
                            <div class="row">
                                <?php foreach ($catMeta as $key => $meta): ?>
                                <?php $count = $categoryCounts[$key] ?? 0; ?>
                                <div class="col-lg-2 col-md-4 col-6 mb-3">
                                    <div class="card card-outline shadow-sm mb-0">
                                        <div class="card-body py-2 px-3">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-<?= $meta['color'] ?> rounded-circle d-flex align-items-center justify-content-center text-white mr-3" style="width:42px;height:42px;font-size:16px;">
                                                    <i class="fas <?= $meta['icon'] ?>"></i>
                                                </div>
                                                <div>
                                                    <div class="text-muted small" style="font-size:11px;"><?= $meta['label'] ?></div>
                                                    <div class="font-weight-bold" style="font-size:22px;line-height:1.1;"><?= number_format($count) ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (($truncated ?? false)): ?>
                            <div class="alert alert-warning py-2 px-3 mb-0">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Showing first 200 results. Click a category above to see all entries.
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <?php foreach ($categoryResults as $key => $rows): ?>
                        <?php if (empty($rows)) continue; ?>
                        <?php $meta = $catMeta[$key] ?? ['label' => ucfirst($key), 'icon' => 'fa-circle', 'color' => 'secondary']; ?>
                        <div class="tab-pane fade" id="pill-<?= $key ?>" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead>
                                        <tr class="bg-dark text-white">
                                            <th style="width:4%;">#</th>
                                            <th style="width:10%;">User</th>
                                            <th style="width:8%;">Type</th>
                                            <th style="width:38%;">Preview</th>
                                            <th style="width:25%;">Detail</th>
                                            <th style="width:15%;">Timestamp</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rows as $i => $r): ?>
                                        <tr>
                                            <td><small class="text-muted"><?= $i + 1 ?></small></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center font-weight-bold" style="width:32px;height:32px;font-size:13px;"><?= strtoupper(mb_substr($r['username'], 0, 1)) ?></div>
                                                    <div class="ml-2">
                                                        <strong class="d-block" style="font-size:13px;"><?= htmlspecialchars($r['username']) ?></strong>
                                                        <small class="text-muted">ID: <?= $r['user_id'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge badge-pill" style="background:#1a56db;color:#fff;"><i class="fas <?= $r['icon'] ?> mr-1"></i><?= $r['type'] ?></span></td>
                                            <td><small class="text-break"><?= htmlspecialchars($r['preview'] ?? '') ?></small></td>
                                            <td><small class="text-muted text-break"><?= htmlspecialchars($r['detail'] ?? '') ?></small></td>
                                            <td><small class="text-muted"><?= !empty($r['timestamp']) ? date('M j, Y H:i', strtotime($r['timestamp'])) : '—' ?></small></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card-footer bg-white border-top text-muted small py-2">
                    <i class="fas fa-info-circle mr-1"></i>Searching across SMS, call logs, contacts, locations, accounts, files, and apps. Results sorted by most recent first. Limited to 200 records per search.
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
</div>
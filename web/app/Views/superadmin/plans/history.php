<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-history mr-2 text-primary"></i>Version History</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('superadmin/plans') ?>">Plans & Pricing</a></li>
                        <li class="breadcrumb-item active">History</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-tags text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1"><?= esc($plan['name']) ?> <small class="text-muted">(<?= esc($plan['slug']) ?>)</small></h5>
                        <p class="mb-0 small text-muted">Full audit trail of all pricing and feature changes for this plan, with per-version diffs.</p>
                    </div>
                </div>
            </div>

            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-clock mr-2"></i>All Versions</h3>
                    <div class="card-tools">
                        <a href="<?= base_url('superadmin/plans') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Plans
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Version</th>
                                    <th>Monthly</th>
                                    <th>Yearly</th>
                                    <th>Devices</th>
                                    <th>History</th>
                                    <th>Effective</th>
                                    <th>Changed By</th>
                                    <th>Reason</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $i => $v): ?>
                                    <?php $diff = $diffs[$v['id']] ?? []; ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-primary">v<?= $v['version'] ?></span>
                                            <?php if ($i === 0): ?>
                                                <span class="badge badge-success ml-1">Latest</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-success font-weight-bold">$<?= number_format($v['price_monthly_cents'] / 100, 2) ?></td>
                                        <td class="text-primary font-weight-bold">$<?= number_format($v['price_yearly_cents'] / 100, 2) ?></td>
                                        <td><span class="badge badge-info"><?= $v['max_devices'] ?></span></td>
                                        <td><span class="badge badge-warning"><?= $v['history_days'] ?> days</span></td>
                                        <td>
                                            <?= date('Y-m-d', strtotime($v['effective_from'])) ?>
                                            <i class="fas fa-arrow-right mx-1 text-muted small"></i>
                                            <?php if ($v['effective_until']): ?>
                                                <?= date('Y-m-d', strtotime($v['effective_until'])) ?>
                                            <?php else: ?>
                                                <span class="badge badge-success">Current</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($v['changed_by']): ?>
                                                <i class="fas fa-user text-muted mr-1"></i>User #<?= $v['changed_by'] ?>
                                            <?php else: ?>
                                                <i class="fas fa-cog text-muted mr-1"></i>System
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($v['change_reason'] ?? '—') ?></td>
                                        <td>
                                            <?php if (!empty($diff)): ?>
                                                <button type="button" class="btn btn-outline-info btn-sm" data-toggle="collapse" data-target="#diff-<?= $v['id'] ?>">
                                                    <i class="fas fa-code-branch mr-1"></i> Diff
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr class="collapse" id="diff-<?= $v['id'] ?>">
                                        <td colspan="9" class="p-0">
                                            <div class="bg-light p-3">
                                                <h6 class="font-weight-bold mb-2 text-muted">
                                                    <i class="fas fa-exchange-alt mr-1"></i>Changes vs v<?= $v['version'] - 1 ?>
                                                </h6>
                                                <table class="table table-sm table-bordered bg-white mb-0">
                                                    <tbody>
                                                        <?php foreach ($diff as $d): ?>
                                                            <tr>
                                                                <td class="font-weight-bold" style="width:30%"><?= esc($d['label']) ?></td>
                                                                <td class="text-danger" style="width:35%"><s><?= esc($d['from']) ?></s></td>
                                                                <td class="text-success" style="width:35%"><?= esc($d['to']) ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($history)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 text-secondary"></i>
                                            <p class="mb-0">No versions recorded for this plan yet.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-credit-card mr-2 text-primary"></i>Payment History</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('superadmin/subscriptions') ?>">Subscribers</a></li>
                        <li class="breadcrumb-item active">Payments</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner"><h3>$<?= number_format($stats['total_revenue_cents'] / 100, 2) ?></h3><p>Total Revenue</p></div>
                        <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner"><h3>$<?= number_format($stats['month_revenue_cents'] / 100, 2) ?></h3><p>This Month</p></div>
                        <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner"><h3><?= $stats['succeeded'] ?></h3><p>Succeeded</p></div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner"><h3><?= $stats['pending'] + $stats['failed'] ?></h3><p>Pending / Failed</p></div>
                        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-primary shadow-sm mb-3">
                <div class="card-header p-2 bg-light">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a href="<?= base_url('superadmin/subscriptions') ?>" class="nav-link">
                                <i class="fas fa-users mr-1 text-primary"></i> Subscribers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('superadmin/payments') ?>" class="nav-link active">
                                <i class="fas fa-credit-card mr-1 text-success"></i> Payment History
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('superadmin/plans') ?>" class="nav-link">
                                <i class="fas fa-tags mr-1 text-warning"></i> Plans &amp; Pricing
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list mr-2"></i>All Payments</h3>
                    <div class="card-tools">
                        <div class="btn-group btn-group-sm">
                            <a href="<?= base_url('superadmin/payments') ?>" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?>">All</a>
                            <a href="<?= base_url('superadmin/payments?status=succeeded') ?>" class="btn <?= $status === 'succeeded' ? 'btn-primary' : 'btn-outline-secondary' ?>">Succeeded</a>
                            <a href="<?= base_url('superadmin/payments?status=pending') ?>" class="btn <?= $status === 'pending' ? 'btn-primary' : 'btn-outline-secondary' ?>">Pending</a>
                            <a href="<?= base_url('superadmin/payments?status=failed') ?>" class="btn <?= $status === 'failed' ? 'btn-primary' : 'btn-outline-secondary' ?>">Failed</a>
                            <a href="<?= base_url('superadmin/payments?status=refunded') ?>" class="btn <?= $status === 'refunded' ? 'btn-primary' : 'btn-outline-secondary' ?>">Refunded</a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0" id="paymentsTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>User</th>
                                    <th>Date</th>
                                    <th>Plan</th>
                                    <th>Billing</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Provider</th>
                                    <th>Method</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $p): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($p['username']) ?></strong>
                                            <br><small class="text-muted"><?= esc($p['user_email'] ?? '—') ?></small>
                                        </td>
                                        <td><?= $p['paid_at'] ? date('M j, Y', strtotime($p['paid_at'])) : date('M j, Y', strtotime($p['created_at'])) ?></td>
                                        <td><span class="badge <?= $p['plan'] === 'free' ? 'badge-secondary' : ($p['plan'] === 'gold' ? 'badge-warning' : 'badge-danger') ?>"><?= ucfirst($p['plan']) ?></span></td>
                                        <td><?= ucfirst($p['billing_cycle']) ?></td>
                                        <td class="font-weight-bold"><?= strtoupper($p['currency']) ?> $<?= number_format($p['amount_cents'] / 100, 2) ?></td>
                                        <td>
                                            <?php if ($p['status'] === 'succeeded'): ?>
                                                <span class="badge badge-success">Succeeded</span>
                                            <?php elseif ($p['status'] === 'pending'): ?>
                                                <span class="badge badge-warning">Pending</span>
                                            <?php elseif ($p['status'] === 'failed'): ?>
                                                <span class="badge badge-danger">Failed</span>
                                            <?php elseif ($p['status'] === 'refunded'): ?>
                                                <span class="badge badge-secondary">Refunded</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $p['payment_provider'] ? ucfirst($p['payment_provider']) : '—' ?></td>
                                        <td><?= esc($p['payment_method'] ?? '—') ?></td>
                                        <td>
                                            <a href="<?= base_url("superadmin/subscriptions/{$p['user_id']}") ?>" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-user mr-1"></i> View User
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($payments)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 text-secondary"></i>
                                            <p class="mb-0">No payments found.</p>
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

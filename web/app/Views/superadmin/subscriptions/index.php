<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-users mr-2 text-primary"></i>Subscribers</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('superadmin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Subscribers</li>
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
                        <div class="inner">
                            <h3><?= $stats['paid_active'] ?></h3>
                            <p>Paid Active Plans</p>
                        </div>
                        <div class="icon"><i class="fas fa-crown"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $stats['active_subs'] ?></h3>
                            <p>Active Subscriptions</p>
                        </div>
                        <div class="icon"><i class="fas fa-user-check"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3><?= $stats['free_users'] ?></h3>
                            <p>Free Users</p>
                        </div>
                        <div class="icon"><i class="fas fa-user-slash"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= $stats['canceled'] ?></h3>
                            <p>Canceled</p>
                        </div>
                        <div class="icon"><i class="fas fa-ban"></i></div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-primary shadow-sm mb-3">
                <div class="card-header p-2 bg-light">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a href="<?= base_url('superadmin/subscriptions') ?>" class="nav-link active">
                                <i class="fas fa-users mr-1 text-primary"></i> Subscribers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('superadmin/payments') ?>" class="nav-link">
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
                    <h3 class="card-title"><i class="fas fa-list mr-2"></i>All Users &amp; Subscriptions</h3>
                    <div class="card-tools">
                        <div class="btn-group btn-group-sm">
                            <a href="<?= base_url('superadmin/subscriptions') ?>" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?>">All</a>
                            <a href="<?= base_url('superadmin/subscriptions?status=active') ?>" class="btn <?= $status === 'active' ? 'btn-primary' : 'btn-outline-secondary' ?>">Active</a>
                            <a href="<?= base_url('superadmin/subscriptions?status=canceled') ?>" class="btn <?= $status === 'canceled' ? 'btn-primary' : 'btn-outline-secondary' ?>">Canceled</a>
                            <a href="<?= base_url('superadmin/subscriptions?status=past_due') ?>" class="btn <?= $status === 'past_due' ? 'btn-primary' : 'btn-outline-secondary' ?>">Past Due</a>
                            <a href="<?= base_url('superadmin/subscriptions?status=trialing') ?>" class="btn <?= $status === 'trialing' ? 'btn-primary' : 'btn-outline-secondary' ?>">Trialing</a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0" id="subsTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>User</th>
                                    <th>Plan</th>
                                    <th>Status</th>
                                    <th>Billing</th>
                                    <th>Period</th>
                                    <th>Provider</th>
                                    <th>Payments</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subscribers as $s): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="mr-2">
                                                    <div class="avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center">
                                                        <?= strtoupper(substr($s['username'], 0, 1)) ?>
                                                    </div>
                                                </div>
                                                <div>
                                                    <strong><?= esc($s['username']) ?></strong>
                                                    <br><small class="text-muted font-italic"><?= esc($s['user_email'] ?? '—') ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($s['plan']): ?>
                                                <span class="badge <?= $s['plan'] === 'free' ? 'badge-secondary' : ($s['plan'] === 'gold' ? 'badge-warning' : 'badge-danger') ?>">
                                                    <i class="fas fa-<?= $s['plan'] === 'free' ? 'star' : ($s['plan'] === 'gold' ? 'crown' : 'gem') ?> mr-1"></i><?= ucfirst($s['plan']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-light">No plan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php $subStatus = $s['sub_status']; ?>
                                            <?php if (!$subStatus): ?>
                                                <span class="badge badge-light">—</span>
                                            <?php elseif ($subStatus === 'active'): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php elseif ($subStatus === 'trialing'): ?>
                                                <span class="badge badge-info">Trialing</span>
                                            <?php elseif ($subStatus === 'past_due'): ?>
                                                <span class="badge badge-warning">Past Due</span>
                                            <?php elseif ($subStatus === 'canceled'): ?>
                                                <span class="badge badge-danger">Canceled</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $s['billing_cycle'] ? ucfirst($s['billing_cycle']) : '—' ?></td>
                                        <td>
                                            <?php if ($s['current_period_start']): ?>
                                                <small><?= date('M j', strtotime($s['current_period_start'])) ?> → <?= date('M j, Y', strtotime($s['current_period_end'])) ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">—</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($s['payment_provider']): ?>
                                                <span class="badge badge-light"><?= ucfirst($s['payment_provider']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge badge-info"><?= (int)$s['payment_count'] ?></span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="<?= base_url("superadmin/subscriptions/{$s['id']}") ?>" class="btn btn-outline-primary btn-sm mr-1">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-outline-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                                        <i class="fas fa-crown"></i> Set Plan
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <form method="post" action="<?= base_url("superadmin/subscriptions/plan/{$s['id']}") ?>" class="dropdown-item p-0 m-0">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="days" value="365">
                                                            <input type="hidden" name="billing" value="yearly">
                                                            <input type="hidden" name="status" value="active">
                                                            <button type="submit" name="plan" value="free" class="dropdown-item"><i class="fas fa-star mr-1 text-secondary"></i>Free</button>
                                                            <button type="submit" name="plan" value="gold" class="dropdown-item"><i class="fas fa-crown mr-1 text-warning"></i>Gold</button>
                                                            <button type="submit" name="plan" value="platinum" class="dropdown-item"><i class="fas fa-gem mr-1 text-danger"></i>Platinum</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($subscribers)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 text-secondary"></i>
                                            <p class="mb-0">No subscribers found.</p>
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

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 0.85rem;
}
</style>

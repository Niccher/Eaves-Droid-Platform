<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-user-circle mr-2 text-primary"></i><?= esc($user['username']) ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('superadmin/subscriptions') ?>">Subscribers</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center mb-3">
                                <div class="avatar-xl rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center">
                                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                </div>
                            </div>
                            <h3 class="profile-username text-center"><?= esc($user['username']) ?></h3>
                            <p class="text-muted text-center font-italic"><?= esc($user['user_email'] ?? 'No email') ?></p>
                            <ul class="list-group list-group-flush mt-3">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>User ID</span>
                                    <span class="badge badge-light">#<?= $user['id'] ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Subscription Type</span>
                                    <?php if ($subscription && $subscription['plan']): ?>
                                        <span class="badge <?= $subscription['plan'] === 'free' ? 'badge-secondary' : ($subscription['plan'] === 'gold' ? 'badge-warning' : 'badge-danger') ?>">
                                            <i class="fas fa-<?= $subscription['plan'] === 'free' ? 'star' : ($subscription['plan'] === 'gold' ? 'crown' : 'gem') ?> mr-1"></i><?= ucfirst($subscription['plan']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-light">No plan</span>
                                    <?php endif; ?>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Last Paid</span>
                                    <?php $lastPaid = $payments[0]['paid_at'] ?? null; ?>
                                    <strong><?= $lastPaid ? date('M j, Y', strtotime($lastPaid)) : '—' ?></strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Account Status</span>
                                    <?php if (($user['account_status'] ?? null) === 'banned' || !$user['active']): ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-tags mr-2"></i>Current Subscription</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($subscription): ?>
                                <div class="text-center mb-3">
                                    <span class="badge badge-lg <?= $subscription['plan'] === 'free' ? 'badge-secondary' : ($subscription['plan'] === 'gold' ? 'badge-warning' : 'badge-danger') ?>">
                                        <i class="fas fa-<?= $subscription['plan'] === 'free' ? 'star' : ($subscription['plan'] === 'gold' ? 'crown' : 'gem') ?> mr-1"></i><?= ucfirst($subscription['plan']) ?>
                                    </span>
                                    <?php if ($subscription['status'] === 'active'): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php elseif ($subscription['status'] === 'trialing'): ?>
                                        <span class="badge badge-info">Trialing</span>
                                    <?php elseif ($subscription['status'] === 'past_due'): ?>
                                        <span class="badge badge-warning">Past Due</span>
                                    <?php elseif ($subscription['status'] === 'canceled'): ?>
                                        <span class="badge badge-danger">Canceled</span>
                                    <?php endif; ?>
                                </div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span class="text-muted">Billing Cycle</span>
                                        <strong><?= $subscription['billing_cycle'] ? ucfirst($subscription['billing_cycle']) : '—' ?></strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span class="text-muted">Period Start</span>
                                        <strong><?= $subscription['current_period_start'] ? date('M j, Y', strtotime($subscription['current_period_start'])) : '—' ?></strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span class="text-muted">Period End</span>
                                        <strong><?= $subscription['current_period_end'] ? date('M j, Y', strtotime($subscription['current_period_end'])) : '—' ?></strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span class="text-muted">Provider</span>
                                        <strong><?= $subscription['payment_provider'] ? ucfirst($subscription['payment_provider']) : '—' ?></strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span class="text-muted">Payment Method</span>
                                        <strong><?= esc($subscription['payment_method'] ?? '—') ?></strong>
                                    </li>
                                    <?php if ($subscription['trial_ends_at']): ?>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="text-muted">Trial Ends</span>
                                            <strong><?= date('M j, Y', strtotime($subscription['trial_ends_at'])) ?></strong>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($subscription['canceled_at']): ?>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="text-muted">Canceled At</span>
                                            <strong class="text-danger"><?= date('M j, Y', strtotime($subscription['canceled_at'])) ?></strong>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-exclamation-circle fa-3x mb-3 text-warning"></i>
                                    <p class="mb-0">This user has no subscription record.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card card-primary card-outline">
                        <div class="card-header bg-primary">
                            <h3 class="card-title text-white"><i class="fas fa-crown mr-2"></i>Manage Plan (Manual)</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">Grant or change this user's plan manually. No payment is processed — used in place of a billing gateway.</p>
                            <form method="post" action="<?= base_url("superadmin/subscriptions/plan/{$user['id']}") ?>">
                                <?= csrf_field() ?>
                                <div class="form-group">
                                    <label class="font-weight-bold">Plan</label>
                                    <div class="row">
                                        <div class="col-4">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="plan_free" name="plan" value="free" class="custom-control-input" <?= (!isset($subscription['plan']) || $subscription['plan'] === 'free') ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="plan_free"><i class="fas fa-star mr-1 text-secondary"></i>Free</label>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="plan_gold" name="plan" value="gold" class="custom-control-input" <?= ($subscription['plan'] ?? '') === 'gold' ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="plan_gold"><i class="fas fa-crown mr-1 text-warning"></i>Gold</label>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="plan_platinum" name="plan" value="platinum" class="custom-control-input" <?= ($subscription['plan'] ?? '') === 'platinum' ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="plan_platinum"><i class="fas fa-gem mr-1 text-danger"></i>Platinum</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="billing" class="font-weight-bold">Billing Cycle</label>
                                            <select name="billing" id="billing" class="form-control">
                                                <option value="yearly" <?= ($subscription['billing_cycle'] ?? 'yearly') === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                                                <option value="monthly" <?= ($subscription['billing_cycle'] ?? '') === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="days" class="font-weight-bold">Period Length (Days)</label>
                                            <input type="number" name="days" id="days" class="form-control" value="365" min="1" max="3650">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="status" class="font-weight-bold">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="active" <?= ($subscription['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="trialing" <?= ($subscription['status'] ?? '') === 'trialing' ? 'selected' : '' ?>>Trialing</option>
                                        <option value="past_due" <?= ($subscription['status'] ?? '') === 'past_due' ? 'selected' : '' ?>>Past Due</option>
                                        <option value="canceled" <?= ($subscription['status'] ?? '') === 'canceled' ? 'selected' : '' ?>>Canceled</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-1"></i> Apply Plan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-4 col-6">
                            <div class="small-box bg-success">
                                <div class="inner"><h3>$<?= number_format($stats['total_revenue_cents'] / 100, 2) ?></h3><p>Total Revenue</p></div>
                                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="small-box bg-info">
                                <div class="inner"><h3><?= count($payments) ?></h3><p>Total Payments</p></div>
                                <div class="icon"><i class="fas fa-receipt"></i></div>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner"><h3>$<?= number_format($stats['month_revenue_cents'] / 100, 2) ?></h3><p>This Month</p></div>
                                <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-receipt mr-2"></i>Payment History</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Date</th>
                                            <th>Plan</th>
                                            <th>Billing</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Provider</th>
                                            <th>Method</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payments as $p): ?>
                                            <tr>
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
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($payments)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    <i class="fas fa-inbox fa-3x mb-3 text-secondary"></i>
                                                    <p class="mb-0">No payments recorded for this user.</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.avatar-xl {
    width: 64px;
    height: 64px;
    font-size: 1.5rem;
}
.badge-lg {
    font-size: 1rem;
    padding: 0.5rem 0.75rem;
}
</style>

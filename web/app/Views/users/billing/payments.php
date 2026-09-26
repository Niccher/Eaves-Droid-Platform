<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-file-invoice-dollar mr-2 text-success"></i>Payment History</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('billing') ?>">Billing</a></li>
                        <li class="breadcrumb-item active">Payments</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <?php
            // Compute summary stats
            $totalPaidCents = 0;
            $successCount   = 0;
            $currency       = 'KES';
            foreach ($payments as $p) {
                if ($p['status'] === 'succeeded') {
                    $totalPaidCents += $p['amount_cents'];
                    $successCount++;
                    $currency = strtoupper($p['currency']);
                }
            }
            $totalPaid = $currency . ' ' . number_format($totalPaidCents / 100, 2);
            ?>

            <?php if (!empty($payments)): ?>
            <!-- Summary Stats Bar -->
            <div class="row mb-3">
                <div class="col-sm-4">
                    <div class="info-box shadow-sm mb-2">
                        <span class="info-box-icon bg-success"><i class="fas fa-receipt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Paid</span>
                            <span class="info-box-number" style="font-family:monospace;"><?= $totalPaid ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="info-box shadow-sm mb-2">
                        <span class="info-box-icon bg-primary"><i class="fas fa-list-ol"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Successful Transactions</span>
                            <span class="info-box-number"><?= $successCount ?> of <?= count($payments) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="info-box shadow-sm mb-2">
                        <span class="info-box-icon bg-warning"><i class="fas fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Latest Payment</span>
                            <span class="info-box-number" style="font-size:14px;">
                                <?= !empty($payments) ? date('M d, Y', strtotime($payments[0]['paid_at'] ?? $payments[0]['created_at'])) : '—' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="card card-outline card-success shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history mr-1"></i> My Transactions</h3>
                    <div class="card-tools">
                        <a href="<?= base_url('billing') ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Plans
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($payments)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No payments found</h5>
                            <p class="text-muted">You haven't made any subscription upgrades yet.</p>
                            <a href="<?= base_url('billing') ?>" class="btn btn-success mt-2">
                                <i class="fas fa-crown mr-1"></i> Upgrade Now
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="paymentsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="min-width:130px;">Date</th>
                                        <th>Reference</th>
                                        <th>Plan</th>
                                        <th>Cycle</th>
                                        <th style="min-width:110px;">Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th class="text-center" style="min-width:120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <?php
                                        // Status config
                                        $statusMap = [
                                            'succeeded' => ['class' => 'badge-success',   'icon' => 'fa-check-circle',  'label' => 'Paid'],
                                            'pending'   => ['class' => 'badge-warning',   'icon' => 'fa-clock',         'label' => 'Pending'],
                                            'failed'    => ['class' => 'badge-danger',    'icon' => 'fa-times-circle',  'label' => 'Failed'],
                                            'refunded'  => ['class' => 'badge-info',      'icon' => 'fa-undo-alt',      'label' => 'Refunded'],
                                        ];
                                        $status    = $payment['status'] ?? 'pending';
                                        $statusCfg = $statusMap[$status] ?? ['class' => 'badge-secondary', 'icon' => 'fa-question-circle', 'label' => ucfirst($status)];

                                        // Payment method config
                                        $methodRaw = strtolower($payment['payment_method'] ?? 'card');
                                        $methodMap = [
                                            'mpesa'   => ['icon' => 'fa-mobile-alt',  'label' => 'M-Pesa',  'color' => '#4CAF50'],
                                            'm-pesa'  => ['icon' => 'fa-mobile-alt',  'label' => 'M-Pesa',  'color' => '#4CAF50'],
                                            'airtel'  => ['icon' => 'fa-sim-card',    'label' => 'Airtel',  'color' => '#E53935'],
                                            'bank'    => ['icon' => 'fa-university',  'label' => 'Bank',    'color' => '#1565C0'],
                                            'card'    => ['icon' => 'fa-credit-card', 'label' => 'Card',    'color' => '#6d28d9'],
                                        ];
                                        $methodCfg = $methodMap[$methodRaw] ?? ['icon' => 'fa-money-bill-wave', 'label' => ucfirst($methodRaw), 'color' => '#555'];

                                        $amountFormatted = strtoupper($payment['currency']) . ' ' . number_format($payment['amount_cents'] / 100, 2);
                                        $ref = esc($payment['provider_payment_id'] ?? 'N/A');
                                        $dateStr = date('M d, Y H:i', strtotime($payment['paid_at'] ?? $payment['created_at']));
                                        ?>
                                        <tr>
                                            <td style="font-size:13px;white-space:nowrap;"><?= $dateStr ?></td>
                                            <td>
                                                <code class="text-dark" style="font-size:12px;" id="ref-<?= esc($payment['id']) ?>"><?= $ref ?></code>
                                                <button class="btn btn-xs btn-outline-secondary ml-1 copy-ref-btn"
                                                        data-ref="<?= $ref ?>"
                                                        title="Copy reference"
                                                        style="padding:1px 5px;font-size:11px;">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </td>
                                            <td><span class="badge badge-light text-uppercase font-weight-bold"><?= esc($payment['plan']) ?></span></td>
                                            <td class="text-capitalize" style="font-size:13px;"><?= esc($payment['billing_cycle']) ?></td>
                                            <td><strong style="font-family:monospace;font-size:13px;"><?= $amountFormatted ?></strong></td>
                                            <td>
                                                <i class="fas <?= $methodCfg['icon'] ?> mr-1" style="color:<?= $methodCfg['color'] ?>;"></i>
                                                <span style="font-size:13px;"><?= $methodCfg['label'] ?></span>
                                            </td>
                                            <td>
                                                <span class="badge <?= $statusCfg['class'] ?> py-1 px-2">
                                                    <i class="fas <?= $statusCfg['icon'] ?> mr-1"></i><?= $statusCfg['label'] ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($payment['status'] === 'succeeded'): ?>
                                                    <button class="btn btn-sm btn-outline-success print-invoice-btn"
                                                            data-id="<?= esc($payment['id']) ?>"
                                                            data-date="<?= $dateStr ?>"
                                                            data-ref="<?= $ref ?>"
                                                            data-plan="<?= esc($payment['plan']) ?>"
                                                            data-cycle="<?= esc($payment['billing_cycle']) ?>"
                                                            data-amount="<?= $amountFormatted ?>"
                                                            data-method="<?= esc($payment['payment_method'] ?? 'card') ?>"
                                                            data-provider="<?= esc($payment['payment_provider'] ?? 'Pesapal') ?>"
                                                            title="Print Receipt">
                                                        <i class="fas fa-print mr-1"></i>Receipt
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted small">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Receipt Printing Hidden Wrapper -->
<div id="receipt-print-area" class="d-none">
    <div class="receipt-container" style="max-width:600px;margin:auto;padding:30px;border:1px solid #eee;box-shadow:0 0 10px rgba(0,0,0,0.15);font-size:16px;line-height:24px;font-family:'Helvetica Neue','Helvetica',Helvetica,Arial,sans-serif;color:#555;background:#fff;">
        <table cellpadding="0" cellspacing="0" style="width:100%;line-height:inherit;text-align:left;">
            <tr class="top">
                <td colspan="2" style="padding-bottom:20px;">
                    <table style="width:100%;">
                        <tr>
                            <td style="font-size:38px;font-weight:bold;color:#1e40af;padding-bottom:20px;">Eaves Droid</td>
                            <td style="text-align:right;padding-bottom:20px;font-size:14px;color:#555;">
                                <strong>Receipt #:</strong> <span id="r-ref" style="font-family:monospace;"></span><br>
                                <strong>Date:</strong> <span id="r-date"></span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-bottom:20px;border-bottom:2px solid #e2e8f0;">
                    <table style="width:100%;">
                        <tr>
                            <td style="font-size:14px;color:#555;">
                                <strong>Eaves Droid Inc.</strong><br>
                                Nairobi, Kenya<br>
                                support@eavesdroid.com
                            </td>
                            <td style="text-align:right;font-size:14px;color:#555;">
                                <strong>Billed To:</strong><br>
                                <?= esc(ucwords($user_info['username'] ?? 'User')) ?><br>
                                <?= esc($user_info['email'] ?? '') ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr style="background:#f1f5f9;">
                <td style="padding:10px;font-weight:bold;color:#1e293b;">Description</td>
                <td style="text-align:right;padding:10px;font-weight:bold;color:#1e293b;">Amount</td>
            </tr>
            <tr style="border-bottom:1px solid #eee;">
                <td style="padding:15px 10px;" id="r-desc"></td>
                <td style="text-align:right;padding:15px 10px;font-family:monospace;" id="r-price"></td>
            </tr>
            <tr style="border-bottom:1px solid #eee;">
                <td style="padding:10px;font-size:13px;color:#888;">
                    <i>Payment Method:</i> <span id="r-method" style="text-transform:capitalize;"></span> via <span id="r-provider" style="text-transform:capitalize;"></span>
                </td>
                <td></td>
            </tr>
            <tr style="font-weight:bold;">
                <td style="padding:15px 10px;font-size:16px;color:#1e293b;">Total Paid</td>
                <td style="text-align:right;padding:15px 10px;font-size:22px;color:#16a34a;font-family:monospace;" id="r-total"></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align:center;padding-top:30px;font-size:13px;color:#94a3b8;border-top:1px dashed #e2e8f0;">
                    Thank you for your business!<br>This is an electronically generated official receipt.
                </td>
            </tr>
        </table>
    </div>
</div>

<!-- Toast notification for copy -->
<div id="copy-toast" style="position:fixed;bottom:24px;right:24px;background:#1e293b;color:#fff;padding:10px 18px;border-radius:8px;font-size:13px;z-index:9999;display:none;box-shadow:0 4px 12px rgba(0,0,0,0.2);">&#10003; Reference copied!</div>

<style>
@media print {
    body * { visibility: hidden; }
    #receipt-print-area, #receipt-print-area * { visibility: visible; }
    #receipt-print-area {
        position: absolute; left: 0; top: 0;
        width: 100%; display: block !important;
    }
}
#paymentsTable tbody tr:hover { background: #f8fafc; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Print receipt per row
    document.querySelectorAll('.print-invoice-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const d = this.dataset;
            document.getElementById('r-ref').textContent   = d.ref;
            document.getElementById('r-date').textContent  = d.date;
            document.getElementById('r-desc').textContent  =
                'Eaves Droid ' + d.plan.charAt(0).toUpperCase() + d.plan.slice(1)
                + ' Plan (' + d.cycle.charAt(0).toUpperCase() + d.cycle.slice(1) + ' Subscription)';
            document.getElementById('r-price').textContent  = d.amount;
            document.getElementById('r-method').textContent = d.method;
            document.getElementById('r-provider').textContent = d.provider;
            document.getElementById('r-total').textContent  = d.amount;
            window.print();
        });
    });

    // Copy reference to clipboard
    document.querySelectorAll('.copy-ref-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const ref = this.dataset.ref;
            navigator.clipboard.writeText(ref).then(function() {
                const toast = document.getElementById('copy-toast');
                toast.style.display = 'block';
                setTimeout(function() { toast.style.display = 'none'; }, 2000);
            }).catch(function() {
                // Fallback for older browsers
                const ta = document.createElement('textarea');
                ta.value = ref;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            });
        });
    });

});
</script>

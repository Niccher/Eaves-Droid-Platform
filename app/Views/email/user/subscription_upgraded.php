<?php
/**
 * Content template for: Subscription Upgraded
 * Used by: Billing::simulateUpgrade
 * Layout: email/_layout
 *
 * Variables:
 *   $username       - account holder's username
 *   $oldPlan        - previous plan key (free|gold|platinum)
 *   $oldPlanName    - previous plan display name
 *   $newPlan        - new plan key
 *   $newPlanName    - new plan display name
 *   $billing        - monthly|yearly
 *   $amountCents    - payable amount in cents
 *   $currency       - currency code
 *   $periodEnd      - subscription end date
 *   $newFeatures    - array of newly-unlocked feature labels (vs previous plan)
 *   $newTiers       - array of newly-unlocked algorithm tier labels (vs previous plan)
 *   $dashboardUrl   - link to the dashboard / billing page
 */
$oldName = ucfirst($oldPlanName ?? ($oldPlan ?? 'Free'));
$newName = ucfirst($newPlanName ?? ($newPlan ?? ''));
$money = fn($cents) => strtoupper($currency ?? 'USD') . ' ' . number_format(($cents ?? 0) / 100, 2);
?>

<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">🎉 Welcome to <?= esc($newName) ?>!</h2>

    <p style="color:#475569;margin:0 0 16px;font-size:14px;">Hello <strong><?= esc($username ?? 'User') ?></strong>,</p>
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">
        Your Eaves Droid subscription has been upgraded from <strong><?= esc($oldName) ?></strong> to
        <strong><?= esc($newName) ?></strong>. Your new plan is now active — here's what changed:
    </p>

    <!-- Plan summary -->
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin:20px 0;">
        <table style="width:100%;border-collapse:collapse;">
            <tr style="border-bottom:1px solid #e2e8f0;">
                <td style="padding:10px 0;color:#64748b;font-weight:600;">Previous plan</td>
                <td style="padding:10px 0;color:#0f172a;text-align:right;"><?= esc($oldName) ?></td>
            </tr>
            <tr style="border-bottom:1px solid #e2e8f0;">
                <td style="padding:10px 0;color:#64748b;font-weight:600;">New plan</td>
                <td style="padding:10px 0;text-align:right;"><span style="display:inline-block;padding:4px 12px;background:#fef3c7;color:#92400e;border-radius:20px;font-size:12px;font-weight:700;"><?= esc($newName) ?></span></td>
            </tr>
            <tr style="border-bottom:1px solid #e2e8f0;">
                <td style="padding:10px 0;color:#64748b;font-weight:600;">Billing cycle</td>
                <td style="padding:10px 0;color:#0f172a;text-align:right;text-transform:capitalize;"><?= esc($billing ?? 'yearly') ?></td>
            </tr>
            <tr style="border-bottom:1px solid #e2e8f0;">
                <td style="padding:10px 0;color:#64748b;font-weight:600;">Amount paid</td>
                <td style="padding:10px 0;color:#0f172a;text-align:right;"><?= $money($amountCents ?? 0) ?></td>
            </tr>
            <tr>
                <td style="padding:10px 0;color:#64748b;font-weight:600;">Plan valid until</td>
                <td style="padding:10px 0;color:#0f172a;text-align:right;"><?= esc($periodEnd ?? '—') ?></td>
            </tr>
        </table>
    </div>

    <?php if (!empty($newFeatures)): ?>
    <!-- Newly unlocked features -->
    <div style="padding:16px;background:#eff6ff;border-left:4px solid #3b82f6;border-radius:8px;margin:20px 0;">
        <p style="margin:0 0 10px;color:#1e40af;font-weight:600;font-size:13px;">✨ Newly unlocked features</p>
        <ul style="margin:0;padding-left:20px;color:#475569;font-size:13px;line-height:2;">
            <?php foreach ($newFeatures as $label): ?>
            <li><strong><?= esc($label) ?></strong> — now included in your <?= esc($newName) ?> plan.</li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if (!empty($newTiers)): ?>
    <!-- Newly unlocked algorithms -->
    <div style="padding:16px;background:#f0fdf4;border-left:4px solid #22c55e;border-radius:8px;margin:20px 0;">
        <p style="margin:0 0 10px;color:#166534;font-weight:600;font-size:13px;">🧠 New analysis capabilities</p>
        <ul style="margin:0;padding-left:20px;color:#475569;font-size:13px;line-height:2;">
            <?php foreach ($newTiers as $label): ?>
            <li><strong><?= esc($label) ?></strong> — new detection models are now available to you.</li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <p style="color:#94a3b8;font-size:13px;margin-top:20px;">
        You can manage your plan anytime from your
        <a href="<?= esc($dashboardUrl ?? site_url('billing')) ?>" style="color:#00d4aa;">billing page</a>.
    </p>
</div>

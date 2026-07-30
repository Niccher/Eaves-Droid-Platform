<?php
/**
 * _security_footer.php - Reusable Security & Audit Metadata Footer
 * 
 * Variables (all optional, auto-escaped):
 *   $action          - Human-readable action name (e.g., "Data Export", "Remote Command: Fetch SMS")
 *   $description     - Plain English explanation of what the action does
 *   $status          - 'success' | 'failed' | 'pending' | 'warning'
 *   $initiatedBy     - Who/what initiated (e.g., "Admin: john", "System (Cron)", "User: jane")
 *   $browser         - Full User-Agent string
 *   $browserIp       - IP address of the browser
 *   $executedAt      - Timestamp in Y-m-d H:i:s format
 */

// Only render if there's meaningful data
$hasData = !empty($action) || !empty($description) || !empty($browser) || !empty($browserIp) || !empty($initiatedBy);
if (!$hasData) return;
?>

<!-- Security & Audit Footer -->
<div style="background:#1a1a2e;border-left:4px solid #00d4aa;margin:24px 0;padding:20px;border-radius:10px;position:relative;overflow:hidden;">
    <div style="position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,#00d4aa,transparent);"></div>
    <h4 style="color:#00d4aa;margin:0 0 16px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;display:flex;align-items:center;gap:8px;">
        <span style="font-size:14px;">🔒</span> Security & Audit Information
    </h4>
    <table style="width:100%;border-collapse:collapse;font-size:12px;font-family:'SF Mono','Fira Code','Monaco',monospace;">
        <?php if (!empty($action)): ?>
        <tr>
            <td style="padding:6px 10px;color:#777;font-weight:600;width:150px;">Action</td>
            <td style="padding:6px 10px;color:#e8e8e8;font-weight:500;"><?= esc($action) ?></td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($description)): ?>
        <tr>
            <td style="padding:6px 10px;color:#777;font-weight:600;">What This Does</td>
            <td style="padding:6px 10px;color:#999;line-height:1.5;"><?= esc($description) ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td style="padding:6px 10px;color:#777;font-weight:600;">Status</td>
            <td style="padding:6px 10px;">
                <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;background:<?= $status === 'success' ? 'rgba(0,212,170,0.15)' : ($status === 'failed' ? 'rgba(255,71,87,0.15)' : ($status === 'warning' ? 'rgba(255,165,2,0.15)' : 'rgba(0,102,255,0.15)')) ?>;color:<?= $status === 'success' ? '#00d4aa' : ($status === 'failed' ? '#ff4757' : ($status === 'warning' ? '#ffa502' : '#0066ff')) ?>;">
                    <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;"></span>
                    <?= esc(ucfirst($status ?? 'success')) ?>
                </span>
            </td>
        </tr>
        <?php if (!empty($initiatedBy)): ?>
        <tr>
            <td style="padding:6px 10px;color:#777;font-weight:600;">Initiated By</td>
            <td style="padding:6px 10px;color:#e8e8e8;font-weight:500;"><?= esc($initiatedBy) ?></td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($browser)): ?>
        <tr>
            <td style="padding:6px 10px;color:#777;font-weight:600;">Browser</td>
            <td style="padding:6px 10px;color:#999;word-break:break-all;"><?= esc($browser) ?></td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($browserIp)): ?>
        <tr>
            <td style="padding:6px 10px;color:#777;font-weight:600;">Browser IP</td>
            <td style="padding:6px 10px;color:#e8e8e8;font-weight:500;font-family:monospace;"><?= esc($browserIp) ?></td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($executedAt)): ?>
        <tr>
            <td style="padding:6px 10px;color:#777;font-weight:600;">Executed At</td>
            <td style="padding:6px 10px;color:#e8e8e8;font-weight:500;font-family:monospace;"><?= esc($executedAt) ?></td>
        </tr>
        <?php endif; ?>
    </table>
</div>
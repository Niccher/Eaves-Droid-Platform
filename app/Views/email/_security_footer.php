<?php
/**
 * _security_footer.php - Reusable Security & Audit Metadata Footer
 */

$act  = $action ?? $securityAction ?? '';
$desc = $description ?? $securityDescription ?? '';
$stat = $status ?? $securityStatus ?? 'success';
$init = $initiatedBy ?? $securityInitiatedBy ?? '';
$ip   = $browserIp ?? $securityBrowserIp ?? '';
$time = $executedAt ?? $securityExecutedAt ?? '';
$ua   = $browser ?? $securityBrowser ?? '';
$trackId = $emailTrackId ?? ''; 

$hasData = !empty($act) || !empty($ua) || !empty($ip) || !empty($init);
if (!$hasData) return;
?>

<!-- Security & Audit Footer -->
<div style="background:#1e293b;border-left:4px solid #00d4aa;margin:24px 0;padding:20px;border-radius:10px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <h4 style="color:#00d4aa;margin:0 0 16px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;">
        🔒 Security &amp; Audit Information
    </h4>
    
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;font-size:12px;color:#cbd5e1;line-height:1.6;">
        <!-- Row 1: Action + Status -->
        <tr>
            <td style="padding:4px 0;width:50%;vertical-align:top;">
                <span style="color:#94a3b8;font-weight:600;">Action:</span> 
                <span style="color:#f1f5f9;font-weight:500;"><?= esc($act) ?></span>
            </td>
            <td style="padding:4px 0;width:50%;vertical-align:top;">
                <span style="color:#94a3b8;font-weight:600;">Status:</span> 
                <span style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;text-transform:uppercase;background:<?= $stat === 'success' || strtolower($stat) === 'completed' ? 'rgba(0,212,170,0.15)' : 'rgba(255,71,87,0.15)' ?>;color:<?= $stat === 'success' || strtolower($stat) === 'completed' ? '#00d4aa' : '#ff4757' ?>;">
                    <?= esc(ucfirst($stat)) ?>
                </span>
            </td>
        </tr>
        
        <!-- Row 2: Browser IP + Executed At -->
        <tr>
            <td style="padding:4px 0;width:50%;vertical-align:top;">
                <span style="color:#94a3b8;font-weight:600;">Browser IP:</span> 
                <span style="color:#f1f5f9;font-family:monospace;"><?= esc($ip) ?></span>
            </td>
            <td style="padding:4px 0;width:50%;vertical-align:top;">
                <span style="color:#94a3b8;font-weight:600;">Executed At:</span> 
                <span style="color:#f1f5f9;font-family:monospace;"><?= esc($time) ?></span>
            </td>
        </tr>
        
        <!-- Row 3: Browser User-Agent (Full width) -->
        <?php if (!empty($ua)): ?>
        <tr>
            <td colspan="2" style="padding:6px 0 4px;border-top:1px solid #334155;margin-top:4px;word-break:break-all;font-family:monospace;font-size:11px;color:#94a3b8;">
                <span style="font-weight:600;color:#64748b;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">Browser:</span> <?= esc($ua) ?>
            </td>
        </tr>
        <?php endif; ?>

        <!-- Subtle Tracking ID -->
        <?php if (!empty($trackId)): ?>
        <tr>
            <td colspan="2" style="padding:6px 0 0;text-align:right;font-size:10px;color:#475569;font-family:monospace;">
                Log ID: <?= esc($trackId) ?>
            </td>
        </tr>
        <?php endif; ?>
    </table>
</div>
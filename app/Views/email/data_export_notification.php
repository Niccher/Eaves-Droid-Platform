<?php
/**
 * Content template for: Data Export Notification
 * Used by: AdvancedController::sendExportCompleteEmail
 * Layout: email/_layout
 */
?>

<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:28px;box-shadow:0 4px 16px rgba(15,23,42,0.03);">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:22px;font-weight:700;letter-spacing:-0.5px;display:flex;align-items:center;gap:8px;">
        <span style="font-size:24px;">📦</span> Data Export Complete
    </h2>
    
    <p style="color:#475569;margin:0 0 20px;font-size:14px;line-height:1.6;">Hello <strong><?= esc($username ?? 'User') ?></strong>,</p>
    <p style="color:#475569;margin:0 0 20px;font-size:14px;line-height:1.6;">Your offline diagnostics backup has been prepared successfully as of <strong><?= esc($asAtTimestamp ?? date('Y-m-d H:i:s')) ?></strong>. Below is a comprehensive overview of the exported payload structure:</p>
    
    <?php foreach ($categories as $category => $info): ?>
        <?php 
            $catLabel = $categoryLabels[$category] ?? str_replace('_', ' ', $category); 
            $isMisc = ($category === 'misc_hardware' || $category === 'misc_software');
        ?>
        <div style="margin:24px 0;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.02);">
            <div style="background:linear-gradient(135deg,#00b894,#00d4aa);color:#ffffff;padding:12px 18px;font-weight:700;font-size:14px;display:flex;justify-content:space-between;align-items:center;">
                <span style="letter-spacing:-0.2px;">📁 <?= esc($catLabel) ?></span>
                <span style="font-weight:500;font-size:11px;opacity:0.95;background:rgba(255,255,255,0.2);padding:2px 8px;border-radius:12px;font-family:monospace;">
                    Total: <?= number_format($info['total_rows']) ?> rows | <?= esc($info['total_size_human']) ?>
                </span>
            </div>
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;border-collapse:collapse;margin:0;font-size:13px;">
                <thead>
                    <tr style="background:#f1f5f9;">
                        <th style="padding:10px 16px;border-bottom:1px solid #e2e8f0;text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:0.8px;color:#64748b;font-weight:600;">Sub-Category</th>
                        <th style="padding:10px 16px;border-bottom:1px solid #e2e8f0;text-align:right;font-size:11px;text-transform:uppercase;letter-spacing:0.8px;color:#64748b;font-weight:600;">Rows</th>
                        <th style="padding:10px 16px;border-bottom:1px solid #e2e8f0;text-align:right;font-size:11px;text-transform:uppercase;letter-spacing:0.8px;color:#64748b;font-weight:600;">Est. Size</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($info['tables'] as $tbl): ?>
                    <tr style="border-bottom:1px solid #e2e8f0;background:#ffffff;">
                        <td style="padding:10px 16px;color:#334155; <?= $isMisc ? 'padding-left: 28px;' : '' ?>">
                            <?= $isMisc ? '└─ ' : '' ?><?= esc($tbl['label']) ?>
                        </td>
                        <td style="padding:10px 16px;text-align:right;font-weight:700;color:#0f172a;font-family:monospace;"><?= number_format($tbl['count']) ?></td>
                        <td style="padding:10px 16px;text-align:right;color:#94a3b8;font-size:12px;font-family:monospace;"><?= esc($tbl['estimated_size_human']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background:#f8fafc;font-weight:700;font-size:13px;border-top:1px solid #e2e8f0;">
                        <td style="padding:12px 16px;color:#00b894;">Category Total</td>
                        <td style="padding:12px 16px;text-align:right;color:#0f172a;font-family:monospace;"><?= number_format($info['total_rows']) ?></td>
                        <td style="padding:12px 16px;text-align:right;color:#00b894;font-family:monospace;"><?= esc($info['total_size_human']) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endforeach; ?>
    
    <div style="margin-top:28px;padding:20px;background:linear-gradient(135deg,rgba(0,184,148,0.06),rgba(0,212,170,0.06));border:1px solid rgba(0,184,148,0.2);border-radius:10px;text-align:center;">
        <div style="margin-bottom:16px;">
            <strong style="color:#00b894;font-size:14px;">Grand Summary:</strong>
            <span style="color:#0f172a;font-family:monospace;font-size:14px;margin-left:8px;font-weight:700;">
                <?= number_format($totalRows) ?> rows / <?= esc($totalSizeHuman) ?>
            </span>
        </div>
        <a href="<?= site_url('account/export') ?>" style="display:inline-block;padding:12px 28px;background:linear-gradient(135deg,#00b894,#00d4aa);color:#ffffff;text-decoration:none;border-radius:8px;font-weight:700;font-size:14px;box-shadow:0 4px 12px rgba(0,184,148,0.2);">
            Download Backup Archive
        </a>
    </div>
    
    <p style="color:#94a3b8;font-size:12px;margin-top:20px;text-align:center;line-height:1.5;">
        This export is bundle-packaged as a structural JSON payload. For security reasons, download links expire after 24 hours.
    </p>
</div>
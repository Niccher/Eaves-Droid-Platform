<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">Storage <?= ucfirst($level) ?> Alert</h2>
    
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">Disk usage on <strong><?= esc($disk['mount']) ?></strong> has exceeded the <strong><?= $level ?> threshold</strong>.</p>
    
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin:20px 0;">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:22px;font-weight:700;color:<?= $level === 'critical' ? '#ef4444' : '#f59e0b' ?>;"><?= esc($disk['usage_percent']) ?>%</div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Usage</div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:22px;font-weight:700;color:#22c55e;"><?= esc($disk['used_formatted']) ?></div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Used</div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:22px;font-weight:700;color:#0f172a;"><?= esc($disk['total_formatted']) ?></div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Total</div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:22px;font-weight:700;color:#22c55e;"><?= esc($disk['free_formatted']) ?></div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Free</div>
        </div>
    </div>
    
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin:20px 0;">
        <div style="height:28px;background:#e2e8f0;border-radius:14px;overflow:hidden;">
            <div style="width: <?= esc($disk['usage_percent']) ?>%;height:100%;background:<?= $level === 'critical' ? '#ef4444' : '#f59e0b' ?>;border-radius:14px;display:flex;align-items:center;justify-content:center;">
                <span style="color:#fff;font-weight:bold;font-size:13px;"><?= esc($disk['usage_percent']) ?>%</span>
            </div>
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:8px;font-size:11px;color:#94a3b8;">
            <span>Warning: <?= esc($threshold_warning ?? 80) ?>%</span>
            <span>Critical: <?= esc($threshold_critical ?? 90) ?>%</span>
        </div>
    </div>
    
    <div style="padding:14px 16px;background:<?= $level === 'critical' ? '#fef2f2' : '#fffbeb' ?>;border-left:4px solid <?= $level === 'critical' ? '#ef4444' : '#f59e0b' ?>;border-radius:8px;margin:20px 0;font-size:13px;color:<?= $level === 'critical' ? '#991b1b' : '#92400e' ?>;">
        <strong><?= $level === 'critical' ? '🚨 Critical' : '⚠️ Warning' ?></strong> Disk usage requires immediate attention. Consider cleaning logs, cache, or old backups.
    </div>
</div>
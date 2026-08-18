<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:20px;">
    <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
        <div style="background:#28a745;padding:20px;text-align:center;">
            <h1 style="color:#fff;margin:0;font-size:22px;">✅ SMTP Test Successful</h1>
        </div>
        <div style="padding:25px;">
            <p style="color:#333;font-size:15px;line-height:1.6;">Hello,</p>
            <p style="color:#333;font-size:15px;line-height:1.6;">This is a test email from <strong>Eaves Droid</strong>. If you received this message, your SMTP configuration is working correctly.</p>
            <div style="background:#e8fce8;border-left:4px solid #28a745;padding:12px 15px;margin:15px 0;border-radius:4px;">
                <p style="margin:0;color:#333;font-size:13px;line-height:1.5;">
                    <strong>Server:</strong> <?= htmlspecialchars($smtpHost) ?>:<?= htmlspecialchars($smtpPort) ?><br>
                    <strong>User:</strong> <?= htmlspecialchars($smtpUser) ?><br>
                    <strong>From:</strong> <?= htmlspecialchars($smtpFromEmail) ?> (<?= htmlspecialchars($smtpFromName) ?>)<br>
                    <strong>Sent at:</strong> <?= date('F j, Y, g:i A') ?>
                </p>
            </div>
            <p style="color:#333;font-size:15px;line-height:1.6;">Your platform is now ready to send emails to users.</p>
            <p style="color:#333;font-size:15px;line-height:1.6;">Thank you,<br><strong>Eaves Droid Team</strong></p>
        </div>
        <div style="background:#f1f1f1;padding:12px;text-align:center;font-size:11px;color:#888;">
            Eaves Droid — AdvancedController Mobile Forensic & Data Intelligence Platform
        </div>
    </div>
</body>
</html>
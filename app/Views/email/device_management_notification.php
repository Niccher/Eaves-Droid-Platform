<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Remote Command Executed - Eaves Droid</title>
</head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid <?= $success ? '#28a745' : '#dc3545' ?>;">
        <h2 style="margin-top: 0; color: <?= $success ? '#28a745' : '#dc3545' ?>;">
            <?= $success ? 'Command Executed Successfully' : 'Command Execution Failed' ?>
        </h2>
        <p>Dear <?= esc($targetUsername ?? 'User') ?>,</p>
        <p>A remote command has been executed on your device:</p>

        <div style="background-color: #fff; padding: 15px; border-radius: 6px; border: 1px solid #ddd; margin: 15px 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #666; font-weight: bold;">Action</td>
                    <td style="padding: 8px 0; text-align: right;"><strong style="color: <?= $success ? '#28a745' : '#dc3545' ?>;"><?= esc($label) ?></strong></td>
                </tr>
                <?php if (!empty($description)): ?>
                <tr>
                    <td style="padding: 8px 0; color: #666; font-weight: bold;">What This Does</td>
                    <td style="padding: 8px 0; text-align: right; font-size: 13px; color: #555;"><?= esc($description) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td style="padding: 8px 0; color: #666; font-weight: bold;">Status</td>
                    <td style="padding: 8px 0; text-align: right; font-weight: bold; color: <?= $success ? '#28a745' : '#dc3545' ?>;"><?= $success ? 'SUCCESS' : 'FAILED' ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #666; font-weight: bold;">Initiated By</td>
                    <td style="padding: 8px 0; text-align: right;"><?= esc($adminName) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #666; font-weight: bold;">Browser</td>
                    <td style="padding: 8px 0; text-align: right; font-size: 12px; word-break: break-all;"><?= esc($userAgent) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #666; font-weight: bold;">Browser IP</td>
                    <td style="padding: 8px 0; text-align: right; font-family: monospace; font-size: 13px;"><?= esc($ip) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #666; font-weight: bold;">Executed At</td>
                    <td style="padding: 8px 0; text-align: right; font-family: monospace;"><?= esc($timestamp) ?></td>
                </tr>
            </table>
        </div>

        <p style="color: #666; font-size: 14px;">If you did not authorize this action, please contact support immediately.</p>

        <p>Thank you,<br>The Eaves Droid Team</p>
    </div>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-container {
            background: #f9f9f9;
            border-radius: 5px;
            padding: 30px;
            border: 1px solid #ddd;
        }
        .header {
            background: #4e73df;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
            margin: -30px -30px 20px -30px;
        }
        .button {
            display: inline-block;
            background: #4e73df;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <h2>Password Reset Request</h2>
    </div>

    <p>Hello <?= esc($username) ?>,</p>

    <p>You have requested to reset your password for your Prj Imgs account. Click the button below to reset your password:</p>

    <p style="text-align: center;">
        <a href="<?= esc($reset_link) ?>" class="button">Reset Password</a>
    </p>

    <p>If the button doesn't work, copy and paste this link into your browser:</p>

    <p style="background: #f0f0f0; padding: 10px; border-radius: 3px; word-break: break-all;">
        <?= esc($reset_link) ?>
    </p>

    <p>This password reset link will expire in 1 hour for security reasons.</p>

    <p>If you didn't request this password reset, please ignore this email. Your password will remain unchanged.</p>

    <div class="footer">
        <p>This email was sent by Prj Imgs.<br>
            Please do not reply to this email.</p>
    </div>
</div>
</body>
</html>
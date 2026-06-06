<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Source Sans Pro', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f6f9;
        }
        .email-container {
            background: #ffffff;
            border-radius: 8px;
            padding: 0;
            border: 1px solid #e1e4e8;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .header {
            background: #007bff;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: white;
            padding: 5px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .content {
            padding: 30px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background: #007bff;
            color: white !important;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,123,255,0.2);
        }
        .link-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #007bff;
            word-break: break-all;
            font-size: 13px;
            color: #666;
            margin-top: 20px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            border-top: 1px solid #e1e4e8;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
        h2 { margin: 0; font-weight: 300; letter-spacing: 1px; }
    </style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <img src="<?= base_url('assets/img/logo.png') ?>" alt="Prj Images Logo" class="logo">
        <h2>Password Reset</h2>
    </div>

    <div class="content">
        <p>Hello <strong><?= esc($username) ?></strong>,</p>

        <p>We received a request to reset the password for your <strong>Prj Images</strong> account. Click the button below to set a new password:</p>

        <div class="button-container">
            <a href="<?= esc($reset_link) ?>" class="button">Reset Password</a>
        </div>

        <p>This password reset link will expire in <strong>1 hour</strong> for security reasons.</p>
        
        <p>If the button above doesn't work, please copy and paste the following URL into your web browser:</p>

        <div class="link-box">
            <?= esc($reset_link) ?>
        </div>

        <p style="margin-top: 20px; color: #888; font-size: 14px;">If you didn't request this change, you can safely ignore this email. Your password will remain unchanged.</p>
    </div>

    <div class="footer">
        <p>Sent with &hearts; from the <strong>Prj Images Team</strong>.<br>
            This is an automated message, please do not reply.</p>
    </div>
</div>
</body>
</html>
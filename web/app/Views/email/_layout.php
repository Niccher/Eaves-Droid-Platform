<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($subject ?? 'Eaves Droid Notification') ?></title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        @media only screen and (max-width: 600px) {
            .email-container { width: 100% !important; }
            .content-padding { padding: 24px 16px !important; }
            .header-title { font-size: 22px !important; }
            .footer-text { font-size: 11px !important; }
        }
        @media (prefers-color-scheme: dark) {
            .email-wrapper { background-color: #0f172a !important; }
            .email-container { background-color: #1e293b !important; border-color: #334155 !important; }
            .content-text { color: #e2e8f0 !important; }
            .meta-label { color: #94a3b8 !important; }
            .meta-value { color: #f1f5f9 !important; }
            .security-footer { background-color: #0f172a !important; border-color: #334155 !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;line-height:1.6;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;">
    <!--[if mso]>
    <center style="width:100%;background:#f1f5f9;">
    <table cellpadding="0" cellspacing="0" border="0" width="100%">
    <tr><td align="center">
    <![endif]-->
    <div class="email-wrapper" style="width:100%;background:#f1f5f9;padding:40px 16px;">
        <table class="email-container" role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(15,23,42,0.08);border:1px solid #e2e8f0;">
            <!-- Header -->
            <thead>
            <tr>
                <td style="background:#ffffff;border-bottom:1px solid #e2e8f0;padding:32px 24px;text-align:center;">
                    <div style="display:inline-block;width:56px;height:56px;background:linear-gradient(135deg,#00d4aa 0%,#00b894 100%);border-radius:14px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;box-shadow:0 4px 16px rgba(0,212,170,0.3);">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:block;">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </div>
                    <h1 class="header-title" style="margin:0;color:#0f172a;font-size:26px;font-weight:700;letter-spacing:-0.5px;">Eaves Droid</h1>
                    <p style="margin:8px 0 0;color:#64748b;font-size:13px;font-weight:500;">Advanced Mobile Forensic & Data Intelligence</p>
                </td>
            </tr>
            </thead>

            <!-- Main Content -->
            <tbody>
            <tr>
                <td class="content-padding" style="padding:32px 24px;">
                    <?= $content ?>
                </td>
            </tr>
            </tbody>

            <!-- Security Footer -->
            <tfoot>
            <tr>
                <td style="padding:0 24px 24px;">
                    <?= $securityAction ? view('email/_security_footer', [
                        'securityAction'        => $securityAction ?? '',
                        'securityDescription'   => $securityDescription ?? '',
                        'securityStatus'        => $securityStatus ?? 'success',
                        'securityInitiatedBy'   => $securityInitiatedBy ?? '',
                        'securityBrowser'       => $securityBrowser ?? '',
                        'securityBrowserIp'     => $securityBrowserIp ?? '',
                        'securityExecutedAt'    => $securityExecutedAt ?? '',
                    ]) : '' ?>
                </td>
            </tr>
            </tfoot>

            <!-- Brand Footer -->
            <tfoot>
            <tr>
                <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:20px 24px;text-align:center;">
                    <p style="margin:0 0 8px;color:#94a3b8;font-size:11px;letter-spacing:0.5px;text-transform:uppercase;">
                        Eaves Droid — Advanced Mobile Forensic & Data Intelligence Platform
                    </p>
                    <p class="footer-text" style="margin:0;color:#cbd5e1;font-size:11px;">
                        This email was sent to <strong><?= esc($email ?? 'your registered email') ?></strong>.<br>
                        If you didn't initiate this action, <a href="<?= site_url('support') ?>" style="color:#00d4aa;text-decoration:none;">contact support immediately</a>.
                    </p>
                </td>
            </tr>
            </tfoot>
        </table>

        <!-- Mobile spacing fix -->
        <div style="height:40px;display:block;line-height:40px;font-size:40px;">&nbsp;</div>
    </div>
    <!--[if mso]>
    </td></tr></table>
    </center>
    <![endif]-->
</body>
</html>
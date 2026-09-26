<div style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
    <div style="background-color: #1e293b; color: #ffffff; padding: 20px; text-align: center;">
        <h2 style="margin: 0; font-size: 20px; font-weight: 700; color: #38bdf8;">🔔 Pesapal IPN Event Alert</h2>
        <p style="margin: 5px 0 0 0; font-size: 13px; color: #94a3b8;">Merchant Instant Payment Notification Listener</p>
    </div>

    <div style="padding: 24px; color: #334155; line-height: 1.6;">
        <p style="margin-top: 0;">Hello <strong><?= esc($adminUsername ?? 'Admin') ?></strong>,</p>
        <p>An IPN webhook request was received by the Eaves Droid WebApp payment listener. Below are the execution and audit log details:</p>

        <div style="background-color: #f8fafc; border-left: 4px solid #3b82f6; padding: 16px; margin: 20px 0; border-radius: 4px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <tr>
                    <td style="padding: 6px 0; color: #64748b; width: 40%;"><strong>Event Status:</strong></td>
                    <td style="padding: 6px 0; font-weight: bold; color: #0f172a;"><?= esc($status ?? 'UNKNOWN') ?></td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b;"><strong>Visitor IP:</strong></td>
                    <td style="padding: 6px 0; color: #0f172a; font-family: monospace;"><?= esc($ip ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b;"><strong>HTTP Method:</strong></td>
                    <td style="padding: 6px 0; color: #0f172a;"><?= esc($method ?? 'GET') ?></td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b;"><strong>Order Tracking ID:</strong></td>
                    <td style="padding: 6px 0; color: #0f172a; font-family: monospace;"><?= esc($orderTrackingId ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b;"><strong>Merchant Reference:</strong></td>
                    <td style="padding: 6px 0; color: #0f172a; font-family: monospace;"><?= esc($reference ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b;"><strong>Notification Type:</strong></td>
                    <td style="padding: 6px 0; color: #0f172a;"><?= esc($notificationType ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b;"><strong>Timestamp:</strong></td>
                    <td style="padding: 6px 0; color: #0f172a;"><?= esc($time ?? date('Y-m-d H:i:s T')) ?></td>
                </tr>
            </table>
        </div>

        <p style="font-size: 13px; color: #64748b;">If this payment is completed, the user's subscription tier has been automatically updated in the database.</p>
    </div>
</div>

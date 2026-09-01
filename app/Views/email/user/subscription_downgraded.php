<div style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
    <div style="background-color: #1e293b; color: #ffffff; padding: 24px; text-align: center;">
        <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: #f59e0b;">Subscription Downgrade Scheduled</h2>
        <p style="margin: 6px 0 0 0; font-size: 14px; color: #94a3b8;">Eaves Droid Subscription Notification</p>
    </div>

    <div style="padding: 24px; color: #334155; line-height: 1.6;">
        <p style="margin-top: 0;">Hello <strong><?= esc($username ?? 'User') ?></strong>,</p>
        <p>We received your request to downgrade your Eaves Droid subscription plan.</p>

        <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 16px; margin: 20px 0; border-radius: 4px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <tr>
                    <td style="padding: 6px 0; color: #64748b; width: 45%;"><strong>Current Plan:</strong></td>
                    <td style="padding: 6px 0; font-weight: bold; color: #0f172a;"><?= esc($oldPlanName ?? 'Platinum') ?></td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b;"><strong>New Scheduled Plan:</strong></td>
                    <td style="padding: 6px 0; font-weight: bold; color: #d97706;"><?= esc($newPlanName ?? 'Gold') ?></td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b;"><strong>Grace Period Expiry:</strong></td>
                    <td style="padding: 6px 0; font-weight: bold; color: #0f172a;"><?= esc($periodEnd ?? date('F j, Y')) ?></td>
                </tr>
            </table>
        </div>

        <div style="background-color: #f8fafc; padding: 16px; border-radius: 6px; margin-bottom: 20px; font-size: 13px; color: #475569;">
            <strong style="color: #0f172a; display: block; margin-bottom: 6px;">Important Details:</strong>
            <ul style="margin: 0; padding-left: 20px;">
                <li style="margin-bottom: 4px;">You will retain <strong>100% full access</strong> to your current <strong><?= esc($oldPlanName ?? 'Platinum') ?></strong> benefits until <strong><?= esc($periodEnd ?? date('F j, Y')) ?></strong>.</li>
                <li style="margin-bottom: 4px;">No additional charges will occur on your current billing cycle.</li>
                <li>If your active device count exceeds the <strong><?= esc($newPlanName ?? 'Gold') ?></strong> limit on transition date, you will be prompted to select which devices remain active. Historical logs will remain preserved in read-only mode.</li>
            </ul>
        </div>

        <div style="text-align: center; margin-top: 24px;">
            <a href="<?= esc($dashboardUrl ?? base_url('billing')) ?>" style="background-color: #3b82f6; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; display: inline-block;">Manage Subscription</a>
        </div>
    </div>
</div>

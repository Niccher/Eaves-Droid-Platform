<?php
/**
 * Content template for: New Support Reply from Admin
 * Layout: email/_layout
 */
?>

<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:28px;">

    <!-- Header accent bar -->
    <div style="border-left:4px solid #16a34a;padding-left:14px;margin-bottom:20px;">
        <h2 style="margin:0;color:#0f172a;font-size:20px;font-weight:700;letter-spacing:-0.3px;">New Reply from Support</h2>
        <p style="margin:4px 0 0;color:#64748b;font-size:13px;">The support team has responded to your message</p>
    </div>

    <p style="color:#475569;margin:0 0 18px;font-size:14px;">Hello <strong><?= esc($username ?? 'User') ?></strong>,</p>

    <!-- Sender card -->
    <div style="display:flex;align-items:center;gap:12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:20px;">
        <div style="width:40px;height:40px;border-radius:50%;background:#16a34a;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:16px;flex-shrink:0;">
            ST
        </div>
        <div>
            <div style="font-weight:600;color:#0f172a;font-size:14px;">Support Team</div>
            <div style="color:#64748b;font-size:12px;">Official Support</div>
        </div>
        <div style="margin-left:auto;color:#64748b;font-size:12px;"><?= esc($sentAt ?? date('M d, Y H:i:s')) ?></div>
    </div>

    <!-- Message preview -->
    <p style="color:#475569;font-size:14px;margin:0 0 10px;">Their response:</p>
    <div style="background:#f8fafc;border-left:3px solid #16a34a;border-radius:0 6px 6px 0;padding:14px 16px;margin:0 0 16px;color:#334155;font-size:14px;font-style:italic;line-height:1.6;">
        &ldquo;<?= esc($messageText ?? '') ?>&rdquo;
    </div>

    <?php if (!empty($hasAttachment)): ?>
        <div style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:13px;margin-bottom:16px;padding:10px 12px;background:#fefce8;border:1px solid #fef08a;border-radius:6px;">
            <span style="font-size:16px;">&#128206;</span>
            <em>This reply includes an image attachment. Open the chat to view it.</em>
        </div>
    <?php endif; ?>

    <!-- CTA Button -->
    <div style="text-align:center;margin:24px 0 8px;">
        <a href="<?= base_url('support/chat') ?>" style="display:inline-block;padding:13px 32px;background:#16a34a;color:#fff;text-decoration:none;border-radius:8px;font-weight:700;font-size:14px;letter-spacing:0.3px;">View Chat &amp; Reply &rarr;</a>
    </div>

    <p style="text-align:center;color:#94a3b8;font-size:12px;margin-top:12px;">Reply directly to this email or click the button above to respond through the portal.</p>
</div>

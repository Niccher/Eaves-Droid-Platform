<?php date_default_timezone_set('Africa/Nairobi'); ?>
<div class="content-wrapper" style="background:#f0f2f5;">
    <!-- Header -->
    <section class="content-header" style="background:#fff;border-bottom:1px solid #e5e7eb;padding:14px 20px;">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:10px;">
                <div class="d-flex align-items-center" style="gap:12px;">
                    <a href="<?= base_url('contacts') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div class="d-flex align-items-center" style="gap:10px;">
                        <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;font-weight:700;flex-shrink:0;">
                            <?= strtoupper(substr($sms_saved ?? 'U', 0, 1)) ?>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:1rem;color:#111;"><?= htmlspecialchars($sms_saved ?? 'Unknown') ?></div>
                            <div style="font-size:0.78rem;color:#6b7280;"><?= htmlspecialchars($sms_person ?? '') ?></div>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center" style="gap:8px;">
                    <span class="badge" style="background:#ede9fe;color:#7c3aed;padding:6px 12px;border-radius:20px;font-size:0.8rem;">
                        <i class="fas fa-sms mr-1"></i> <?= count($sms_thread) ?> messages
                    </span>
                    <?php if (!empty($number_variants)): ?>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" style="border-radius:8px;font-size:0.78rem;">
                            <i class="fas fa-phone-alt mr-1"></i> Number variants
                        </button>
                        <div class="dropdown-menu dropdown-menu-right p-2" style="min-width:200px;">
                            <?php foreach ($number_variants as $v): ?>
                                <div class="d-flex align-items-center py-1 px-2" style="font-size:0.82rem;font-family:monospace;color:#374151;">
                                    <i class="fas fa-check-circle text-success mr-2" style="font-size:0.7rem;"></i><?= htmlspecialchars($v) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="content" style="padding:20px;">
        <div class="container-fluid">

            <?php if (empty($sms_thread)): ?>
            <div class="text-center py-5">
                <div style="width:72px;height:72px;border-radius:50%;background:#f3f4f6;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="fas fa-comment-slash" style="font-size:1.8rem;color:#9ca3af;"></i>
                </div>
                <h5 style="color:#374151;">No SMS conversations found</h5>
                <p class="text-muted" style="font-size:0.88rem;">No messages found for any of the number variants searched.</p>
            </div>
            <?php else: ?>

            <!-- Chat container -->
            <div style="max-width:760px;margin:0 auto;">
                <?php
                $prevDate = '';
                foreach ($sms_thread as $sms):
                    $msgDate = date('D, d M Y', $sms['sms_time'] / 1000);
                    $msgTime = date('H:i', $sms['sms_time'] / 1000);
                    $isInbox = strtolower($sms['sms_type'] ?? '') === 'inbox';
                    $rawBody = $sms['sms_body'] ?? '';
                    // Try base64 decode, fallback to raw
                    $decoded = base64_decode($rawBody, true);
                    $body = ($decoded !== false && mb_detect_encoding($decoded, 'UTF-8', true)) ? $decoded : $rawBody;
                ?>
                    <?php if ($msgDate !== $prevDate): $prevDate = $msgDate; ?>
                    <div class="text-center my-3">
                        <span style="background:#e5e7eb;color:#6b7280;font-size:0.75rem;padding:4px 14px;border-radius:20px;">
                            <?= $msgDate ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex mb-2 <?= $isInbox ? 'justify-content-start' : 'justify-content-end' ?>">
                        <?php if ($isInbox): ?>
                        <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;color:#fff;font-size:0.75rem;font-weight:700;flex-shrink:0;align-self:flex-end;margin-right:8px;">
                            <?= strtoupper(substr($sms_saved ?? 'U', 0, 1)) ?>
                        </div>
                        <?php endif; ?>
                        <div style="max-width:72%;">
                            <div style="
                                background:<?= $isInbox ? '#fff' : 'linear-gradient(135deg,#6366f1,#818cf8)' ?>;
                                color:<?= $isInbox ? '#111' : '#fff' ?>;
                                padding:10px 14px;
                                border-radius:<?= $isInbox ? '4px 18px 18px 18px' : '18px 4px 18px 18px' ?>;
                                box-shadow:0 1px 3px rgba(0,0,0,0.08);
                                font-size:0.9rem;
                                line-height:1.5;
                                word-break:break-word;
                            "><?= nl2br(htmlspecialchars($body)) ?></div>
                            <div class="d-flex align-items-center mt-1 <?= $isInbox ? '' : 'justify-content-end' ?>" style="gap:5px;">
                                <small style="color:#9ca3af;font-size:0.72rem;"><?= $msgTime ?></small>
                                <?php if (!$isInbox): ?>
                                    <i class="fas fa-check-double" style="color:#6366f1;font-size:0.65rem;"></i>
                                <?php else: ?>
                                    <small style="color:#9ca3af;font-size:0.7rem;"><?= htmlspecialchars($sms['sms_number'] ?? '') ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!$isInbox): ?>
                        <div style="width:30px;height:30px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;color:#6b7280;font-size:0.75rem;flex-shrink:0;align-self:flex-end;margin-left:8px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <!-- End of thread marker -->
                <div class="text-center my-4">
                    <span style="background:#f3f4f6;color:#9ca3af;font-size:0.72rem;padding:4px 14px;border-radius:20px;">
                        <i class="fas fa-lock mr-1"></i> End of conversation
                    </span>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </section>
</div>

<style>
.content-wrapper { min-height: 100vh; }
</style>

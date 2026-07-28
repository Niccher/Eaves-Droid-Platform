<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-cogs text-purple mr-2"></i>Default Apps</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Default browser, dialer, SMS, launcher, and other intent handlers</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Default App Snapshots <small class="text-muted ml-2"><?= count($rows) ?></small></h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Handler</th>
                                <th>Package</th>
                                <th>App Name</th>
                                <th>System</th>
                                <th>Extracted</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($rows)): foreach ($rows as $r): ?>
                            <?php
                            $handlers = [
                                'browser' => ['label' => 'Browser', 'icon' => 'fas fa-globe'],
                                'dialer' => ['label' => 'Dialer', 'icon' => 'fas fa-phone'],
                                'sms' => ['label' => 'SMS', 'icon' => 'fas fa-sms'],
                                'launcher' => ['label' => 'Launcher', 'icon' => 'fas fa-home'],
                                'email' => ['label' => 'Email', 'icon' => 'fas fa-envelope'],
                                'maps' => ['label' => 'Maps', 'icon' => 'fas fa-map-marked-alt'],
                                'music' => ['label' => 'Music', 'icon' => 'fas fa-music'],
                                'gallery' => ['label' => 'Gallery', 'icon' => 'fas fa-images'],
                                'browser_app' => ['label' => 'Browser App', 'icon' => 'fas fa-globe'],
                                'sms_package' => ['label' => 'SMS Package', 'icon' => 'fas fa-comment-sms'],
                            ];
                            ?>
                            <?php foreach ($handlers as $key => $info): 
                                $jsonCol = 'default_' . $key . '_json';
                                $app = $r[$jsonCol] ?? null;
                                if (is_string($app)) $app = json_decode($app, true);
                                if (!empty($app) && isset($app['package_name'])):
                            ?>
                                <tr>
                                    <td><i class="<?= $info['icon'] ?> mr-1"></i> <strong><?= $info['label'] ?></strong></td>
                                    <td><code class="small"><?= esc($app['package_name']) ?></code></td>
                                    <td><?= esc($app['app_name'] ?? 'N/A') ?></td>
                                    <td class="text-center">
                                        <?= isset($app['is_system']) && $app['is_system'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?>
                                    </td>
                                    <td><?= format_timestamp_display((int)$r['extracted_at']) ?></td>
                                    <td class="text-center">
                                            <button class="btn btn-sm btn-outline-danger delete-row"
                                                data-id="<?= $r['id'] ?? '' ?>"
                                                data-url="<?= base_url('advanced/default_apps/delete') ?>"
                                                title="Delete this row">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endif; endforeach; ?>
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
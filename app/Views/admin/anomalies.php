<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-exclamation-triangle text-warning mr-1"></i> Anomaly Detection Engine</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item active">Anomaly Engine</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle mr-2"></i> <?= session()->getFlashdata('success') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle mr-2"></i> <?= session()->getFlashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php endif; ?>

            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1">Anomaly Engine Configuration</h5>
                        <p class="mb-0 small text-muted">Configure the anomaly detection engine — select default detection engine (PHP, Python, or Hybrid), and control which algorithms users can access.</p>
                    </div>
                </div>
            </div>

            <div class="card card-warning shadow-sm">
                <div class="card-body p-0">
                    <form method="post" action="<?= base_url('admin/anomalies') ?>" id="anomalyForm">
                        <?= csrf_field() ?>

                        <ul class="nav nav-tabs" id="adminAnomalyTabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tab-engines">
                                    <i class="fas fa-microchip mr-2"></i>Engines
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab-algorithms">
                                    <i class="fas fa-list mr-2"></i>Algorithms
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab-history">
                                    <i class="fas fa-history mr-2"></i>Run History
                                    <?php if (!empty($job_history)): ?>
                                    <span class="badge badge-secondary ml-1"><?= count($job_history) ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content border border-top-0 p-4 bg-white">
                            <!-- ENGINES TAB -->
                            <div class="tab-pane fade show active" id="tab-engines">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Choose which detection engine users can access. <strong>Both</strong> runs PHP for PHP-compatible algorithms and Python for Python-only algorithms.
                                </div>
                                <div class="row">
                                    <?php foreach ($engines as $e):
                                        $checked = $default_engine === $e['id'] ? 'checked' : '';
                                    ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 border <?= $checked ? 'border-warning' : '' ?>">
                                            <div class="card-body text-center">
                                                <div class="mb-3" style="font-size:2.5rem;">
                                                    <i class="<?= $e['icon'] ?> text-<?= $e['icon_color'] ?>"></i>
                                                </div>
                                                <h5 class="font-weight-bold"><?= $e['label'] ?></h5>
                                                <p class="text-muted small"><?= $e['description'] ?></p>
                                                <div class="d-flex justify-content-center" style="gap:.25rem;">
                                                    <?php foreach ($e['badges'] as $b): ?>
                                                    <span class="badge badge-<?= $b['color'] ?>">
                                                        <i class="<?= $b['icon'] ?> mr-1"></i><?= $b['text'] ?>
                                                    </span>
                                                    <?php endforeach; ?>
                                                </div>
                                                <div class="mt-3">
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="engine_<?= $e['id'] ?>"
                                                               name="default_engine" value="<?= $e['id'] ?>"
                                                               class="custom-control-input" <?= $checked ?>>
                                                        <label class="custom-control-label font-weight-bold" for="engine_<?= $e['id'] ?>">
                                                            <?= $e['id'] === 'both' ? 'Enable Both' : 'Set as Default' ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- ALGORITHMS TAB -->
                            <div class="tab-pane fade" id="tab-algorithms">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Select which algorithms users can run. Unchecked algorithms will be hidden from users. If none are selected, all algorithms are available.
                                </div>

                                <?php
                                $allowedSet = !empty($allowed_algorithms) ? array_flip($allowed_algorithms) : [];
                                $allAllowed = empty($allowed_algorithms);
                                ?>
                                <div class="mb-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="toggle-all-algs" <?= $allAllowed ? 'checked' : '' ?>>
                                        <label class="custom-control-label font-weight-bold" for="toggle-all-algs">
                                            <i class="fas fa-check-double mr-1"></i> Allow All Algorithms
                                        </label>
                                    </div>
                                    <small class="text-muted">When checked, users see all algorithms. Uncheck to restrict.</small>
                                </div>
                                <hr>

                                <?php foreach ($categories as $catKey => $cat): ?>
                                <div class="card card-outline card-<?= $cat['color'] ?> shadow-sm mb-3">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="<?= $cat['icon'] ?> mr-2"></i><?= $cat['label'] ?>
                                        </h3>
                                        <div class="card-tools">
                                            <span class="badge badge-<?= $cat['color'] ?>"><?= count($cat['algorithms']) ?> algorithms</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <?php foreach ($cat['algorithms'] as $alg): 
                                                $checked = $allAllowed || isset($allowedSet[$alg['id']]);
                                                $compatLabel = match($alg['compat']) {
                                                    'both' => 'PHP + Python',
                                                    'php' => 'PHP',
                                                    'python' => 'Python',
                                                    default => $alg['compat'],
                                                };
                                                $compatBadge = match($alg['compat']) {
                                                    'both' => 'primary',
                                                    'php' => 'success',
                                                    'python' => 'warning',
                                                    default => 'secondary',
                                                };
                                            ?>
                                            <div class="col-md-6 col-lg-4 mb-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input alg-checkbox"
                                                           id="alg_<?= $alg['id'] ?>"
                                                           name="allowed_algorithms[]" value="<?= $alg['id'] ?>"
                                                           <?= $checked ? 'checked' : '' ?>>
                                                    <label class="custom-control-label" for="alg_<?= $alg['id'] ?>">
                                                        <strong><?= esc($alg['name']) ?></strong>
                                                        <span class="badge badge-<?= $compatBadge ?> ml-1"><?= $compatLabel ?></span>
                                                        <br>
                                                        <small class="text-muted"><?= esc($alg['description']) ?></small>
                                                    </label>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            </div>

                    <!-- RUN HISTORY TAB (inside tab-content) -->
                    <div class="tab-pane fade" id="tab-history">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Anomaly detection runs logged by the system across all users.
                        </div>

                        <?php if (empty($job_history)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            <p class="h5">No anomaly detection runs have been logged yet.</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Owner</th>
                                        <th>Engine</th>
                                        <th>Algorithms</th>
                                        <th>Scope</th>
                                        <th>Status</th>
                                        <th>Time Taken</th>
                                        <th>Run At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; ?>
                                    <?php foreach ($job_history as $j): ?>
                                    <tr>
                                        <td class="text-muted"><?= $i++ ?></td>
                                        <td>
                                            <i class="fas fa-user-circle mr-1"></i>
                                            <?= esc($j['owner_name'] ?? 'Unknown') ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $j['engine'] === 'python' ? 'warning' : ($j['engine'] === 'php' ? 'success' : 'primary') ?>">
                                                <i class="fas fa-<?= $j['engine'] === 'python' ? 'robot' : ($j['engine'] === 'php' ? 'code' : 'cogs') ?> mr-1"></i>
                                                <?= ucfirst($j['engine'] ?? '?') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary badge-pill mr-1"><?= $j['algorithm_count'] ?></span>
                                            <?php if (!empty($j['algorithm_names'])): ?>
                                                <?php foreach (array_slice($j['algorithm_names'], 0, 3) as $nm): ?>
                                                <span class="badge badge-light border mr-1"><?= esc($nm) ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($j['algorithm_names']) > 3): ?>
                                                <span class="badge badge-light border">+<?= count($j['algorithm_names']) - 3 ?></span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($j['scope'] === 'incremental'): ?>
                                            <span class="badge badge-info">New Data</span>
                                            <?php else: ?>
                                            <span class="badge badge-secondary">Full Scan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php $statusBadge = match($j['status']) {
                                                'completed' => 'success',
                                                'running' => 'primary',
                                                'failed' => 'danger',
                                                'pending' => 'secondary',
                                                default => 'secondary',
                                            }; ?>
                                            <span class="badge badge-<?= $statusBadge ?>">
                                                <i class="fas fa-<?= $j['status'] === 'completed' ? 'check-circle' : ($j['status'] === 'running' ? 'spinner' : ($j['status'] === 'failed' ? 'times-circle' : 'clock')) ?> mr-1"></i>
                                                <?= ucfirst($j['status'] ?? '?') ?>
                                            </span>
                                        </td>
                                        <td class="text-nowrap">
                                            <?php if ($j['completed_at'] && $j['time_taken'] > 0): ?>
                                            <i class="far fa-clock mr-1"></i>
                                            <?php
                                                $t = (int)$j['time_taken'];
                                                if ($t >= 60) {
                                                    echo floor($t / 60) . 'm ' . ($t % 60) . 's';
                                                } else {
                                                    echo $t . 's';
                                                }
                                            ?>
                                            <?php else: ?>
                                            <span class="text-muted">&mdash;</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-nowrap">
                                            <i class="far fa-calendar-alt mr-1"></i>
                                            <?= date('M j, Y g:i A', strtotime($j['created_at'])) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                        </div>

                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-warning font-weight-bold shadow-sm px-4">
                                <i class="fas fa-save mr-2"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.getElementById('toggle-all-algs').addEventListener('change', function() {
    const checked = this.checked;
    document.querySelectorAll('.alg-checkbox').forEach(function(cb) {
        cb.checked = checked;
    });
});
</script>

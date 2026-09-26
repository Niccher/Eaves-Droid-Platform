                            <div class="tab-pane active show" id="pane-history" role="tabpanel">
                                <div class="callout callout-success bg-light border-left-success py-2 px-3 mb-3 small">
                                    <i class="fas fa-history text-success mr-1"></i>
                                    Recent anomaly detection forensics runs logged across all users and devices.
                                </div>

                                <?php if (empty($job_history)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block text-secondary"></i>
                                    <p class="h6">No anomaly detection runs have been executed yet.</p>
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
                                                    <i class="fas fa-user-circle mr-1 text-muted"></i>
                                                    <?= esc($j['owner_name'] ?? 'Unknown') ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?= ($j['engine'] ?? '') === 'python' ? 'warning' : (($j['engine'] ?? '') === 'php' ? 'success' : 'primary') ?>">
                                                        <i class="fas fa-<?= ($j['engine'] ?? '') === 'python' ? 'robot' : (($j['engine'] ?? '') === 'php' ? 'code' : 'cogs') ?> mr-1"></i>
                                                        <?= ucfirst($j['engine'] ?? 'PHP') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-secondary badge-pill mr-1"><?= $j['algorithm_count'] ?? 0 ?></span>
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
                                                    <?php if (($j['scope'] ?? '') === 'incremental'): ?>
                                                    <span class="badge badge-info">Incremental</span>
                                                    <?php else: ?>
                                                    <span class="badge badge-secondary">Full Extraction</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php $statusBadge = match($j['status'] ?? '') {
                                                        'completed' => 'success',
                                                        'running' => 'primary',
                                                        'failed' => 'danger',
                                                        default => 'secondary',
                                                    }; ?>
                                                    <span class="badge badge-<?= $statusBadge ?>">
                                                        <i class="fas fa-<?= ($j['status'] ?? '') === 'completed' ? 'check-circle' : (($j['status'] ?? '') === 'running' ? 'spinner fa-spin' : (($j['status'] ?? '') === 'failed' ? 'times-circle' : 'clock')) ?> mr-1"></i>
                                                        <?= ucfirst($j['status'] ?? 'completed') ?>
                                                    </span>
                                                </td>
                                                <td class="text-nowrap">
                                                    <?php if (!empty($j['completed_at']) && !empty($j['time_taken'])): ?>
                                                    <i class="far fa-clock mr-1 text-muted"></i>
                                                    <?php
                                                        $t = (int)$j['time_taken'];
                                                        echo ($t >= 60) ? (floor($t / 60) . 'm ' . ($t % 60) . 's') : ($t . 's');
                                                    ?>
                                                    <?php else: ?>
                                                    <span class="text-muted">&mdash;</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-nowrap small text-muted">
                                                    <i class="far fa-calendar-alt mr-1"></i>
                                                    <?= !empty($j['created_at']) ? date('M j, Y g:i A', strtotime($j['created_at'])) : '-' ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>


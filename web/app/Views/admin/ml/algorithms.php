                            <div class="tab-pane active show" id="pane-algorithms" role="tabpanel">
                                <form method="post" action="<?= base_url('admin/anomalies') ?>" id="anomalyAlgoForm">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="default_engine" value="<?= esc($default_engine ?? 'php') ?>">
                                    <div class="callout callout-warning bg-light border-left-warning py-2 px-3 mb-3 small">
                                        <i class="fas fa-sliders-h text-warning mr-1"></i>
                                        Select which anomaly detection algorithms users can run. Unchecked algorithms will be hidden from users. When "Allow All" is checked, all 15 detectors are active.
                                    </div>

                                    <?php
                                    $allowedSet = !empty($allowed_algorithms) ? array_flip($allowed_algorithms) : [];
                                    $allAllowed = empty($allowed_algorithms);
                                    ?>
                                    <div class="mb-3 d-flex justify-content-between align-items-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="toggle-all-algs" <?= $allAllowed ? 'checked' : '' ?>>
                                            <label class="custom-control-label font-weight-bold" for="toggle-all-algs">
                                                <i class="fas fa-check-double mr-1 text-primary"></i> Allow All Algorithms
                                            </label>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-warning font-weight-bold">
                                            <i class="fas fa-save mr-1"></i> Save Allowed Algorithms
                                        </button>
                                    </div>
                                    <hr>

                                    <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $catKey => $cat): ?>
                                    <div class="card card-outline card-<?= $cat['color'] ?> shadow-sm mb-3">
                                        <div class="card-header py-2">
                                            <h5 class="card-title text-dark font-weight-bold mb-0">
                                                <i class="<?= $cat['icon'] ?> text-<?= $cat['color'] ?> mr-2"></i><?= $cat['label'] ?>
                                            </h5>
                                            <div class="card-tools">
                                                <span class="badge badge-<?= $cat['color'] ?>"><?= count($cat['algorithms']) ?> algorithms</span>
                                            </div>
                                        </div>
                                        <div class="card-body py-2">
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
                                                            <span class="badge badge-<?= $compatBadge ?> ml-1" style="font-size:10px;"><?= $compatLabel ?></span>
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
                                    <?php endif; ?>

                                    <div class="border-top pt-3 text-right">
                                        <button type="submit" class="btn btn-warning font-weight-bold"><i class="fas fa-save mr-1"></i> Save Algorithm Preferences</button>
                                    </div>
                                </form>
                            </div>


                            <div class="tab-pane active show" id="pane-engines" role="tabpanel">
                                <form method="post" action="<?= base_url('admin/anomalies') ?>" id="anomalyEngineForm">
                                    <?= csrf_field() ?>
                                    <div class="callout callout-info bg-light border-left-info py-2 px-3 mb-3 small">
                                        <i class="fas fa-info-circle text-info mr-1"></i>
                                        Choose which detection engine is enabled for anomalous behavior analysis. <strong>Hybrid Mode</strong> automatically routes deep-learning detectors to the Python container and falls back gracefully to local PHP-ML if unreachable.
                                    </div>
                                    <div class="row">
                                        <?php if (!empty($engines)): ?>
                                        <?php foreach ($engines as $e):
                                            $checked = ($default_engine ?? 'php') === $e['id'] ? 'checked' : '';
                                        ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100 border <?= $checked ? 'border-primary shadow-sm' : '' ?>">
                                                <div class="card-body text-center">
                                                    <div class="mb-3" style="font-size:2.5rem;">
                                                        <i class="<?= $e['icon'] ?> text-<?= $e['icon_color'] ?>"></i>
                                                    </div>
                                                    <h5 class="font-weight-bold"><?= $e['label'] ?></h5>
                                                    <p class="text-muted small"><?= $e['description'] ?></p>
                                                    <div class="d-flex justify-content-center flex-wrap" style="gap:.25rem;">
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
                                                                <?= $e['id'] === 'both' ? 'Enable Hybrid Failover' : 'Set as Default' ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="border-top pt-3 text-right">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Engine Selection</button>
                                    </div>
                                </form>
                            </div>


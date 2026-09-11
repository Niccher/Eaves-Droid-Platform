<div class="tab-pane fade <?= ($active_tab === 'phpml') ? 'show active' : '' ?>" id="pane-phpml" role="tabpanel">
                                <form action="<?= base_url('admin/settings/update') ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="section" value="ml">

                                    <div class="callout callout-info bg-light py-2 px-3 mb-3 small">
                                        <i class="fas fa-database text-info mr-1"></i>
                                        Algorithm parameters below are stored in the database (<code>settings</code> table, <code>class='ml'</code>) and read at runtime by the PHP-ML engine. Defaults apply when no custom value has been saved.
                                    </div>

                                <!-- K-Means -->
                                <div class="card card-outline card-info shadow-sm mb-3">
                                    <div class="card-header"><h3 class="card-title"><i class="fas fa-circle-nodes mr-1"></i> K-Means Clustering</h3></div>
                                    <div class="card-body">
                                        <div class="form-group row mb-0">
                                            <label class="col-sm-2 col-form-label">Number of Clusters (K) <small class="text-muted">(default: 3)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" name="ml_phpml_kmeans_k" class="form-control" value="<?= htmlspecialchars($settings['ml_phpml_kmeans_k'] ?? '3') ?>" min="2" max="20">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    <strong>How it works:</strong> K-Means partitions data points into <em>K</em> distinct clusters by iteratively assigning each point to the nearest cluster centroid, then recalculating centroids as the mean of assigned points. Used to group similar behavioural patterns &mdash; e.g., typical calling times, regular locations, periodic SMS activity.
                                                    <br><br>
                                                    <strong>Low K (2&ndash;3):</strong> Broad, coarse groupings &mdash; detects only the most obvious behavioural modes. Fewer false positives, but may miss subtle patterns.
                                                    <br>
                                                    <strong>High K (8+):</strong> Fine-grained clustering &mdash; identifies small, specialised behaviour groups. Higher sensitivity, but increases false-positive risk from noise.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- DBSCAN -->
                                <div class="card card-outline card-info shadow-sm mb-3">
                                    <div class="card-header"><h3 class="card-title"><i class="fas fa-bezier-curve mr-1"></i> DBSCAN Density-Based Clustering</h3></div>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Epsilon (&epsilon;) <small class="text-muted">(default: 0.01)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" step="0.001" name="ml_phpml_dbscan_epsilon" class="form-control" value="<?= htmlspecialchars($settings['ml_phpml_dbscan_epsilon'] ?? '0.01') ?>" min="0.001" max="1">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    <strong>How it works:</strong> DBSCAN groups points that are closely packed together, marking points in low-density regions as outliers. Unlike K-Means, it does <em>not</em> require specifying the number of clusters &mdash; it discovers arbitrary-shaped clusters based on density. Ideal for geographic trajectory analysis (geo-fence breaches, velocity anomalies).
                                                    <br><br>
                                                    <strong>Low &epsilon; (0.001&ndash;0.01):</strong> Very strict neighbourhood &mdash; only extremely close points are considered neighbours. Produces many small clusters and more points flagged as outliers (anomalies). High sensitivity.
                                                    <br>
                                                    <strong>High &epsilon; (0.1+):</strong> Large neighbourhood radius &mdash; merges points into broad clusters. Fewer outliers, lower sensitivity. May miss subtle anomalies.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-0">
                                            <label class="col-sm-2 col-form-label">Min Samples <small class="text-muted">(default: 2)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" name="ml_phpml_dbscan_minpoints" class="form-control" value="<?= htmlspecialchars($settings['ml_phpml_dbscan_minpoints'] ?? '2') ?>" min="1" max="50">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    <strong>How it works:</strong> Minimum number of points required to form a dense neighbourhood.
                                                    <br><br>
                                                    <strong>Low (1&ndash;3):</strong> A few points forming a loose group are enough to form a cluster. More clusters, fewer outliers.
                                                    <br>
                                                    <strong>High (10+):</strong> Requires many points in a neighbourhood to form a cluster. More points classified as outliers (anomalies).
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Isolation Forest -->
                                <div class="card card-outline card-info shadow-sm mb-0">
                                    <div class="card-header"><h3 class="card-title"><i class="fas fa-tree mr-1"></i> Isolation Forest</h3></div>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Number of Trees <small class="text-muted">(default: 100)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" name="ml_phpml_isolationforest_trees" class="form-control" value="<?= htmlspecialchars($settings['ml_phpml_isolationforest_trees'] ?? '100') ?>" min="10" max="1000">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    <strong>How it works:</strong> Isolation Forest isolates anomalies by randomly partitioning the data with decision trees. AnomaliesController are few and different &mdash; they require fewer partitions to isolate, producing shorter path lengths. The algorithm builds an ensemble of trees (the "forest") and scores each point by its average path length. Highly effective for multi-dimensional anomaly detection across SMS, calls, locations, and app behaviour simultaneously.
                                                    <br><br>
                                                    <strong>Low (10&ndash;50):</strong> Fast training, lower memory &mdash; but higher variance in anomaly scores. May produce inconsistent results across runs.
                                                    <br>
                                                    <strong>High (500+):</strong> Stable, consistent anomaly scores. Converges to reliable estimates. Higher memory and CPU cost during training.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-0">
                                            <label class="col-sm-2 col-form-label">Samples Per Tree <small class="text-muted">(default: 256)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" name="ml_phpml_isolationforest_samples" class="form-control" value="<?= htmlspecialchars($settings['ml_phpml_isolationforest_samples'] ?? '256') ?>" min="32" max="4096">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    <strong>Low (32&ndash;128):</strong> Smaller subsamples &mdash; faster training, reduced memory. Good for very large datasets (&gt;100k rows). May reduce detection quality.
                                                    <br>
                                                    <strong>High (512+):</strong> Uses more data per tree &mdash; better representation of the data distribution. Higher memory footprint. Recommended for datasets with 10k&ndash;100k rows.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-top pt-3 mt-3 text-right">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save PHP-ML Settings</button>
                                    </div>
                                </form>
                            </div>



                            <!-- ======================== RUN HISTORY TAB ======================== -->
                            
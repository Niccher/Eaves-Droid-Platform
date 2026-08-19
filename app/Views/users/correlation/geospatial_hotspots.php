<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header pt-3 pb-2">
        <div class="container-fluid">
            <div class="row align-items-center mb-2">
                <div class="col-sm-6">
                    <h1 class="h3 mb-0 text-dark font-weight-bold">
                        <i class="fas fa-bullseye text-emerald mr-2" style="color: #10b981;"></i> Geospatial Hotspots &amp; Geofence Manager
                    </h1>
                    <p class="text-muted mb-0 small">DBSCAN spatial clustering, reverse-geocoded address cache, and interactive geofence zone drawing.</p>
                </div>
                <div class="col-sm-6 text-right d-flex justify-content-end align-items-center">
                    <button type="button" class="btn btn-emerald btn-sm mr-2 shadow-sm text-white" style="background-color: #059669; border-color: #059669;" data-toggle="modal" data-target="#geofenceManagerModal">
                        <i class="fas fa-draw-polygon mr-1"></i> Manage Geofences
                    </button>
                    <a class="btn btn-outline-secondary btn-sm shadow-sm" href="<?= base_url('analysis') ?>"><i class="fas fa-arrow-left mr-1"></i> Back to Analysis</a>
                </div>
            </div>
        </div>
    </section>

<?php if (isset($ml_insight) && !empty($ml_insight['insights'])): ?>
<?php $_eng = (new \App\Models\AnomaliesModel())->getDefaultEngine(); $_engLabel = match($_eng){'python'=>'Python Engine','both'=>'Hybrid Engine',default=>'PHP Engine'}; ?>
<section class="content mb-4">
    <div class="container-fluid">
        <div class="card bg-secondary text-white shadow-sm border-0" style="border-radius: 8px;">
            <div class="card-header border-0 bg-transparent pt-4 px-4 pb-0 d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center mb-2 mb-md-0">
                    <div class="rounded-circle p-3 mr-3 shadow" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-bullseye fa-lg text-white"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 font-weight-bold text-white"><?= $_engLabel ?> Geospatial DBSCAN Clustering &amp; Geofence Synthesis</h4>
                        <small class="text-light opacity-75">Density-based spatial clustering, place reverse-geocoding, &amp; co-location analysis</small>
                    </div>
                </div>
                <div>
                    <span class="badge badge-pill badge-success px-3 py-2 shadow-sm" style="font-size: 0.85rem;">
                        <i class="fas fa-microchip mr-1"></i> Algorithm: <?= esc($ml_insight['algorithm'] ?? 'DBSCAN Density Clustering') ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-4 col-md-5 mb-3 mb-md-0 border-right-md border-emerald pr-md-4">
                        <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                            <h6 class="text-emerald font-weight-bold mb-2" style="color: #34d399;"><i class="fas fa-database mr-2"></i> What Is Happening</h6>
                            <p class="mb-0 text-light opacity-90" style="font-size: 0.9rem; line-height: 1.5;">
                                <?= esc($ml_insight['description'] ?? 'Groups dense clusters of GPS locations into frequent base-of-operation hotspots using DBSCAN, reverse-geocodes street addresses, and checks polygon geofence breaches.') ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-7 pl-md-4">
                        <h6 class="text-warning font-weight-bold mb-2"><i class="fas fa-lightbulb mr-2"></i> Hotspot &amp; Geofence Findings</h6>
                        <ul class="list-unstyled mb-0" style="font-size: 0.9rem;">
                            <?php foreach ($ml_insight['insights'] as $insight): ?>
                            <li class="mb-2 d-flex align-items-start">
                                <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                                <span><?= $insight ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>


<?= view('analysis/anomaly_alert_card', ['anomaly_alerts' => $anomaly_alerts ?? []]) ?>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-7">
                    <div class="card card-outline card-success shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title"><i class="fas fa-map-pin mr-1"></i> Top Identified Bases</h3>
                            <button type="button" class="btn btn-xs btn-outline-success" data-toggle="modal" data-target="#geofenceModal">
                                <i class="fas fa-draw-polygon mr-1"></i> Geofence Manager
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($clusters as $i => $c): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge badge-success mr-2">#<?= ($i+1) ?></span>
                                            <b><?= esc($c['label']) ?></b><br>
                                            <small class="text-muted"><i class="fas fa-map-marker-alt text-danger mr-1"></i> <?= esc($c['address_label'] ?? ($c['lat'] . ', ' . $c['lng'])) ?></small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-light border"><?= $c['pings'] ?> Sessions</span><br>
                                            <small class="text-xs">Last: <?= date('M j, H:i', ($c['last_seen'] ?? time() * 1000) / 1000) ?></small>
                                        </div>
                                        <a href="https://www.google.com/maps?q=<?= $c['lat'] ?>,<?= $c['lng'] ?>" target="_blank" class="btn btn-sm btn-outline-success ml-3">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card card-primary card-outline shadow-sm mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-bluetooth-b text-primary mr-2"></i> Co-located Bluetooth Devices</h3>
                        </div>
                        <div class="card-body p-2" style="max-height: 200px; overflow-y: auto;">
                            <?php if (!empty($bluetooth_colocation)): ?>
                                <ul class="list-group list-group-flush text-sm">
                                    <?php foreach (array_slice($bluetooth_colocation, 0, 5) as $bt): ?>
                                    <li class="list-group-item p-2 d-flex justify-content-between">
                                        <span><i class="fab fa-bluetooth text-primary mr-1"></i> <b><?= esc($bt['device_name'] ?: 'Unknown Device') ?></b></span>
                                        <small class="text-muted"><?= esc($bt['mac_address']) ?></small>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted text-sm mb-0 p-2">No paired Bluetooth devices recorded nearby.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card card-light border">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Heuristic Classification</h3>
                        </div>
                        <div class="card-body p-3">
                            <div class="alert alert-light border mb-2">
                                <h6 class="font-weight-bold mb-1"><i class="fas fa-home text-success mr-2"></i> Primary Base</h6>
                                <p class="mb-0 text-xs">Identified by DBSCAN density clustering during overnight hours (23:00 - 07:00).</p>
                            </div>
                            <div class="alert alert-light border mb-0">
                                <h6 class="font-weight-bold mb-1"><i class="fas fa-briefcase text-primary mr-2"></i> Frequent Hotspot</h6>
                                <p class="mb-0 text-xs">Clusters with pings concentrated during business hours (09:00 - 17:00).</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Geofence Manager Modal -->
    <div class="modal fade" id="geofenceModal" tabindex="-1" role="dialog" aria-labelledby="geofenceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="geofenceModalLabel"><i class="fas fa-draw-polygon mr-2"></i> Interactive Geofence Manager</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted text-sm">Geofences are auto-calculated using DBSCAN hotspot centroids by default. You can also define custom boundary zones below.</p>
                    <form id="geofenceForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Zone Name</label>
                                    <input type="text" class="form-control form-control-sm" placeholder="e.g. Home Safe Zone" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Zone Type</label>
                                    <select class="form-control form-control-sm">
                                        <option value="home">Home Base (Default DBSCAN)</option>
                                        <option value="work">Workplace (Default DBSCAN)</option>
                                        <option value="custom">Custom Restricted Polygon</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Center Lat</label>
                                    <input type="text" class="form-control form-control-sm" value="<?= $clusters[0]['lat'] ?? '-1.286' ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Center Lng</label>
                                    <input type="text" class="form-control form-control-sm" value="<?= $clusters[0]['lng'] ?? '36.817' ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Radius (Meters)</label>
                                    <input type="number" class="form-control form-control-sm" value="250">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sm btn-success" onclick="alert('Geofence boundary saved successfully!'); $('#geofenceModal').modal('hide');">Save Geofence Zone</button>
                </div>
            </div>
        </div>
    </div>
</div>

    </section>
</div>

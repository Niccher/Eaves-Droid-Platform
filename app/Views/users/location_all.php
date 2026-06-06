<?php
/**
 * Location History View
 *
 * @var array $location_dump
 * @var int $totalLocations
 * @var object $pager
 * @var string $nav_urls
 */
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center">
                        <h1 class="h2 mb-0">
                            <i class="fas fa-map-marker-alt text-primary mr-2"></i>
                            Location History
                        </h1>
                        <div class="ml-3">
                            <span class="badge badge-light border p-2">
                                <i class="fas fa-chart-bar text-primary mr-1"></i>
                                Total: <b><?php echo $totalLocations ?? 0 ?></b>
                            </span>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">GPS and Network location tracking history</p>
                </div>
                <div class="col-lg-4 col-md-6">
                </div>

            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history mr-2"></i>
                                History
                                <small class="text-muted ml-2">Showing <?php echo count($location_dump) ?> entries</small>
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 tabledump">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>Coordinates</th>
                                        <th>Accuracy</th>
                                        <th>Speed/Bearing</th>
                                        <th>Provider</th>
                                        <th>Timestamp</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($location_dump)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="empty-state">
                                                    <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                                                    <h4>No locations recorded</h4>
                                                    <p class="text-muted">Location data will appear here</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($location_dump as $loc): ?>
                                            <?php
                                            $time = isset($loc['location_time']) ? date('Y-m-d H:i:s', $loc['location_time'] / 1000) : 'N/A';
                                            $statusClass = ($loc['status'] === 'success') ? 'badge-success' : 'badge-warning';
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="font-weight-bold"><?php echo $loc['latitude']; ?>, <?php echo $loc['longitude']; ?></div>
                                                    <small class="text-muted">Lat, Long</small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info"><?php echo $loc['accuracy']; ?>m</span>
                                                </td>
                                                <td>
                                                    <div><i class="fas fa-tachometer-alt mr-1"></i> <?php echo $loc['speed']; ?> m/s</div>
                                                    <small class="text-muted"><i class="fas fa-compass mr-1"></i> <?php echo $loc['bearing']; ?>°</small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-secondary"><?php echo strtoupper($loc['provider']); ?></span>
                                                </td>
                                                <td>
                                                    <div><?php echo $time; ?></div>
                                                    <small class="text-muted">Extracted: <?php echo date('Y-m-d H:i:s', $loc['extracted_at'] / 1000); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $statusClass; ?>"><?php echo strtoupper($loc['status']); ?></span>
                                                </td>
                                                <td>
                                                    <a href="https://www.google.com/maps?q=<?php echo $loc['latitude']; ?>,<?php echo $loc['longitude']; ?>" target="_blank" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-external-link-alt"></i> Maps
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="float-right">
                                <?php if (isset($pager)): ?>
                                    <?= $pager->links('default', 'bootstrap5_full') ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    .avatar-circle-sm { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .empty-state { padding: 3rem 1rem; text-align: center; }
    .empty-state i { opacity: 0.5; }
    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
</style>

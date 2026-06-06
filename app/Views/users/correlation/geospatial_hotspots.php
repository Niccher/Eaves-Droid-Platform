<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-map-marked-alt text-success mr-2"></i> Base of Operations Analysis</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('analysis') ?>">Analysis</a></li>
                        <li class="breadcrumb-item active">Hotspots</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-7">
                    <div class="card card-outline card-success shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title">Top Identified Bases</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($clusters as $i => $c): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge badge-success mr-2">#<?= ($i+1) ?></span>
                                            <b><?= esc($c['label']) ?></b><br>
                                            <small class="text-muted"><?= $c['lat'] ?>, <?= $c['lng'] ?></small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-light border"><?= $c['pings'] ?> Sessions</span><br>
                                            <small class="text-xs">Last: <?= date('M j, H:i', $c['last_seen'] / 1000) ?></small>
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
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Heuristic Classification</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-light border">
                                <h5><i class="fas fa-home mr-2"></i> Primary Base</h5>
                                <p class="mb-0 small">Identified by maximum stay duration and pings during late-night hours (00:00 - 06:00).</p>
                            </div>
                            <div class="alert alert-light border">
                                <h5><i class="fas fa-briefcase mr-2"></i> Frequent Hotspot</h5>
                                <p class="mb-0 small">Secondary clusters with pings concentrated during business hours (09:00 - 17:00).</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

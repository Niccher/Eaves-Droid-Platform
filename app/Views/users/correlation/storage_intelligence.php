<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-hdd text-info mr-2"></i> Media & Storage Forensics</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('analysis') ?>">Analysis</a></li>
                        <li class="breadcrumb-item active">Storage</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Storage Health Summary -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-info"><i class="fas fa-hdd"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Analyzed</span>
                            <span class="info-box-number"><?= number_format($storage['total_size'] / (1024 * 1024), 2) ?> MB</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-success"><i class="fas fa-file-image"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Files</span>
                            <span class="info-box-number"><?= number_format($storage['count']) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Space Hogs (>50MB)</span>
                            <span class="info-box-number"><?= count($storage['large_hogs']) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-primary"><i class="fab fa-whatsapp"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">WhatsApp Media</span>
                            <span class="info-box-number"><?= number_format($storage['by_source']['WhatsApp'] / (1024 * 1024), 2) ?> MB</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Storage Overview & Aging -->
            <div class="row">
                <!-- Source Distribution -->
                <div class="col-md-6">
                    <div class="card card-outline card-info shadow-sm h-100">
                        <div class="card-header border-0 bg-light">
                            <h3 class="card-title"><i class="fas fa-chart-pie text-info mr-2"></i> Source Distribution</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="storageChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Media Age -->
                <div class="col-md-6">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-header border-0 bg-light">
                            <h3 class="card-title"><i class="fas fa-calendar-alt text-secondary mr-2"></i> Content Aging</h3>
                        </div>
                        <div class="card-body d-flex align-items-center justify-content-center">
                            <div class="row w-100">
                                <?php foreach ($storage['by_age'] as $age => $count): ?>
                                <div class="col-md-4 col-sm-12 text-center mb-3">
                                    <input type="text" class="knob" value="<?= $count ?>" data-width="100" data-height="100" data-fgColor="<?= $age == 'Recent' ? '#28a745' : ($age == 'Mid (6mo-1yr)' ? '#ffc107' : '#dc3545') ?>" readonly>
                                    <div class="knob-label mt-2 font-weight-bold"><?= $age ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Files -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-header border-0 bg-light">
                            <h3 class="card-title text-danger"><i class="fas fa-weight-hanging mr-2"></i> Largest Space Hogs</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-valign-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>File Name</th>
                                            <th>File Path</th>
                                            <th class="text-right">Size (MB)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $displayFiles = !empty($storage['large_hogs']) ? $storage['large_hogs'] : $storage['top_files'];
                                        foreach (array_slice($displayFiles, 0, 10) as $file): ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-file-alt text-muted mr-2"></i>
                                                <b><?= esc($file['name']) ?></b>
                                            </td>
                                            <td><small class="text-muted"><?= esc($file['path']) ?></small></td>
                                            <td class="text-right">
                                                <span class="badge badge-warning p-2" style="font-size: 0.9em;">
                                                    <?= number_format($file['size'] / (1024 * 1024), 2) ?> MB
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-Knob/1.2.13/jquery.knob.min.js"></script>
<script>
$(function () {
    $(".knob").knob();

    var ctx = document.getElementById('storageChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($storage['by_source'])) ?>,
            datasets: [{
                data: <?= json_encode(array_values($storage['by_source'])) ?>,
                backgroundColor: ['#25d366', '#ffc107', '#007bff', '#6c757d']
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>

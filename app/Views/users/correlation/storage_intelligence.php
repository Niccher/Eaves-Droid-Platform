<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-hdd text-info mr-2"></i> Media & Storage Forensics</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-outline-info btn-sm" href="<?= base_url('analysis') ?>"><i class="fas fa-arrow-left mr-1"></i> Back to Analysis</a>
                </div>
            </div>
        </div>
    </section>

<?php if (isset($ml_insight) && !empty($ml_insight['insights'])): ?>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-info shadow-sm">
                    <div class="card-header">
                        <?php $_eng = (new \App\Models\AnomaliesModel())->getDefaultEngine(); $_engLabel = match($_eng){'python'=>'Python Engine','both'=>'Hybrid Engine',default=>'PHP Engine'}; ?>
                        <h3 class="card-title"><i class="fas fa-brain mr-2"></i> <?= $_engLabel ?> Intelligence</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <span class="badge badge-info p-2"><?= $ml_insight['algorithm'] ?></span>
                                <p class="text-muted mt-2 mb-0"><small><?= $ml_insight['data_source'] ?></small></p>
                            </div>
                            <div class="col-md-8">
                                <p><?= $ml_insight['description'] ?></p>
                                <ul class="mb-0">
                                    <?php foreach ($ml_insight['insights'] as $insight): ?>
                                    <li><?= $insight ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
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
                            <span class="info-box-text">Total FilesController</span>
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

            <!-- Top FilesController -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-header border-0 bg-light">
                            <h3 class="card-title text-danger"><i class="fas fa-weight-hanging mr-2"></i> Largest Space Hogs
                                <small class="text-muted ml-2">Showing <?= count($storage['display_files'] ?? []) ?> of <?= $total ?></small>
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-valign-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>File</th>
                                            <th>Type</th>
                                            <th class="text-right">Size</th>
                                            <th>Last Modified</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $displayFiles = $storage['display_files'] ?? [];
                                        $typeLabels = [
                                            'jpg' => 'Image', 'jpeg' => 'Image', 'png' => 'Image', 'gif' => 'Image',
                                            'bmp' => 'Image', 'webp' => 'Image', 'svg' => 'Image',
                                            'mp4' => 'Video', 'avi' => 'Video', 'mkv' => 'Video', 'mov' => 'Video',
                                            'wmv' => 'Video', 'flv' => 'Video', '3gp' => 'Video',
                                            'mp3' => 'Audio', 'wav' => 'Audio', 'aac' => 'Audio', 'flac' => 'Audio',
                                            'ogg' => 'Audio', 'wma' => 'Audio', 'm4a' => 'Audio',
                                            'pdf' => 'PDF', 'doc' => 'Word', 'docx' => 'Word', 'xls' => 'Excel',
                                            'xlsx' => 'Excel', 'ppt' => 'PowerPoint', 'pptx' => 'PowerPoint',
                                            'txt' => 'Text', 'csv' => 'CSV',
                                            'apk' => 'App', 'zip' => 'Archive', 'rar' => 'Archive', '7z' => 'Archive',
                                            'tar' => 'Archive', 'gz' => 'Archive', 'bz2' => 'Archive',
                                            'html' => 'Code', 'php' => 'Code', 'js' => 'Code', 'css' => 'Code',
                                            'xml' => 'Code', 'json' => 'Code', 'sql' => 'Code',
                                        ];
                                        $typeColors = [
                                            'Image' => 'info', 'Video' => 'danger', 'Audio' => 'success',
                                            'PDF' => 'secondary', 'Word' => 'primary', 'Excel' => 'success',
                                            'PowerPoint' => 'warning', 'Text' => 'light', 'CSV' => 'info',
                                            'App' => 'dark', 'Archive' => 'warning', 'Code' => 'secondary',
                                        ];
                                        foreach ($displayFiles as $file):
                                            $ext = strtolower($file['extension'] ?? '');
                                            $label = $typeLabels[$ext] ?? strtoupper($ext) ?: 'Other';
                                            $color = $typeColors[$label] ?? 'secondary';
                                        ?>
                                        <tr>
                                            <td>
                                                <b><?= esc($file['name']) ?></b><br>
                                                <i><small class="text-muted"><?= esc($file['path']) ?></small></i>
                                            </td>
                                            <td><span class="badge badge-<?= $color ?>"><?= $label ?></span></td>
                                            <td class="text-right">
                                                <span class="badge badge-warning p-2" style="font-size: 0.9em;">
                                                    <?= $file['formatted_size'] ?: number_format($file['size'] / (1024 * 1024), 2) . ' MB' ?>
                                                </span>
                                            </td>
                                            <td><small class="text-muted"><?= $file['formatted_date'] ?: 'N/A' ?></small></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php if (isset($pager_links)): ?>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="entry-info">
                                        Showing <?= (($currentPage-1)*$perPage+1) ?> to <?= min($currentPage*$perPage, $total) ?> of <?= $total ?> entries
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="float-right">
                                        <?= $pager_links ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
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

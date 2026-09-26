<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header pt-3 pb-2">
        <div class="container-fluid">
            <div class="row align-items-center mb-2">
                <div class="col-sm-6">
                    <h1 class="h3 mb-0 text-dark font-weight-bold">
                        <i class="fas fa-hdd text-info mr-2"></i> Media &amp; Storage Forensics
                    </h1>
                    <p class="text-muted mb-0 small">Large file hog detection, hidden media directory auditing, and storage allocation breakdown.</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-outline-secondary btn-sm shadow-sm" href="<?= $back_url ?? base_url('analysis') ?>"><i class="fas fa-arrow-left mr-1"></i> <?= esc($back_label ?? 'Back to Analysis') ?></a>
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
                    <div class="rounded-circle p-3 mr-3 shadow" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                        <i class="fas fa-database fa-lg text-white"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 font-weight-bold text-white"><?= $_engLabel ?> Media &amp; Storage Forensics Synthesis</h4>
                        <small class="text-light opacity-75">Automated large file profiling, mime-type distribution, &amp; hidden directory audits</small>
                    </div>
                </div>
                <div>
                    <span class="badge badge-pill badge-info px-3 py-2 shadow-sm" style="font-size: 0.85rem;">
                        <i class="fas fa-microchip mr-1"></i> Algorithm: <?= esc($ml_insight['algorithm'] ?? 'Storage Tree Profiler') ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-4 col-md-5 mb-3 mb-md-0 border-right-md border-sky pr-md-4">
                        <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                            <h6 class="text-info font-weight-bold mb-2" style="color: #38bdf8;"><i class="fas fa-database mr-2"></i> What Is Happening</h6>
                            <p class="mb-0 text-light opacity-90" style="font-size: 0.9rem; line-height: 1.5;">
                                <?= esc($ml_insight['description'] ?? 'Indexes device file trees, identifies storage hogs consuming excessive disk space, isolates hidden media files, and categorizes storage by file format.') ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-7 pl-md-4">
                        <h6 class="text-warning font-weight-bold mb-2"><i class="fas fa-lightbulb mr-2"></i> Storage Forensics Findings</h6>
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

    <?php
    $formatBytes = function($bytes, $precision = 1) {
        $bytes = (float)$bytes;
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $pow = floor(log($bytes) / log(1024));
        $pow = max(0, min($pow, count($units) - 1));
        $bytes /= pow(1024, $pow);
        return number_format($bytes, $precision) . ' ' . $units[$pow];
    };
    ?>
    <section class="content">
        <div class="container-fluid">
            <!-- Storage Health Summary -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-info"><i class="fas fa-hdd"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Analyzed</span>
                            <span class="info-box-number"><?= $formatBytes($storage['total_size'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-success"><i class="fas fa-file-image"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Files Analyzed</span>
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
                            <span class="info-box-number"><?= $formatBytes($storage['by_source']['WhatsApp'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Storage Overview & Aging -->
            <div class="row">
                <!-- Source Distribution -->
                <div class="col-md-6">
                    <div class="card card-outline card-info shadow-sm h-100">
                        <div class="card-header border-0 bg-transparent">
                            <h3 class="card-title font-weight-bold"><i class="fas fa-chart-pie text-info mr-2"></i> Source Distribution</h3>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-7">
                                    <canvas id="storageChart" style="min-height: 200px; height: 200px; max-height: 200px; max-width: 100%;"></canvas>
                                </div>
                                <div class="col-md-5">
                                    <div class="list-group list-group-flush">
                                        <?php 
                                        $sourceColors = [
                                            'WhatsApp' => '#25d366', 
                                            'Camera/DCIM' => '#ffc107', 
                                            'Downloads' => '#007bff', 
                                            'Screenshots' => '#17a2b8', 
                                            'Documents' => '#6f42c1', 
                                            'APKs' => '#e83e8c', 
                                            'Other' => '#6c757d'
                                        ];
                                        $totalBytes = max(1, array_sum($storage['by_source']));
                                        foreach ($storage['by_source'] as $src => $bytes): 
                                            $pct = round(($bytes / $totalBytes) * 100, 1);
                                            $dotColor = $sourceColors[$src] ?? '#17a2b8';
                                        ?>
                                        <div class="list-group-item bg-transparent px-0 py-2 border-0">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span><i class="fas fa-circle mr-2" style="color: <?= $dotColor ?>;"></i> <?= esc($src) ?></span>
                                                <span class="font-weight-bold text-dark"><?= $formatBytes($bytes) ?> (<?= $pct ?>%)</span>
                                            </div>
                                            <div class="progress mt-1" style="height: 4px;">
                                                <div class="progress-bar" style="width: <?= $pct ?>%; background-color: <?= $dotColor ?>;"></div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Media Age -->
                <div class="col-md-6">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-header border-0 bg-transparent">
                            <h3 class="card-title font-weight-bold"><i class="fas fa-history text-secondary mr-2"></i> Content Aging &amp; Lifecycle Analysis</h3>
                        </div>
                        <div class="card-body d-flex align-items-center">
                            <div class="row w-100">
                                <?php 
                                $ageConfig = [
                                    'Recent' => ['icon' => 'fa-bolt', 'color' => 'success', 'desc' => 'Modified < 6 Months'],
                                    'Mid (6mo-1yr)' => ['icon' => 'fa-clock', 'color' => 'warning', 'desc' => 'Modified 6mo - 1 Year'],
                                    'Legacy (>1yr)' => ['icon' => 'fa-archive', 'color' => 'danger', 'desc' => 'Modified > 1 Year']
                                ];
                                foreach ($storage['by_age'] as $age => $count): 
                                    $cfg = $ageConfig[$age] ?? ['icon' => 'fa-folder', 'color' => 'info', 'desc' => 'Storage Category'];
                                ?>
                                <div class="col-md-4 col-sm-12 text-center mb-3">
                                    <div class="p-3 rounded border shadow-sm h-100 d-flex flex-column align-items-center justify-content-center bg-light">
                                        <div class="rounded-circle bg-<?= $cfg['color'] ?> text-white mb-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                                            <i class="fas <?= $cfg['icon'] ?> fa-lg"></i>
                                        </div>
                                        <h4 class="font-weight-bold text-<?= $cfg['color'] ?> mb-0"><?= number_format($count) ?></h4>
                                        <span class="font-weight-bold text-dark small mt-1"><?= $age ?></span>
                                        <small class="text-muted text-xs"><?= $cfg['desc'] ?></small>
                                    </div>
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

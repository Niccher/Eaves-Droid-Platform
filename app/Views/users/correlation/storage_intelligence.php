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
            <!-- Storage Overview -->
            <div class="row">
                <div class="col-md-5">
                    <div class="card card-outline card-info shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title">Source Distribution</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="storageChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="card shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title">Top 10 Largest Files (Space Hogs)</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($storage['top_files'] as $file): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <i class="fas fa-file-alt text-muted mr-2"></i>
                                            <b><?= $file['name'] ?></b><br>
                                            <small class="text-muted"><?= substr($file['path'], 0, 50) ?>...</small>
                                        </div>
                                        <span class="badge badge-warning p-2" style="height: fit-content;">
                                            <?= number_format($file['size'] / (1024 * 1024), 2) ?> MB
                                        </span>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Media Age -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card card-outline card-secondary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title">Content Aging (File Counts)</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($storage['by_age'] as $age => $count): ?>
                                <div class="col-md-4 text-center">
                                    <input type="text" class="knob" value="<?= $count ?>" data-width="90" data-height="90" data-fgColor="<?= $age == 'Recent' ? '#28a745' : '#6c757d' ?>" readonly>
                                    <div class="knob-label"><?= $age ?></div>
                                </div>
                                <?php endforeach; ?>
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

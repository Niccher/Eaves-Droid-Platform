<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-walking text-secondary mr-2"></i> Lifestyle & Mobility Profile</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('analysis') ?>">Analysis</a></li>
                        <li class="breadcrumb-item active">Lifestyle</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Activity Breakdown -->
                <div class="col-md-7">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Activity Breakdown</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="activityChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Screen Time Balance -->
                <div class="col-md-5">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">Screen Time vs Interaction</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="screenChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Insights -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-lightbulb text-warning mr-2"></i> Mobility Insights</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-4 border-right">
                                    <div class="description-block">
                                        <h5 class="description-header"><?= $mobility['STILL'] ?> pings</h5>
                                        <span class="description-text">SEDENTARY PERIODS</span>
                                    </div>
                                </div>
                                <div class="col-sm-4 border-right">
                                    <div class="description-block">
                                        <h5 class="description-header"><?= $mobility['WALKING'] + $mobility['RUNNING'] ?> pings</h5>
                                        <span class="description-text">ACTIVE PERIODS</span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="description-block">
                                        <h5 class="description-header"><?= $mobility['IN_VEHICLE'] ?> pings</h5>
                                        <span class="description-text">TRANSIT PERIODS</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function () {
    var ctx = document.getElementById('activityChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Still', 'Walking', 'Vehicle', 'Bicycle', 'Running', 'Tilting', 'Unknown'],
            datasets: [{
                data: [
                    <?= $mobility['STILL'] ?>,
                    <?= $mobility['WALKING'] ?>,
                    <?= $mobility['IN_VEHICLE'] ?>,
                    <?= $mobility['ON_BICYCLE'] ?>,
                    <?= $mobility['RUNNING'] ?>,
                    <?= $mobility['TILTING'] ?>,
                    <?= $mobility['UNKNOWN'] ?>
                ],
                backgroundColor: ['#6c757d', '#28a745', '#007bff', '#ffc107', '#dc3545', '#17a2b8', '#e9ecef']
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });

    var sCtx = document.getElementById('screenChart').getContext('2d');
    new Chart(sCtx, {
        type: 'pie',
        data: {
            labels: ['Screen On', 'Screen Off'],
            datasets: [{
                data: [<?= $mobility['screen_on'] ?>, <?= $mobility['screen_off'] ?>],
                backgroundColor: ['#20c997', '#adb5bd']
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true
        }
    });
});
</script>

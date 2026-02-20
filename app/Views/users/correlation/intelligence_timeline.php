<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-stream text-dark mr-2"></i> Universal Intelligence Timeline</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('analysis') ?>">Analysis</a></li>
                        <li class="breadcrumb-item active">Timeline</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <!-- The time line -->
                    <div class="timeline">
                        <?php 
                        $current_date = '';
                        foreach ($timeline as $event): 
                            $date = date('F j, Y', $event['time'] / 1000);
                            if ($date != $current_date):
                                $current_date = $date;
                        ?>
                        <!-- timeline time label -->
                        <div class="time-label">
                            <span class="bg-red"><?= $date ?></span>
                        </div>
                        <!-- /.timeline-label -->
                        <?php endif; ?>

                        <!-- timeline item -->
                        <div>
                            <i class="<?= $event['icon'] ?> <?= $event['color'] ?>"></i>
                            <div class="timeline-item shadow-sm">
                                <span class="time"><i class="fas fa-clock"></i> <?= date('H:i', $event['time'] / 1000) ?></span>
                                <h3 class="timeline-header"><b><?= $event['title'] ?></b></h3>
                                <div class="timeline-body">
                                    <?= $event['body'] ?>
                                </div>
                            </div>
                        </div>
                        <!-- END timeline item -->
                        <?php endforeach; ?>

                        <?php if (empty($timeline)): ?>
                        <div class="alert alert-info border-0 shadow-sm">
                            <i class="fas fa-info-circle mr-2"></i> No events found in the timeline yet.
                        </div>
                        <?php endif; ?>

                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

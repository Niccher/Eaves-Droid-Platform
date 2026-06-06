<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-smile text-warning mr-2"></i> Sentiment & Relationship Health</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('analysis') ?>">Analysis</a></li>
                        <li class="breadcrumb-item active">Sentiment</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Sentiment Overview -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-warning shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title">Top Contact Sentiment Profile</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-valign-middle">
                                    <thead>
                                        <tr>
                                            <th>Contact Address</th>
                                            <th>Sentiment Score</th>
                                            <th>Emotional Tone</th>
                                            <th>Confidence</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sentiment as $addr => $data): ?>
                                        <tr>
                                            <td>
                                                <b><?= $data['name'] ?></b><br>
                                                <small class="text-muted"><?= $addr ?></small>
                                            </td>
                                            <td>
                                                <?php 
                                                $score = ($data['positive'] - $data['negative']) / max(1, $data['positive'] + $data['negative']);
                                                $width = round((($score + 1) / 2) * 100);
                                                ?>
                                                <div class="progress progress-xs">
                                                    <div class="progress-bar <?= $score > 0 ? 'bg-success' : ($score < 0 ? 'bg-danger' : 'bg-warning') ?>" style="width: <?= $width ?>%"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($score > 0.3): ?>
                                                    <span class="badge badge-success"><i class="fas fa-heart mr-1"></i> Highly Positive</span>
                                                <?php elseif ($score < -0.3): ?>
                                                    <span class="badge badge-danger"><i class="fas fa-angry mr-1"></i> Highly Negative</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning"><i class="fas fa-meh mr-1"></i> Neutral/Mixed</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= $data['total'] ?> pings analyzed</small>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($sentiment)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center p-4">
                                                <i class="fas fa-brain text-muted fa-2x mb-2"></i>
                                                <p>Insufficient message data to generate sentiment profiles for contacts.</p>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
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

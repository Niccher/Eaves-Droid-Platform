<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header pt-3 pb-2">
        <div class="container-fluid">
            <div class="row align-items-center mb-2">
                <div class="col-sm-6">
                    <h1 class="h3 mb-0 text-dark font-weight-bold">
                        <i class="fas fa-smile-beam text-purple mr-2" style="color: #a855f7;"></i> Sentiment &amp; Relationship Health Profiler
                    </h1>
                    <p class="text-muted mb-0 small">Natural language sentiment scoring across incoming/outgoing SMS messages and communication tone tracking.</p>
                </div>
                <div class="col-sm-6 text-right">
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
                    <div class="rounded-circle p-3 mr-3 shadow" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%);">
                        <i class="fas fa-heartbeat fa-lg text-white"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 font-weight-bold text-white"><?= $_engLabel ?> Sentiment &amp; Tone Analysis Synthesis</h4>
                        <small class="text-light opacity-75">Automated NLP sentiment classification, relationship tone tracking, &amp; hostility scoring</small>
                    </div>
                </div>
                <div>
                    <span class="badge badge-pill badge-warning px-3 py-2 shadow-sm" style="font-size: 0.85rem;">
                        <i class="fas fa-microchip mr-1"></i> Algorithm: <?= esc($ml_insight['algorithm'] ?? 'VADER / Lexicon NLP') ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-4 col-md-5 mb-3 mb-md-0 border-right-md border-purple pr-md-4">
                        <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                            <h6 class="text-warning font-weight-bold mb-2"><i class="fas fa-database mr-2"></i> What Is Happening</h6>
                            <p class="mb-0 text-light opacity-90" style="font-size: 0.9rem; line-height: 1.5;">
                                <?= esc($ml_insight['description'] ?? 'Evaluates conversation transcripts using natural language processing lexicon dictionaries to score sentiment polarities (positive, neutral, negative) and flag communication friction.') ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-7 pl-md-4">
                        <h6 class="text-warning font-weight-bold mb-2"><i class="fas fa-lightbulb mr-2"></i> Sentiment Profiler Findings</h6>
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
            <!-- Sentiment Overview -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-warning shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                Top Contact Sentiment Profile
                                <small class="text-muted ml-2">Showing <?= count($sentiment) ?> of <?= $total ?></small>
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-striped table-valign-middle">
                                    <thead>
                                        <tr>
                                            <th>Contact Name / Number</th>
                                            <th>Sentiment Score</th>
                                            <th>Emotional Tone</th>
                                            <th>Confidence</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sentiment as $addr => $data): ?>
                                        <tr>
                                            <td>
                                                <div><b><?= esc($data['name']) ?></b></div>
                                                <small class="text-muted font-italic"><?= esc($data['raw_address'] ?? $addr) ?></small>
                                            </td>
                                            <td>
                                                <?php 
                                                $totalCount = max(1, $data['positive'] + $data['negative']);
                                                $score = ($data['positive'] - $data['negative']) / $totalCount;
                                                $scoreFormatted = ($score > 0 ? '+' : '') . number_format($score, 2);
                                                $width = round((($score + 1) / 2) * 100);
                                                $scoreColor = $score > 0.1 ? 'text-success' : ($score < -0.1 ? 'text-danger' : 'text-warning');
                                                ?>
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="font-weight-bold <?= $scoreColor ?>"><?= $scoreFormatted ?></span>
                                                    <small class="text-muted font-weight-bold"><?= $width ?>%</small>
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar <?= $score > 0.1 ? 'bg-success' : ($score < -0.1 ? 'bg-danger' : 'bg-warning') ?>" style="width: <?= $width ?>%"></div>
                                                </div>
                                                <small class="text-muted d-block mt-1"><?= $data['positive'] ?> Pos &bull; <?= $data['negative'] ?> Neg</small>
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


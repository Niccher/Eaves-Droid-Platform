<!-- Step 1 – Anomaly Detection Wizard: Info & Engine Selection -->
<div class="content-wrapper">

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="fas fa-search-dollar text-warning mr-2"></i>
                        Anomaly Detection
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0 m-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>"><i class="fas fa-home mr-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('analysis') ?>">Intelligence</a></li>
                        <li class="breadcrumb-item active">Anomalies</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <!-- No Previous Results Callout -->
            <div class="callout callout-info bg-white shadow-sm border-left-info mb-3">
                <div class="d-flex align-items-start">
                    <div class="mr-3">
                        <i class="fas fa-chart-bar fa-3x text-info"></i>
                    </div>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1"><i class="fas fa-info-circle mr-1"></i>No Previous Analysis Results</h5>
                        <p class="mb-2">You haven't run an anomaly detection analysis yet. Select an engine and algorithms below, then click <strong>Next: Algorithms</strong> to get started.</p>
                        <a href="<?= base_url('analysis/anomalies/algorithms') ?>" class="btn btn-info btn-sm font-weight-bold shadow-sm">
                            <i class="fas fa-play mr-1"></i> Start New Analysis
                        </a>
                    </div>
                </div>
            </div>

            <!-- Combined Info & Warning Callout at Top -->
            <div class="callout callout-warning bg-light shadow-sm border-left-warning mb-4">
                <h5 class="text-warning font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i>About This Tool &amp; Accuracy Disclaimer</h5>
                <p class="mb-2">
                    This module analyses uploaded Android device datasets (SMS logs, contacts, calls, geographic locations, app metadata, and background system metrics) to isolate abnormal behavioral patterns.
                </p>
                <hr class="my-2" style="border-top: 1px solid rgba(0,0,0,0.08);">
                <p class="mb-0 text-muted small">
                    <i class="fas fa-info-circle mr-1 text-info"></i> <strong>Disclaimer:</strong> Detections are based on mathematical heuristics and statistical machine-learning models. Results are purely indicative. They are subject to false positives or false negatives, and should be manually verified before taking operational actions.
                </p>
            </div>

            <div class="row">

                <!-- LEFT: Information Block -->
                <div class="col-lg-6 col-md-12 mb-4">
                    <!-- Data category list -->
                    <div class="card card-outline card-info shadow-sm h-100 mb-0">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-database mr-1 text-info"></i> Analyzed Android Scope
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex align-items-center">
                                    <span class="info-box-icon bg-danger mr-3 rounded" style="width:36px;height:36px;min-width:36px;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-sms text-white" style="font-size:.85rem;"></i>
                                    </span>
                                    <div>
                                        <strong>SMS Messages</strong>
                                        <small class="d-block text-muted">Frequency spikes, time anomalies, BERT semantic phishing classifiers</small>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <span class="info-box-icon bg-success mr-3 rounded" style="width:36px;height:36px;min-width:36px;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-address-book text-white" style="font-size:.85rem;"></i>
                                    </span>
                                    <div>
                                        <strong>Contacts &amp; Graph Relations</strong>
                                        <small class="d-block text-muted">Sudden additions, duplicates, graph convolutional network relationships</small>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <span class="info-box-icon bg-warning mr-3 rounded" style="width:36px;height:36px;min-width:36px;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-phone text-white" style="font-size:.85rem;"></i>
                                    </span>
                                    <div>
                                        <strong>Call Logs</strong>
                                        <small class="d-block text-muted">Short-burst scouting calls, nocturnal logs, multidimensional Isolation Forests</small>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <span class="info-box-icon bg-primary mr-3 rounded" style="width:36px;height:36px;min-width:36px;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-map-marker-alt text-white" style="font-size:.85rem;"></i>
                                    </span>
                                    <div>
                                        <strong>Locations &amp; Paths</strong>
                                        <small class="d-block text-muted">Geo-fence breaches, velocity calculation, DBSCAN trajectory clusters</small>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <span class="info-box-icon bg-secondary mr-3 rounded" style="width:36px;height:36px;min-width:36px;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-folder-open text-white" style="font-size:.85rem;"></i>
                                    </span>
                                    <div>
                                        <strong>Files &amp; Applications</strong>
                                        <small class="d-block text-muted">Manifest permission profiling, shannon entropy checks, autoencoder anomaly flags</small>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Engine Selection Form -->
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card card-primary card-outline shadow-sm h-100 mb-0">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-cogs mr-1 text-primary"></i> Target Engine Selection
                            </h3>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <p class="text-muted mb-3">
                                    Choose the backend engine that will process your device data.
                                    The selection determines which algorithms are available in Step 2.
                                </p>

                                <?php foreach ($engines as $engine): ?>
                                <!-- Engine Option -->
                                <div class="card card-outline shadow-none mb-3" id="card-engine-<?= $engine['id'] ?>"
                                     style="border:2px solid <?= $engine['default'] ? '#007bff' : '#dee2e6' ?>;cursor:pointer; transition: border-color 0.2s;"
                                     onclick="selectEngine('<?= $engine['id'] ?>')">
                                    <div class="card-body py-3">
                                        <div class="custom-control custom-radio">
                                            <input class="custom-control-input" type="radio"
                                                   id="engine_<?= $engine['id'] ?>" name="engine" value="<?= $engine['id'] ?>"
                                                   <?= $engine['default'] ? 'checked' : '' ?>>
                                            <label class="custom-control-label w-100" for="engine_<?= $engine['id'] ?>"
                                                   style="cursor:pointer;">
                                                <div class="d-flex align-items-start">
                                                    <i class="<?= $engine['icon'] ?> fa-2x <?= $engine['icon_color'] ?> mr-3 mt-1"></i>
                                                    <div>
                                                        <strong class="d-block text-dark"><?= $engine['label'] ?></strong>
                                                        <small class="text-muted"><?= $engine['subtitle'] ?></small>
                                                        <div class="mt-2">
                                                            <?php foreach ($engine['badges'] as $badge): ?>
                                                                <span class="badge badge-<?= $badge['color'] ?> mr-1">
                                                                    <i class="<?= $badge['icon'] ?> mr-1"></i><?= $badge['text'] ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                        <p class="mb-0 mt-2 small text-muted">
                                                            <?= $engine['description'] ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Buttons Section -->
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <!-- Default settings skip option -->
                                <a href="<?= base_url('analysis/anomalies/run?skip=1') ?>"
                                   class="btn btn-outline-success font-weight-bold"
                                   id="btn-skip-results">
                                    <i class="fas fa-magic mr-1"></i> Use Default &amp; Skip to Results
                                </a>

                                <a href="<?= base_url('analysis/anomalies/algorithms') ?>"
                                   class="btn btn-primary font-weight-bold shadow-sm"
                                   id="btn-next-engine">
                                    Next: Algorithms
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>

            </div><!-- /.row -->

        </div>
    </section>

</div><!-- /.content-wrapper -->

<script>
function selectEngine(value) {
    // Update radio checked status
    const radio = document.getElementById('engine_' + value);
    if (radio) radio.checked = true;

    // Reset card borders
    document.querySelectorAll('[id^="card-engine-"]').forEach(function(card) {
        card.style.borderColor = '#dee2e6';
    });

    // Highlight selected card
    const selectedCard = document.getElementById('card-engine-' + value);
    if (selectedCard) selectedCard.style.borderColor = '#007bff';

    // Update the Next link with the chosen engine parameter
    const nextBtn = document.getElementById('btn-next-engine');
    if (nextBtn) {
        nextBtn.href = "<?= base_url('analysis/anomalies/algorithms') ?>?engine=" + value;
    }
}

// Bind load events
document.addEventListener('DOMContentLoaded', function() {
    // Check initial active value
    const checkedRadio = document.querySelector('input[name="engine"]:checked');
    if (checkedRadio) {
        selectEngine(checkedRadio.value);
    }

    // Handle manual radio adjustments
    document.querySelectorAll('input[name="engine"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            selectEngine(this.value);
        });
    });
});
</script>

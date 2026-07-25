<!-- Step 2 – Anomaly Detection Wizard: Algorithm Selection -->
<div class="content-wrapper">

    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="fas fa-sliders-h text-primary mr-2"></i>
                        Algorithm Selection
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0 m-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>"><i class="fas fa-home mr-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('analysis') ?>">Intelligence</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('analysis/anomalies') ?>">Anomaly Detection</a></li>
                        <li class="breadcrumb-item active">Algorithms</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Instruction callout -->
            <?php 
                $engine = service('request')->getGet('engine') ?? 'php';
                $engineLabel = ($engine === 'python') ? 'Python (Docker Container)' : 'PHP (In-Process PHP-ML)';
            ?>
            <div class="callout callout-primary bg-light shadow-sm">
                <h5><i class="fas fa-hand-pointer mr-2 text-primary"></i>Configuration Settings</h5>
                <p class="mb-0">
                    Selected Engine: <strong><span class="badge badge-info"><?= esc($engineLabel) ?></span></strong>. 
                    Select <strong>multiple algorithms</strong> in each category to execute them concurrently. Each category has at least one pre-selected recommended default.
                    <?php if ($engine === 'php'): ?>
                        <br><span class="text-danger small mt-1 d-block"><i class="fas fa-exclamation-circle"></i> Note: Algorithms that are <strong>Python-only</strong> have been disabled because you selected the PHP engine. To enable them, go back and choose the Python-based Engine.</span>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Algorithm accordion form -->
            <form action="<?= base_url('analysis/anomalies/results') ?>" method="post" id="form-algorithms">
                <!-- CSRF Token (CI4 default) -->
                <?= csrf_field() ?>

                <div id="accordion-algorithms">

                    <?php foreach ($categories as $catKey => $cat): ?>
                    <div class="card card-<?= $cat['color'] ?> card-outline shadow-sm mb-2">
                        <div class="card-header" id="heading-<?= $catKey ?>" style="cursor:pointer;"
                             data-toggle="collapse"
                             data-target="#collapse-<?= $catKey ?>"
                             aria-expanded="true"
                             aria-controls="collapse-<?= $catKey ?>">
                            <h3 class="card-title mb-0 d-flex align-items-center w-100">
                                <i class="<?= $cat['icon'] ?> text-<?= $cat['color'] ?> mr-2"></i>
                                <span class="font-weight-bold text-dark"><?= esc($cat['label']) ?></span>
                                <span class="badge badge-<?= $cat['color'] ?> ml-2">
                                    <?= count($cat['algorithms']) ?> available
                                </span>
                                <span class="ml-auto text-muted small" style="font-weight:400;">
                                    <i class="fas fa-chevron-down"></i>
                                </span>
                            </h3>
                        </div>

                        <div id="collapse-<?= $catKey ?>" class="collapse show"
                             aria-labelledby="heading-<?= $catKey ?>">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width:50px;" class="text-center">Active</th>
                                                <th>Algorithm</th>
                                                <th>Required Engine</th>
                                                <th>Description</th>
                                                <th>Strengths</th>
                                                <th>Weaknesses</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($cat['algorithms'] as $alg): 
                                                $isPythonOnly = ($alg['compat'] === 'python');
                                                $disabled = ($engine === 'php' && $isPythonOnly);
                                                $rowClass = $disabled ? 'table-secondary text-muted' : ($alg['default'] ? 'table-primary' : '');
                                            ?>
                                            <tr class="<?= $rowClass ?>"
                                                style="<?= $disabled ? 'cursor: not-allowed; opacity: 0.75;' : 'cursor:pointer;' ?>"
                                                <?php if (!$disabled): ?>
                                                onclick="toggleCheckbox('<?= esc($alg['id']) ?>', event)"
                                                <?php endif; ?>>
                                                <td class="text-center align-middle">
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input checkbox-alg"
                                                               type="checkbox"
                                                               id="<?= esc($alg['id']) ?>"
                                                               name="algs[<?= esc($catKey) ?>][]"
                                                               value="<?= esc($alg['id']) ?>"
                                                               <?= $alg['default'] && !$disabled ? 'checked' : '' ?>
                                                               <?= $disabled ? 'disabled' : '' ?>>
                                                        <label class="custom-control-label"
                                                               for="<?= esc($alg['id']) ?>"
                                                               onclick="event.stopPropagation();"></label>
                                                    </div>
                                                </td>
                                                <td class="align-middle font-weight-bold text-dark">
                                                    <?= esc($alg['name']) ?>
                                                    <?php if ($alg['default'] && !$disabled): ?>
                                                        <span class="badge badge-success ml-1">Default</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="align-middle" style="min-width:130px;">
                                                    <span class="d-block mb-1">
                                                        <?php if ($isPythonOnly): ?>
                                                            <span class="badge badge-danger" style="opacity:0.6;"><i class="fab fa-php mr-1"></i>PHP ✗</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-success"><i class="fab fa-php mr-1"></i>PHP ✓</span>
                                                        <?php endif; ?>
                                                    </span>
                                                    <span class="d-block">
                                                        <span class="badge badge-success"><i class="fab fa-python mr-1"></i>Python ✓</span>
                                                    </span>
                                                </td>
                                                <td class="align-middle small"><?= esc($alg['description']) ?></td>
                                                <td class="align-middle small">
                                                    <span class="text-success font-weight-bold">
                                                        <i class="fas fa-plus-circle mr-1"></i><?= esc($alg['strengths']) ?>
                                                    </span>
                                                </td>
                                                <td class="align-middle small">
                                                    <span class="text-danger font-weight-bold">
                                                        <i class="fas fa-minus-circle mr-1"></i><?= esc($alg['weaknesses']) ?>
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
                    <?php endforeach; ?>

                </div><!-- /#accordion-algorithms -->

                <!-- Analysis scope -->
                <div class="card card-outline card-secondary shadow-sm mt-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-search-plus mr-2"></i>Analysis Scope
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-radio">
                                    <input class="custom-control-input" type="radio" id="scope-full"
                                           name="scope" value="full" checked>
                                    <label class="custom-control-label font-weight-bold" for="scope-full">
                                        Analyze All Entries
                                    </label>
                                    <small class="d-block text-muted ml-4 mt-1">
                                        Scan every record in the database — recommended for first-time or comprehensive analysis.
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-radio">
                                    <input class="custom-control-input" type="radio" id="scope-incremental"
                                           name="scope" value="incremental"
                                           <?= ($engine !== 'python') ? 'disabled' : '' ?>>
                                    <label class="custom-control-label font-weight-bold" for="scope-incremental">
                                        Analyze New Entries Only
                                    </label>
                                    <small class="d-block text-muted ml-4 mt-1">
                                        Only inspect records added since the last analysis.
                                        <?php if ($engine !== 'python'): ?>
                                            <span class="text-warning d-block mt-1">
                                                <i class="fas fa-info-circle"></i> Requires Python engine.
                                            </span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="row mt-4 mb-2">
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <a href="<?= base_url('analysis/anomalies') ?>"
                           class="btn btn-outline-secondary font-weight-bold">
                            <i class="fas fa-arrow-left mr-1"></i> Back: Change Engine
                        </a>
                        <button type="button" onclick="submitForm()" class="btn btn-primary btn-lg shadow-sm font-weight-bold">
                            <i class="fas fa-play-circle mr-2"></i> Start Detection
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </section>

</div><!-- /.content-wrapper -->

<script>
function toggleCheckbox(id, event) {
    // Stop event bubbling to prevent double toggles
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'LABEL') {
        return;
    }

    const cb = document.getElementById(id);
    if (cb && !cb.disabled) {
        cb.checked = !cb.checked;
        const row = cb.closest('tr');
        if (row) {
            if (cb.checked) {
                row.classList.add('table-primary');
            } else {
                row.classList.remove('table-primary');
            }
        }
    }
}

function submitForm() {
    // Verify at least one algorithm is selected
    const checked = document.querySelectorAll('.checkbox-alg:checked');
    if (checked.length === 0) {
        Swal.fire({
            title: 'No Algorithms Selected!',
            text: 'Please select at least one algorithm to run the detection.',
            icon: 'warning',
            confirmButtonColor: '#007bff'
        });
        return;
    }

    // Submit form (it is visual, moves to results page)
    document.getElementById('form-algorithms').submit();
}

document.addEventListener('DOMContentLoaded', function () {
    // Bind direct checkbox status changes
    document.querySelectorAll('.checkbox-alg').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const row = this.closest('tr');
            if (row) {
                if (this.checked) {
                    row.classList.add('table-primary');
                } else {
                    row.classList.remove('table-primary');
                }
            }
        });
    });
});
</script>

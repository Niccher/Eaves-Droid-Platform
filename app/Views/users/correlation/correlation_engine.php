<?= $this->extend('users/layouts/main') ?>

<?= $this->section('content') ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center">
                        <h1 class="h2 mb-0">
                            <i class="fas fa-project-diagram text-primary mr-2"></i>
                            Correlation Engine
                        </h1>
                        <div class="ml-3">
                            <span class="badge badge-primary p-2">
                                <i class="fas fa-link mr-1"></i> Platinum
                            </span>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">Cross-category relationship graph linking contacts, calls, SMS, location, and app usage</p>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="float-right mt-2">
                        <a href="<?= base_url('analysis') ?>" class="btn btn-info ml-2">
                            <i class="fas fa-microchip mr-1"></i> Advanced Analysis
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (empty($graph['nodes'])): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            No correlation data available yet. Run the anomaly scanner to generate relationship data.
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-circle-nodes mr-2"></i>
                                    Relationship Graph
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-light">
                                        <?= count($graph['nodes']) ?> contacts &middot; <?= count($graph['edges']) ?> links
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="correlation-graph" style="width: 100%; height: 500px;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-list-ol mr-2"></i>
                                    Top Links
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Contact A</th>
                                                <th>Contact B</th>
                                                <th>Weight</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($topLinks as $i => $link): ?>
                                                <tr>
                                                    <td><?= $i + 1 ?></td>
                                                    <td>
                                                        <span class="badge badge-light">
                                                            <?= esc($link['source']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-light">
                                                            <?= esc($link['target']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?= $link['weight'] >= 10 ? 'danger' : ($link['weight'] >= 5 ? 'warning' : 'info') ?>">
                                                            <?= $link['weight'] ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($clusters)): ?>
                            <div class="card card-warning mt-3">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-object-group mr-2"></i>
                                        Contact Clusters
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($clusters as $cluster): ?>
                                        <div class="mb-2">
                                            <span class="badge badge-warning mr-1">
                                                <?= $cluster['size'] ?> contacts
                                            </span>
                                            <small class="text-muted">
                                                <?= implode(', ', array_column($cluster['contacts'], 'label')) ?>
                                            </small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php if (!empty($graph['nodes'])): ?>
<script src="https://unpkg.com/vis-network@9.1.9/dist/vis-network.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/vis-network@9.1.9/dist/vis-network.min.css">
<script>
document.addEventListener('DOMContentLoaded', function () {
    const graphData = <?= $graph_json ?>;

    const nodes = new vis.DataSet(graphData.nodes.map(n => ({
        id: n.id,
        label: n.label,
        title: n.label + ' (SMS: ' + n.sms_count + ', Calls: ' + n.call_count + ', Score: ' + n.score + ')',
        value: Math.max(10, Math.min(50, n.score / 5)),
        color: { background: '#007bff', border: '#0056b3' },
        font: { size: 12 },
    })));

    const edges = new vis.DataSet(graphData.edges.map(e => ({
        from: e.source,
        to: e.target,
        value: Math.max(1, Math.min(10, e.weight / 5)),
        color: { color: '#6c757d', highlight: '#007bff' },
        smooth: { type: 'continuous' },
        title: 'SMS: ' + e.sms + ' | Calls: ' + e.calls + ' | Co-loc: ' + e.cooccurrence + ' | Co-activity: ' + e.coactivity + ' | Apps: ' + e.app_usage,
    })));

    const container = document.getElementById('correlation-graph');
    const data = { nodes: nodes, edges: edges };
    const options = {
        physics: {
            enabled: true,
            forceAtlas2Based: {
                gravitationalConstant: -26,
                centralGravity: 0.005,
                springLength: 230,
                springConstant: 0.18,
            },
            solver: 'forceAtlas2Based',
            timestep: 0.35,
            stabilization: { iterations: 150 },
        },
        edges: {
            smooth: { type: 'continuous' },
        },
        nodes: {
            shape: 'dot',
            font: { size: 14 },
        },
        interaction: {
            hover: true,
            tooltipDelay: 200,
        },
    };

    const network = new vis.Network(container, data, options);
});
</script>
<?php endif; ?>
<?= $this->endSection() ?>
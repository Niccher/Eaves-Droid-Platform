<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header pt-3 pb-2">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center">
                        <h1 class="h3 mb-0 text-dark font-weight-bold">
                            <i class="fas fa-project-diagram text-indigo mr-2" style="color: #6366f1;"></i>
                            Correlation Engine
                        </h1>
                        <div class="ml-3">
                            <span class="badge badge-primary px-2 py-1">
                                <i class="fas fa-link mr-1"></i> Platinum
                            </span>
                        </div>
                    </div>
                    <p class="text-muted mb-0 small">Cross-category relationship graph linking contacts, calls, SMS, location, and app usage</p>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="float-right">
                        <a href="<?= base_url('analysis') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Analysis
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Explanation Callout Banner (Secondary Theme) -->
    <section class="content mb-3">
        <div class="container-fluid">
            <div class="card bg-secondary text-white shadow-sm border-0" style="border-radius: 8px;">
                <div class="card-header border-0 bg-transparent pt-3 px-4 pb-0 d-flex align-items-center justify-content-between flex-wrap">
                    <div class="d-flex align-items-center mb-2 mb-md-0">
                        <div class="rounded-circle p-3 mr-3 shadow" style="width: 46px; height: 46px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                            <i class="fas fa-project-diagram fa-lg text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold text-white">Correlation Engine &amp; Relationship Guide</h5>
                            <small class="text-light opacity-75">Multi-category relationship scoring linking phone hub, call frequency, SMS volume &amp; co-occurrences</small>
                        </div>
                    </div>
                    <div>
                        <span class="badge badge-pill badge-warning px-3 py-2 shadow-sm" style="font-size: 0.85rem;">
                            <i class="fas fa-brain mr-1"></i> Algorithm: Co-occurrence Matrix
                        </span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0 border-right-md border-indigo pr-md-4">
                            <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                                <h6 class="text-warning font-weight-bold mb-2"><i class="fas fa-mobile-alt mr-2"></i> What Is Happening</h6>
                                <p class="mb-0 text-light opacity-90" style="font-size: 0.88rem; line-height: 1.5;">
                                    The AI engine links your primary device (<strong>me_device</strong>) with surrounding contacts (<strong>c0, c1, ...</strong>) into an interconnected web to measure relationship closeness.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0 border-right-md border-indigo pr-md-4">
                            <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                                <h6 class="text-warning font-weight-bold mb-2"><i class="fas fa-weight-hanging mr-2"></i> What "Weight" Means</h6>
                                <p class="mb-0 text-light opacity-90" style="font-size: 0.88rem; line-height: 1.5;">
                                    <strong>Weight (e.g., 425)</strong> = Total relationship score. Calculated from call length (5x weight), SMS frequency (1x weight), and location co-occurrences.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                                <h6 class="text-warning font-weight-bold mb-2"><i class="fas fa-object-group mr-2"></i> What "Contact Clusters" Means</h6>
                                <p class="mb-0 text-light opacity-90" style="font-size: 0.88rem; line-height: 1.5;">
                                    <strong>Clusters (e.g. 43 contacts)</strong> = Closely knit social or work groups detected interacting around similar times or sharing mutual communication density.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php 
                $topLinks = $topLinks ?? $top_links ?? []; 
                $clusters = $clusters ?? [];
                $nodeLabelMap = [];
                if (!empty($graph['nodes'])) {
                    foreach ($graph['nodes'] as $n) {
                        $nodeLabelMap[$n['id']] = $n['label'] ?? $n['id'];
                    }
                }
            ?>
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
                    <!-- Left Column (col-lg-8): Relationship Graph + Contact Clusters directly below it -->
                    <div class="col-lg-8">
                        <!-- Card 1: Relationship Graph -->
                        <div class="card card-primary shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold">
                                    <i class="fas fa-circle-nodes mr-2"></i>
                                    Relationship Graph
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-light">
                                        <?= count($graph['nodes']) - 1 ?> contacts &middot; <?= count($graph['edges']) ?> links
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="correlation-graph" style="width: 100%; height: 500px;"></div>
                            </div>
                        </div>

                        <!-- Card 2: Contact Clusters (Directly Below Relationship Graph Card) -->
                        <?php if (!empty($clusters)): ?>
                        <div class="card card-outline card-warning shadow-sm">
                            <div class="card-header border-0 pb-2">
                                <h3 class="card-title text-dark font-weight-bold">
                                    <i class="fas fa-object-group text-warning mr-2"></i>
                                    Detected Contact Clusters &amp; Interconnected Circles
                                    <small class="text-muted d-block small mt-1">Groups of contacts exhibiting high co-occurrence or temporal communication density</small>
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped table-valign-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Cluster ID</th>
                                                <th>Cluster Size</th>
                                                <th>Group Members / Associated Contacts</th>
                                                <th>Interconnection Level</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($clusters as $ci => $cluster): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge badge-secondary px-2 py-1">Cluster #<?= $ci + 1 ?></span>
                                                    </td>
                                                    <td>
                                                        <b class="text-dark"><?= $cluster['size'] ?> Contacts</b>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-wrap">
                                                            <?php foreach ($cluster['contacts'] as $member): ?>
                                                                <span class="badge badge-light border mr-1 mb-1 p-2">
                                                                    <i class="fas fa-user-circle text-info mr-1"></i>
                                                                    <b><?= esc($member['label']) ?></b>
                                                                    <small class="text-muted ml-1">(<?= esc($member['number']) ?>)</small>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-success"><i class="fas fa-link mr-1"></i> Highly Interconnected</span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Right Column (col-lg-4): Top Links -->
                    <div class="col-lg-4">
                        <div class="card card-info shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold">
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
                                                        <span class="badge badge-light border">
                                                            <?= esc($nodeLabelMap[$link['source']] ?? $link['source']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-primary border">
                                                            <?= esc($nodeLabelMap[$link['target']] ?? $link['target']) ?>
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
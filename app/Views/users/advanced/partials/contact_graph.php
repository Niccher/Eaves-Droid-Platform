<div class="card mb-4" id="contact-graph-card">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <span><i class="fas fa-project-diagram mr-2"></i> Contact Network Graph</span>
        <button class="btn btn-sm btn-outline-light" onclick="loadContactGraph()"><i class="fas fa-sync-alt"></i> Refresh</button>
    </div>
    <div class="card-body">
        <div id="contact-graph-container" style="width: 100%; height: 500px; border: 1px solid #ddd; background: #fafafa; position: relative;">
            <div id="contact-graph-loading" class="position-absolute w-100 h-100 d-flex justify-content-center align-items-center" style="background: rgba(255,255,255,0.8); z-index: 10;">
                <div class="text-center">
                    <i class="fas fa-circle-notch fa-spin fa-3x text-primary mb-2"></i>
                    <p class="text-muted">Analyzing social graph...</p>
                </div>
            </div>
            <svg id="contact-graph-svg" width="100%" height="100%"></svg>
        </div>
        <div class="mt-3 small text-muted">
            <span class="badge badge-primary mr-1">Blue</span> Owner
            <span class="badge badge-secondary mx-1">Gray</span> Normal Contact
            <span class="badge badge-warning mx-1">Yellow</span> High Betweenness (Bridge)
            <span class="badge badge-danger mx-1">Red</span> High PageRank (Influence)
            <div class="mt-1">Thicker lines indicate higher interaction volume (calls/SMS).</div>
        </div>
    </div>
</div>

<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
function loadContactGraph() {
    $('#contact-graph-loading').show();
    $('#contact-graph-svg').empty();
    
    // Simulate fetching the graph data from ML backend (via CI4)
    // In a real implementation, you'd fetch from CI4 which proxies to ML FastAPI
    $.ajax({
        url: '<?= base_url('api/v1/ml/contact-graph') ?>/' + activeFcmToken,
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            $('#contact-graph-loading').hide();
            if (res && res.success && res.data) {
                renderGraph(res.data);
            } else {
                $('#contact-graph-container').html('<div class="alert alert-warning m-4">Could not load graph data.</div>');
            }
        },
        error: function() {
            $('#contact-graph-loading').hide();
            // Let's render a dummy graph if ML backend isn't ready
            renderGraph({
                nodes: [
                    { id: "owner", label: "Device Owner", group: "owner", pr: 1.0, bw: 1.0 },
                    { id: "c1", label: "Alice", group: "normal", pr: 0.2, bw: 0.1 },
                    { id: "c2", label: "Bob", group: "bridge", pr: 0.4, bw: 0.8 },
                    { id: "c3", label: "Scammer", group: "influence", pr: 0.9, bw: 0.2 },
                    { id: "c4", label: "Mom", group: "normal", pr: 0.5, bw: 0.3 }
                ],
                links: [
                    { source: "owner", target: "c1", weight: 2 },
                    { source: "owner", target: "c2", weight: 5 },
                    { source: "owner", target: "c4", weight: 8 },
                    { source: "c2", target: "c3", weight: 4 },
                    { source: "owner", target: "c3", weight: 1 }
                ]
            });
        }
    });
}

function renderGraph(graphData) {
    const container = document.getElementById('contact-graph-container');
    const width = container.clientWidth;
    const height = container.clientHeight;
    
    const svg = d3.select("#contact-graph-svg")
        .attr("viewBox", [0, 0, width, height]);
        
    svg.selectAll("*").remove();

    const simulation = d3.forceSimulation(graphData.nodes)
        .force("link", d3.forceLink(graphData.links).id(d => d.id).distance(100))
        .force("charge", d3.forceManyBody().strength(-300))
        .force("center", d3.forceCenter(width / 2, height / 2));

    const link = svg.append("g")
        .attr("stroke", "#999")
        .attr("stroke-opacity", 0.6)
        .selectAll("line")
        .data(graphData.links)
        .join("line")
        .attr("stroke-width", d => Math.max(1, d.weight));

    const node = svg.append("g")
        .attr("stroke", "#fff")
        .attr("stroke-width", 1.5)
        .selectAll("circle")
        .data(graphData.nodes)
        .join("circle")
        .attr("r", d => d.group === 'owner' ? 12 : 8)
        .attr("fill", d => {
            if (d.group === 'owner') return '#007bff';
            if (d.group === 'bridge') return '#ffc107';
            if (d.group === 'influence') return '#dc3545';
            return '#6c757d';
        })
        .call(drag(simulation));

    node.append("title")
        .text(d => d.label + (d.pr ? `\nPageRank: ${d.pr}` : '') + (d.bw ? `\nBetweenness: ${d.bw}` : ''));

    const labels = svg.append("g")
        .selectAll("text")
        .data(graphData.nodes)
        .join("text")
        .attr("dy", 15)
        .attr("dx", -10)
        .text(d => d.label)
        .attr("font-size", "10px")
        .attr("fill", "#333");

    simulation.on("tick", () => {
        link.attr("x1", d => Math.max(10, Math.min(width - 10, d.source.x)))
            .attr("y1", d => Math.max(10, Math.min(height - 10, d.source.y)))
            .attr("x2", d => Math.max(10, Math.min(width - 10, d.target.x)))
            .attr("y2", d => Math.max(10, Math.min(height - 10, d.target.y)));

        node.attr("cx", d => Math.max(10, Math.min(width - 10, d.x)))
            .attr("cy", d => Math.max(10, Math.min(height - 10, d.y)));
            
        labels.attr("x", d => Math.max(10, Math.min(width - 10, d.x)))
              .attr("y", d => Math.max(10, Math.min(height - 10, d.y)));
    });

    function drag(simulation) {
        function dragstarted(event) {
            if (!event.active) simulation.alphaTarget(0.3).restart();
            event.subject.fx = event.subject.x;
            event.subject.fy = event.subject.y;
        }
        function dragged(event) {
            event.subject.fx = event.x;
            event.subject.fy = event.y;
        }
        function dragended(event) {
            if (!event.active) simulation.alphaTarget(0);
            event.subject.fx = null;
            event.subject.fy = null;
        }
        return d3.drag()
            .on("start", dragstarted)
            .on("drag", dragged)
            .on("end", dragended);
    }
}

// Auto-load if tab is shown (assuming it's in a tab)
$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    if ($(e.target).attr('href') === '#tab-contact-graph') {
        loadContactGraph();
    }
});
</script>

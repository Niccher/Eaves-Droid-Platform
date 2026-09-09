<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">
                        <i class="fas fa-server text-info mr-2"></i>Infrastructure &amp; Container Telemetry
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('superadmin/home') ?>">Super Admin</a></li>
                        <li class="breadcrumb-item active">Infrastructure</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <!-- Control & Polling Rate Toolbar -->
            <div class="card card-outline card-primary shadow-sm mb-4">
                <div class="card-body py-2 px-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <div class="d-flex align-items-center my-1">
                            <span class="text-muted font-weight-bold mr-2"><i class="fas fa-clock mr-1"></i> Polling Interval:</span>
                            <div class="btn-group btn-group-toggle" data-toggle="buttons" id="pollIntervalGroup">
                                <label class="btn btn-sm btn-outline-secondary" onclick="setPollInterval(0)" id="btnPoll0">
                                    <input type="radio" name="pollRate" value="0"> Manual
                                </label>
                                <label class="btn btn-sm btn-outline-secondary" onclick="setPollInterval(10)" id="btnPoll10">
                                    <input type="radio" name="pollRate" value="10"> 10s
                                </label>
                                <label class="btn btn-sm btn-outline-secondary" onclick="setPollInterval(20)" id="btnPoll20">
                                    <input type="radio" name="pollRate" value="20"> 20s
                                </label>
                                <label class="btn btn-sm btn-outline-secondary active" onclick="setPollInterval(30)" id="btnPoll30">
                                    <input type="radio" name="pollRate" value="30" checked> 30s
                                </label>
                                <label class="btn btn-sm btn-outline-secondary" onclick="setPollInterval(40)" id="btnPoll40">
                                    <input type="radio" name="pollRate" value="40"> 40s
                                </label>
                                <label class="btn btn-sm btn-outline-secondary" onclick="setPollInterval(60)" id="btnPoll60">
                                    <input type="radio" name="pollRate" value="60"> 60s
                                </label>
                            </div>
                            <span class="badge badge-light border ml-3 px-2 py-1 font-weight-normal text-muted" id="pollCountdownBadge">
                                <i class="fas fa-spinner fa-pulse mr-1 text-info" id="pollSpinner"></i>
                                <span id="pollCountdownText">Refreshing in 30s...</span>
                            </span>
                        </div>

                        <div class="d-flex align-items-center my-1">
                            <button type="button" class="btn btn-sm btn-info mr-2 shadow-sm" onclick="fetchTelemetry(true)">
                                <i class="fas fa-sync-alt mr-1" id="btnRefreshIcon"></i> Poll Now
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary mr-2 shadow-sm" onclick="openBenchmarkModal()">
                                <i class="fas fa-tachometer-alt mr-1"></i> Latency Waterfall
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary mr-2 shadow-sm" onclick="openTableInspectorModal()">
                                <i class="fas fa-database mr-1"></i> Tables Breakdown
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success shadow-sm" onclick="exportTelemetryJson()">
                                <i class="fas fa-file-download mr-1"></i> Export JSON
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TRI-TIER RESOURCE ALLOCATION CARDS -->
            <div class="row">

                <!-- 1. WEBAPP CONTAINER -->
                <div class="col-lg-4 col-md-12 mb-4">
                    <div class="card card-outline card-primary shadow-sm h-100">
                        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                            <h5 class="card-title font-weight-bold mb-0">
                                <i class="fab fa-php text-primary mr-2"></i> WebApp Container
                            </h5>
                            <span class="badge badge-pill badge-success" id="webStatusBadge">Healthy</span>
                        </div>
                        <div class="card-body">
                            <!-- Memory Allocation -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small font-weight-bold mb-1">
                                    <span><i class="fas fa-memory mr-1 text-primary"></i> PHP Memory (Used / Limit)</span>
                                    <span id="webMemoryText">-- MB / -- MB (0%)</span>
                                </div>
                                <div class="progress progress-sm rounded">
                                    <div class="progress-bar bg-primary" role="progressbar" id="webMemoryBar" style="width: 0%"></div>
                                </div>
                                <div class="d-flex justify-content-between text-muted small mt-1">
                                    <span>Peak: <strong id="webMemoryPeak">-- MB</strong></span>
                                    <span>Limit: <strong id="webMemoryLimit">-- MB</strong></span>
                                </div>
                            </div>

                            <!-- System Load & CPU -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small font-weight-bold mb-1">
                                    <span><i class="fas fa-microchip mr-1 text-warning"></i> System Load Average</span>
                                    <span id="webCpuText">Load: --, --, --</span>
                                </div>
                                <div class="progress progress-sm rounded">
                                    <div class="progress-bar bg-warning" role="progressbar" id="webCpuBar" style="width: 0%"></div>
                                </div>
                                <div class="d-flex justify-content-between text-muted small mt-1">
                                    <span>1m: <strong id="webLoad1m">--</strong> | 5m: <strong id="webLoad5m">--</strong> | 15m: <strong id="webLoad15m">--</strong></span>
                                    <span>Est. CPU: <strong id="webCpuPercent">--%</strong></span>
                                </div>
                            </div>

                            <!-- Disk Storage -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small font-weight-bold mb-1">
                                    <span><i class="fas fa-hdd mr-1 text-info"></i> Container Disk Storage</span>
                                    <span id="webDiskText">-- GB / -- GB (0%)</span>
                                </div>
                                <div class="progress progress-sm rounded">
                                    <div class="progress-bar bg-info" role="progressbar" id="webDiskBar" style="width: 0%"></div>
                                </div>
                                <div class="d-flex justify-content-between text-muted small mt-1">
                                    <span>Free: <strong id="webDiskFree">-- GB</strong></span>
                                    <span>Total: <strong id="webDiskTotal">-- GB</strong></span>
                                </div>
                            </div>

                            <div class="border-top pt-2">
                                <div class="row text-center small text-muted">
                                    <div class="col-6 border-right">
                                        <div>Uploads / Loot Footprint</div>
                                        <div class="font-weight-bold text-dark" id="webUploadsSize">-- MB</div>
                                    </div>
                                    <div class="col-6">
                                        <div>PHP Runtime</div>
                                        <div class="font-weight-bold text-dark" id="webPhpVersion">PHP <?= PHP_VERSION ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. MYSQL DATABASE CONTAINER -->
                <div class="col-lg-4 col-md-12 mb-4">
                    <div class="card card-outline card-info shadow-sm h-100">
                        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                            <h5 class="card-title font-weight-bold mb-0">
                                <i class="fas fa-database text-info mr-2"></i> MySQL Container
                            </h5>
                            <div>
                                <span class="badge badge-light border mr-1" id="mysqlLatencyBadge">-- ms</span>
                                <span class="badge badge-pill badge-success" id="mysqlStatusBadge">Connected</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Thread Connections -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small font-weight-bold mb-1">
                                    <span><i class="fas fa-network-wired mr-1 text-info"></i> Active Thread Connections</span>
                                    <span id="mysqlConnectionsText">-- / --</span>
                                </div>
                                <div class="progress progress-sm rounded">
                                    <div class="progress-bar bg-info" role="progressbar" id="mysqlConnectionsBar" style="width: 0%"></div>
                                </div>
                                <div class="d-flex justify-content-between text-muted small mt-1">
                                    <span>Active: <strong id="mysqlThreadsConnected">--</strong></span>
                                    <span>Max Peak: <strong id="mysqlMaxUsed">--</strong></span>
                                    <span>Max Allowed: <strong id="mysqlMaxAllowed">--</strong></span>
                                </div>
                            </div>

                            <!-- InnoDB Buffer Pool Memory -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small font-weight-bold mb-1">
                                    <span><i class="fas fa-layer-group mr-1 text-primary"></i> InnoDB Buffer Pool</span>
                                    <span id="mysqlBufferText">-- MB / -- MB (0%)</span>
                                </div>
                                <div class="progress progress-sm rounded">
                                    <div class="progress-bar bg-primary" role="progressbar" id="mysqlBufferBar" style="width: 0%"></div>
                                </div>
                                <div class="d-flex justify-content-between text-muted small mt-1">
                                    <span>Used: <strong id="mysqlBufferUsed">-- MB</strong></span>
                                    <span>Pool Size: <strong id="mysqlBufferTotal">-- MB</strong></span>
                                </div>
                            </div>

                            <!-- Total Database Footprint -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small font-weight-bold mb-1">
                                    <span><i class="fas fa-table mr-1 text-secondary"></i> Schema Disk Size</span>
                                    <span id="mysqlSizeText">-- MB</span>
                                </div>
                                <div class="progress progress-sm rounded">
                                    <div class="progress-bar bg-secondary" role="progressbar" id="mysqlSizeBar" style="width: 25%"></div>
                                </div>
                                <div class="d-flex justify-content-between text-muted small mt-1">
                                    <span>Total Tables: <strong id="mysqlTableCount">--</strong></span>
                                    <span>Throughput: <strong id="mysqlQps">-- QPS</strong></span>
                                </div>
                            </div>

                            <div class="border-top pt-2">
                                <div class="row text-center small text-muted">
                                    <div class="col-6 border-right">
                                        <div>Server Uptime</div>
                                        <div class="font-weight-bold text-dark" id="mysqlUptime">--</div>
                                    </div>
                                    <div class="col-6">
                                        <div>Database Version</div>
                                        <div class="font-weight-bold text-dark" id="mysqlVersion">--</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. PYTHON ML MICROSERVICE CONTAINER -->
                <div class="col-lg-4 col-md-12 mb-4">
                    <div class="card card-outline card-warning shadow-sm h-100">
                        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                            <h5 class="card-title font-weight-bold mb-0">
                                <i class="fab fa-python text-warning mr-2"></i> Python ML Microservice
                            </h5>
                            <div>
                                <span class="badge badge-light border mr-1" id="pyLatencyBadge">-- ms</span>
                                <span class="badge badge-pill badge-success" id="pyStatusBadge">Healthy</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Process Memory -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small font-weight-bold mb-1">
                                    <span><i class="fas fa-memory mr-1 text-warning"></i> Process RAM (RSS)</span>
                                    <span id="pyMemoryText">-- MB / -- MB</span>
                                </div>
                                <div class="progress progress-sm rounded">
                                    <div class="progress-bar bg-warning" role="progressbar" id="pyMemoryBar" style="width: 0%"></div>
                                </div>
                                <div class="d-flex justify-content-between text-muted small mt-1">
                                    <span>Container RAM: <strong id="pyMemoryUsed">-- MB</strong></span>
                                    <span>System Limit: <strong id="pyMemoryTotal">-- MB</strong></span>
                                </div>
                            </div>

                            <!-- Process CPU Load -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small font-weight-bold mb-1">
                                    <span><i class="fas fa-microchip mr-1 text-danger"></i> Container CPU Utilization</span>
                                    <span id="pyCpuText">--%</span>
                                </div>
                                <div class="progress progress-sm rounded">
                                    <div class="progress-bar bg-danger" role="progressbar" id="pyCpuBar" style="width: 0%"></div>
                                </div>
                                <div class="d-flex justify-content-between text-muted small mt-1">
                                    <span>Active Detectors: <strong id="pyModelsCount">-- Active</strong></span>
                                    <span>Cache Items: <strong id="pyCacheEntries">--</strong></span>
                                </div>
                            </div>

                            <!-- Shared DB Link Verification -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small font-weight-bold mb-1">
                                    <span><i class="fas fa-database mr-1 text-success"></i> MySQL Link from Python</span>
                                    <span id="pyDbStatus">Checking...</span>
                                </div>
                                <div class="progress progress-sm rounded">
                                    <div class="progress-bar bg-success" role="progressbar" id="pyDbBar" style="width: 100%"></div>
                                </div>
                                <div class="d-flex justify-content-between text-muted small mt-1">
                                    <span>DB Latency: <strong id="pyDbLatency">-- ms</strong></span>
                                    <span>Verified Tables: <strong id="pyDbTables">--/10</strong></span>
                                </div>
                            </div>

                            <div class="border-top pt-2">
                                <div class="row text-center small text-muted">
                                    <div class="col-6 border-right">
                                        <div>Failover Engine</div>
                                        <div class="font-weight-bold text-success">PHP-ML Armed</div>
                                    </div>
                                    <div class="col-6">
                                        <div>Container Version</div>
                                        <div class="font-weight-bold text-dark" id="pyVersion">FastAPI v2.5.0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- LIVE TELEMETRY ROLLING CHARTS -->
            <div class="card card-outline card-secondary shadow-sm mb-4">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-chart-line text-primary mr-2"></i>Real-Time Tri-Tier Telemetry History (Rolling 60s)
                    </h5>
                    <div class="card-tools">
                        <span class="badge badge-light border mr-1"><i class="fas fa-circle text-primary mr-1"></i> WebApp</span>
                        <span class="badge badge-light border mr-1"><i class="fas fa-circle text-info mr-1"></i> MySQL</span>
                        <span class="badge badge-light border"><i class="fas fa-circle text-warning mr-1"></i> Python ML</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6 mb-3 mb-lg-0">
                            <h6 class="font-weight-bold text-muted small"><i class="fas fa-microchip mr-1"></i> CPU Load &amp; System Utilization (%)</h6>
                            <div style="height: 220px;">
                                <canvas id="chartCpuHistory"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <h6 class="font-weight-bold text-muted small"><i class="fas fa-tachometer-alt mr-1"></i> Multi-Tier Latency (ms)</h6>
                            <div style="height: 220px;">
                                <canvas id="chartLatencyHistory"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RUNTIME ENVIRONMENT & CONTAINER ARCHITECTURE -->
            <div class="card card-outline card-info shadow-sm mb-4">
                <div class="card-header bg-white py-2">
                    <h5 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-cubes text-info mr-2"></i>Container Architecture &amp; System Runtime Matrix
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:20%;">Component</th>
                                <th style="width:25%;">Container / Environment</th>
                                <th style="width:20%;">Ports / Protocol</th>
                                <th style="width:35%;">Health &amp; Diagnostics</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong><i class="fab fa-php text-primary mr-1"></i> WebApp</strong></td>
                                <td>PHP <?= PHP_VERSION ?> &bull; CodeIgniter 4</td>
                                <td>HTTP 80 / 8080 &bull; HTTPS 443</td>
                                <td><span class="badge badge-success">Active</span> &bull; OPcache: <span id="envOpcache">Enabled</span> &bull; OpenSSL: Enabled</td>
                            </tr>
                            <tr>
                                <td><strong><i class="fas fa-database text-info mr-1"></i> Database</strong></td>
                                <td>MySQL Community 8.0 &bull; InnoDB</td>
                                <td>TCP 3306 &bull; Native Protocol</td>
                                <td><span class="badge badge-success" id="envMysqlBadge">Connected</span> &bull; <span id="envMysqlDetails">10 Core forensic tables loaded</span></td>
                            </tr>
                            <tr>
                                <td><strong><i class="fab fa-python text-warning mr-1"></i> ML Microservice</strong></td>
                                <td>Python 3.12 &bull; FastAPI + scikit-learn</td>
                                <td>HTTP 9070 &bull; REST API</td>
                                <td><span class="badge badge-success" id="envPyBadge">Active</span> &bull; Endpoints: <code>/api/v1/health</code>, <code>/api/v1/analysis-jobs</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- MODAL: LATENCY WATERFALL BENCHMARK -->
<div class="modal fade" id="modalBenchmark" tabindex="-1" role="dialog" aria-labelledby="modalBenchmarkTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-dark text-white py-2">
                <h5 class="modal-title" id="modalBenchmarkTitle"><i class="fas fa-tachometer-alt mr-2 text-primary"></i> Tri-Tier Latency Waterfall Benchmark</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3" id="benchmarkBody">
                <div class="text-center py-4">
                    <button class="btn btn-primary px-4 py-2" onclick="runBenchmark()">
                        <i class="fas fa-play mr-1"></i> Start Latency Benchmark
                    </button>
                    <p class="text-muted small mt-2">Measures precise execution times across Browser &rarr; WebApp &rarr; MySQL DB &rarr; Python ML &rarr; WebApp.</p>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info btn-sm" onclick="runBenchmark()"><i class="fas fa-redo mr-1"></i> Run Again</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: FORENSIC TABLES BREAKDOWN -->
<div class="modal fade" id="modalTableInspector" tabindex="-1" role="dialog" aria-labelledby="modalTableInspectorTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-dark text-white py-2">
                <h5 class="modal-title" id="modalTableInspectorTitle"><i class="fas fa-database mr-2 text-info"></i> Core Forensic Tables Footprint</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-sm table-striped table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Table Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Estimated Rows</th>
                            <th>Disk Size (KB)</th>
                        </tr>
                    </thead>
                    <tbody id="tableInspectorBody">
                        <tr><td colspan="5" class="text-center py-3 text-muted"><i class="fas fa-spinner fa-pulse mr-1"></i> Loading table metrics...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js inclusion (AdminLTE bundled or CDN fallback) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
(function() {
    let currentPollInterval = parseInt(localStorage.getItem('infra_poll_interval') || '30', 10);
    let pollTimer = null;
    let countdownTimer = null;
    let countdownSeconds = currentPollInterval;
    let latestTelemetry = null;

    // Rolling chart historical data buffers (max 12 points)
    const MAX_HISTORY = 12;
    const historyLabels = [];
    const cpuWebAppHistory = [];
    const cpuPythonHistory = [];
    const latencyMysqlHistory = [];
    const latencyPythonHistory = [];

    let chartCpu = null;
    let chartLatency = null;

    function initCharts() {
        const ctxCpu = document.getElementById('chartCpuHistory');
        const ctxLat = document.getElementById('chartLatencyHistory');

        if (ctxCpu && typeof Chart !== 'undefined') {
            chartCpu = new Chart(ctxCpu.getContext('2d'), {
                type: 'line',
                data: {
                    labels: historyLabels,
                    datasets: [
                        {
                            label: 'WebApp CPU / Load (%)',
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            data: cpuWebAppHistory,
                            tension: 0.3,
                            fill: true,
                        },
                        {
                            label: 'Python ML CPU (%)',
                            borderColor: '#ffc107',
                            backgroundColor: 'rgba(255, 193, 7, 0.1)',
                            data: cpuPythonHistory,
                            tension: 0.3,
                            fill: true,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, max: 100 }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }

        if (ctxLat && typeof Chart !== 'undefined') {
            chartLatency = new Chart(ctxLat.getContext('2d'), {
                type: 'line',
                data: {
                    labels: historyLabels,
                    datasets: [
                        {
                            label: 'MySQL Latency (ms)',
                            borderColor: '#17a2b8',
                            backgroundColor: 'rgba(23, 162, 184, 0.1)',
                            data: latencyMysqlHistory,
                            tension: 0.3,
                            fill: true,
                        },
                        {
                            label: 'Python ML Latency (ms)',
                            borderColor: '#fd7e14',
                            backgroundColor: 'rgba(253, 126, 20, 0.1)',
                            data: latencyPythonHistory,
                            tension: 0.3,
                            fill: true,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }
    }

    function updateCharts(timeStr, webCpu, pyCpu, mysqlLat, pyLat) {
        if (historyLabels.length >= MAX_HISTORY) {
            historyLabels.shift();
            cpuWebAppHistory.shift();
            cpuPythonHistory.shift();
            latencyMysqlHistory.shift();
            latencyPythonHistory.shift();
        }
        historyLabels.push(timeStr);
        cpuWebAppHistory.push(webCpu);
        cpuPythonHistory.push(pyCpu);
        latencyMysqlHistory.push(mysqlLat);
        latencyPythonHistory.push(pyLat);

        if (chartCpu) chartCpu.update();
        if (chartLatency) chartLatency.update();
    }

    window.setPollInterval = function(seconds) {
        currentPollInterval = seconds;
        localStorage.setItem('infra_poll_interval', seconds);

        // Update active buttons
        [0, 10, 20, 30, 40, 60].forEach(s => {
            const btn = document.getElementById('btnPoll' + s);
            if (btn) {
                if (s === seconds) btn.classList.add('active');
                else btn.classList.remove('active');
            }
        });

        clearInterval(pollTimer);
        clearInterval(countdownTimer);

        const countdownBadge = document.getElementById('pollCountdownBadge');
        const countdownText = document.getElementById('pollCountdownText');
        const spinner = document.getElementById('pollSpinner');

        if (seconds === 0) {
            if (countdownText) countdownText.textContent = 'Manual (Paused)';
            if (spinner) spinner.style.display = 'none';
            return;
        }

        if (spinner) spinner.style.display = 'inline-block';
        countdownSeconds = seconds;
        if (countdownText) countdownText.textContent = 'Refreshing in ' + countdownSeconds + 's...';

        countdownTimer = setInterval(function() {
            countdownSeconds--;
            if (countdownSeconds <= 0) {
                countdownSeconds = currentPollInterval;
            }
            if (countdownText) countdownText.textContent = 'Refreshing in ' + countdownSeconds + 's...';
        }, 1000);

        pollTimer = setInterval(function() {
            window.fetchTelemetry(false);
        }, seconds * 1000);
    };

    window.fetchTelemetry = function(manual) {
        const icon = document.getElementById('btnRefreshIcon');
        if (manual && icon) icon.classList.add('fa-spin');

        fetch('<?= base_url('superadmin/infrastructure/telemetry') ?>', { credentials: 'same-origin' })
            .then(r => r.json())
            .then(data => {
                if (icon) icon.classList.remove('fa-spin');
                if (!data.success) return;
                latestTelemetry = data;
                renderTelemetry(data);
            })
            .catch(err => {
                if (icon) icon.classList.remove('fa-spin');
            });
    };

    function renderTelemetry(data) {
        const web = data.webapp || {};
        const db = data.mysql || {};
        const py = data.python || {};
        const timeStr = new Date().toLocaleTimeString();

        // 1. WebApp Rendering
        setText('webMemoryText', web.memory_used_mb + ' MB / ' + web.memory_limit_mb + ' MB (' + web.memory_percent + '%)');
        setBar('webMemoryBar', web.memory_percent, web.memory_percent > 85 ? 'bg-danger' : (web.memory_percent > 70 ? 'bg-warning' : 'bg-primary'));
        setText('webMemoryPeak', web.memory_peak_mb + ' MB');
        setText('webMemoryLimit', web.memory_limit_mb + ' MB');

        const coreTxt = web.cpu_cores ? ' (' + web.cpu_cores + ' core' + (web.cpu_cores > 1 ? 's' : '') + ')' : '';
        setText('webCpuText', 'Load: ' + web.load_1m + ', ' + web.load_5m + ', ' + web.load_15m);
        setBar('webCpuBar', web.cpu_percent, web.cpu_percent > 80 ? 'bg-danger' : 'bg-warning');
        setText('webLoad1m', web.load_1m);
        setText('webLoad5m', web.load_5m);
        setText('webLoad15m', web.load_15m);
        setText('webCpuPercent', web.cpu_percent + '%' + coreTxt);

        setText('webDiskText', web.disk_used_gb + ' GB / ' + web.disk_total_gb + ' GB (' + web.disk_percent + '%)');
        setBar('webDiskBar', web.disk_percent, web.disk_percent > 85 ? 'bg-danger' : 'bg-info');
        setText('webDiskFree', web.disk_free_gb + ' GB');
        setText('webDiskTotal', web.disk_total_gb + ' GB');
        setText('webUploadsSize', web.uploads_size_mb + ' MB');
        setText('webPhpVersion', 'PHP ' + (web.php_version || '<?= PHP_VERSION ?>'));

        // 2. MySQL Rendering
        if (db.status === 'healthy') {
            setText('mysqlStatusBadge', 'Connected');
            setClass('mysqlStatusBadge', 'badge badge-pill badge-success');
            setText('mysqlLatencyBadge', db.latency_ms + ' ms');
            setText('mysqlThreadsConnected', db.threads_connected);
            setText('mysqlMaxUsed', db.max_used_connections);
            setText('mysqlMaxAllowed', db.max_connections);
            const connPct = db.max_connections > 0 ? Math.round((db.threads_connected / db.max_connections) * 100) : 1;
            setText('mysqlConnectionsText', db.threads_connected + ' / ' + db.max_connections + ' (' + connPct + '%)');
            setBar('mysqlConnectionsBar', connPct, 'bg-info');

            setText('mysqlBufferText', db.buffer_pool_used_mb + ' MB / ' + db.buffer_pool_total_mb + ' MB (' + db.buffer_pool_percent + '%)');
            setBar('mysqlBufferBar', db.buffer_pool_percent, 'bg-primary');
            setText('mysqlBufferUsed', db.buffer_pool_used_mb + ' MB');
            setText('mysqlBufferTotal', db.buffer_pool_total_mb + ' MB');

            setText('mysqlSizeText', db.total_size_mb + ' MB');
            setText('mysqlTableCount', db.tables_count + ' tables');
            setText('mysqlQps', db.questions_per_sec + ' QPS');
            setText('mysqlUptime', formatUptime(db.uptime_seconds));
            setText('mysqlVersion', db.version || 'MySQL 8.0');
        } else {
            setText('mysqlStatusBadge', 'Disconnected');
            setClass('mysqlStatusBadge', 'badge badge-pill badge-danger');
            setText('mysqlLatencyBadge', 'Offline');
        }

        // 3. Python ML Rendering
        if (py.status === 'healthy') {
            setText('pyStatusBadge', 'Healthy');
            setClass('pyStatusBadge', 'badge badge-pill badge-success');
            setText('pyLatencyBadge', py.latency_ms + ' ms');
            setText('pyMemoryUsed', py.memory_used_mb + ' MB');
            setText('pyMemoryTotal', py.memory_total_mb + ' MB');
            const pyMemPct = py.memory_total_mb > 0 ? Math.round((py.memory_used_mb / py.memory_total_mb) * 100) : 5;
            setText('pyMemoryText', py.memory_used_mb + ' MB (' + pyMemPct + '%)');
            setBar('pyMemoryBar', pyMemPct, 'bg-warning');

            setText('pyCpuText', py.cpu_percent + '% CPU');
            setBar('pyCpuBar', py.cpu_percent, 'bg-danger');
            setText('pyModelsCount', py.models_count + ' Active');
            setText('pyCacheEntries', py.cache_entries);

            if (py.database_status === 'connected') {
                setText('pyDbStatus', 'Connected');
                setClass('pyDbStatus', 'badge badge-success');
                setText('pyDbLatency', py.database_latency_ms + ' ms');
                setText('pyDbTables', py.database_tables_verified + '/' + py.database_total_tables);
            } else {
                setText('pyDbStatus', 'Failed');
                setClass('pyDbStatus', 'badge badge-danger');
            }
            setText('pyVersion', 'FastAPI v' + (py.version || '2.5.0'));
        } else {
            setText('pyStatusBadge', 'Failover Active');
            setClass('pyStatusBadge', 'badge badge-pill badge-warning');
            setText('pyLatencyBadge', 'Fallback');
            setText('pyMemoryText', 'Local In-Process');
            setText('pyCpuText', '0.0% (PHP-ML)');
            setText('pyModelsCount', '15 PHP Models');
            setText('pyDbStatus', 'PHP Direct');
            setClass('pyDbStatus', 'badge badge-info');
        }

        // 4. Update Rolling Charts
        updateCharts(
            timeStr,
            web.cpu_percent || 1.0,
            py.cpu_percent || 0.0,
            db.latency_ms || 0.0,
            py.latency_ms || 0.0
        );

        // 5. Populate Table Inspector Modal if open or cached
        renderTableInspector(db.core_tables || []);
    }

    function renderTableInspector(tables) {
        const tbody = document.getElementById('tableInspectorBody');
        if (!tbody || !tables.length) return;
        let html = '';
        tables.forEach(t => {
            html += '<tr>' +
                '<td><code>' + escHtml(t.table) + '</code></td>' +
                '<td>' + escHtml(t.label) + '</td>' +
                '<td>' + (t.exists ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Pending</span>') + '</td>' +
                '<td><strong>' + Number(t.rows).toLocaleString() + '</strong> rows</td>' +
                '<td>' + Number(t.size_kb).toLocaleString() + ' KB</td>' +
                '</tr>';
        });
        tbody.innerHTML = html;
    }

    // Modal focus management to prevent aria-hidden warnings
    $('#modalBenchmark, #modalTableInspector').on('hide.bs.modal', function() {
        if (document.activeElement && this.contains(document.activeElement)) {
            document.activeElement.blur();
        }
    });

    window.openTableInspectorModal = function() {
        $('#modalTableInspector').modal('show');
    };

    window.openBenchmarkModal = function() {
        $('#modalBenchmark').modal('show');
    };

    window.runBenchmark = function() {
        const body = document.getElementById('benchmarkBody');
        if (!body) return;
        body.innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-pulse fa-3x text-info"></i><p class="mt-3 font-weight-bold">Benchmarking End-to-End Latency Waterfall...</p></div>';

        fetch('<?= base_url('superadmin/infrastructure/benchmark') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                body.innerHTML = '<div class="alert alert-danger">Benchmark failed: ' + (data.message || 'Error') + '</div>';
                return;
            }

            let html = '<div class="alert alert-info py-2 mb-3 d-flex justify-content-between align-items-center">' +
                '<span><i class="fas fa-tachometer-alt mr-1"></i> Total End-to-End Latency: <strong>' + data.total_ms + ' ms</strong></span>' +
                '<span class="badge badge-light">Completed at ' + escHtml(data.timestamp) + '</span>' +
                '</div>';

            html += '<div class="list-group shadow-sm mb-3">';
            data.hops.forEach((h, idx) => {
                const badgeColor = h.status === 'optimal' ? 'badge-success' : (h.status === 'normal' ? 'badge-warning' : 'badge-danger');
                html += '<div class="list-group-item d-flex justify-content-between align-items-center">' +
                    '<div>' +
                    '   <h6 class="mb-0 font-weight-bold">Hop ' + (idx + 1) + ': ' + escHtml(h.name) + '</h6>' +
                    '   <small class="text-muted">' + escHtml(h.description) + '</small>' +
                    '</div>' +
                    '<div class="text-right">' +
                    '   <span class="badge ' + badgeColor + ' p-2 font-weight-bold">' + h.latency_ms + ' ms</span>' +
                    '</div>' +
                    '</div>';
            });
            html += '</div>';

            body.innerHTML = html;
        })
        .catch(err => {
            body.innerHTML = '<div class="alert alert-danger">Benchmark request failed.</div>';
        });
    };

    window.exportTelemetryJson = function() {
        if (!latestTelemetry) {
            alert('No telemetry data collected yet. Please wait for initial poll.');
            return;
        }
        const dataStr = 'data:text/json;charset=utf-8,' + encodeURIComponent(JSON.stringify(latestTelemetry, null, 2));
        const a = document.createElement('a');
        a.setAttribute('href', dataStr);
        a.setAttribute('download', 'infrastructure_telemetry_' + new Date().toISOString().replace(/[:.]/g, '-') + '.json');
        document.body.appendChild(a);
        a.click();
        a.remove();
    };

    function setText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    function setClass(id, cls) {
        const el = document.getElementById(id);
        if (el) el.className = cls;
    }

    function setBar(id, percent, bgClass) {
        const el = document.getElementById(id);
        if (el) {
            el.style.width = Math.min(100, Math.max(0, percent)) + '%';
            if (bgClass) {
                el.className = 'progress-bar ' + bgClass;
            }
        }
    }

    function formatUptime(seconds) {
        const d = Math.floor(seconds / (3600 * 24));
        const h = Math.floor((seconds % (3600 * 24)) / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        return d > 0 ? d + 'd ' + h + 'h' : (h > 0 ? h + 'h ' + m + 'm' : m + 'm');
    }

    function escHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }

    // Initialize charts and initial fetch
    initCharts();
    window.setPollInterval(currentPollInterval);
    window.fetchTelemetry(true);
})();
</script>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 600px; width: 100%; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .location-stat-card { transition: all 0.3s ease; }
        .location-stat-card:hover { transform: translateY(-5px); }
    </style>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-8 col-md-6">
                        <div class="d-flex align-items-center">
                            <h1 class="h2 mb-0">
                                <i class="fas fa-map-marked-alt text-primary mr-2"></i>
                                Location Intelligence
                            </h1>
                            <div class="ml-3">
                                <span class="badge badge-primary border p-2">
                                    Points: <b><?= count($locations) ?></b>
                                </span>
                            </div>
                        </div>
                        <p class="text-muted mt-2 mb-0">Interactive heatmap and movement path analysis from device GPS data</p>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <nav aria-label="breadcrumb" class="float-right mt-2">
                            <ol class="breadcrumb bg-transparent p-0 mb-0">
                                <li class="breadcrumb-item"><a href="<?= base_url('home') ?>"><i class="fas fa-home mr-1"></i>Home</a></li>
                                <li class="breadcrumb-item"><a href="<?= base_url('analysis') ?>">Intelligence</a></li>
                                <li class="breadcrumb-item"><a href="<?= base_url('analysis/advanced') ?>">Advanced</a></li>
                                <li class="breadcrumb-item active">Location</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                
                <div class="row">
                    <!-- Map Column -->
                    <div class="col-md-9">
                        <div class="card card-outline card-primary shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title"><i class="fas fa-globe-africa mr-1"></i> Movement Map</h3>
                                <div class="card-tools">
                                    <div class="btn-group">
                                        <button type="button" id="toggleHeatmap" class="btn btn-sm btn-outline-primary active">Heatmap</button>
                                        <button type="button" id="togglePaths" class="btn btn-sm btn-outline-primary">Paths</button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div id="map"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Side Controls/Stats -->
                    <div class="col-md-3">
                        <!-- Hotspots -->
                        <div class="card card-outline card-info shadow-sm location-stat-card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-thumbtack mr-1"></i> Frequent Hotspots</h3>
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush" id="hotspotList">
                                    <li class="list-group-item text-center py-4 text-muted">Analyzing clusters...</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Time Analysis -->
                        <div class="card card-outline card-secondary shadow-sm mt-4 location-stat-card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-clock mr-1"></i> Activity Profile</h3>
                            </div>
                            <div class="card-body">
                                <p class="text-sm">Identify when the device is most active.</p>
                                <div class="progress mb-2" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 70%" title="Daytime"></div>
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 30%" title="Nighttime"></div>
                                </div>
                                <div class="d-flex justify-content-between text-xs">
                                    <span>Day: 70%</span>
                                    <span>Night: 30%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

    <!-- Leaflet & Heatmap JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
    
    <script>
        $(document).ready(function() {
            const locations = <?= json_encode($locations) ?>;
            
            if (locations.length === 0) {
                $('#map').html('<div class="d-flex h-100 align-items-center justify-content-center bg-light text-muted">No location data available for this user.</div>');
                return;
            }

            // Initialize Map
            const firstLoc = [locations[0].latitude, locations[0].longitude];
            const map = L.map('map').setView(firstLoc, 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Prepare Heatmap Data
            const heatData = locations.map(l => [parseFloat(l.latitude), parseFloat(l.longitude), 0.5]);
            const heatLayer = L.heatLayer(heatData, {radius: 25, blur: 15, maxZoom: 17}).addTo(map);

            // Prepare Path Layer
            const pathPoints = locations.map(l => [parseFloat(l.latitude), parseFloat(l.longitude)]);
            const pathLayer = L.polyline(pathPoints, {color: '#007bff', weight: 3, opacity: 0.6});

            // Toggle Controls
            $('#toggleHeatmap').click(function() {
                $(this).addClass('active');
                $('#togglePaths').removeClass('active');
                map.removeLayer(pathLayer);
                heatLayer.addTo(map);
            });

            $('#togglePaths').click(function() {
                $(this).addClass('active');
                $('#toggleHeatmap').removeClass('active');
                map.removeLayer(heatLayer);
                pathLayer.addTo(map);
                map.fitBounds(pathLayer.getBounds());
            });

            // Simple Clustering for "Hotspots" (Placeholder logic)
            function identifyHotspots() {
                // In a real app, this would use DB-side clustering or more advanced JS
                // Here we just show the most frequent rounding-to-3-decimal points
                const clusters = {};
                locations.forEach(l => {
                    const key = parseFloat(l.latitude).toFixed(3) + ',' + parseFloat(l.longitude).toFixed(3);
                    if (!clusters[key]) clusters[key] = 0;
                    clusters[key]++;
                });

                const sorted = Object.entries(clusters).sort((a,b) => b[1] - a[1]).slice(0, 5);
                const list = $('#hotspotList');
                list.empty();
                
                sorted.forEach((s, i) => {
                    const label = i === 0 ? 'Home (Likely)' : (i === 1 ? 'Office (Likely)' : 'Frequent Point');
                    list.append(`
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-map-marker-alt text-primary mr-2"></i> ${label}</span>
                            <span class="badge badge-primary badge-pill">${s[1]} hits</span>
                        </li>
                    `);
                });
            }

            identifyHotspots();
        });
    </script>

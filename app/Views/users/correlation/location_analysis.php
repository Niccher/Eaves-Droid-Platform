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
        <section class="content-header pt-3 pb-2">
            <div class="container-fluid">
                <div class="row align-items-center mb-2">
                    <div class="col-sm-6">
                        <h1 class="h3 mb-0 text-dark font-weight-bold">
                            <i class="fas fa-map-marked-alt text-primary mr-2"></i> Location Intelligence &amp; Trajectory Profiling
                        </h1>
                        <p class="text-muted mb-0 small">GPS waypoint tracking, travel speed anomaly alerts, and cellular tower fallback triangulation.</p>
                    </div>
                    <div class="col-sm-6 text-right">
                        <form action="<?= base_url('analysis/location') ?>" method="get" class="form-inline justify-content-end">
                            <div class="input-group input-group-sm mr-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-calendar-alt"></i></span>
                                </div>
                                <input type="date" name="start_date" translate="no" class="form-control border-left-0" value="<?= esc($start_date) ?>" placeholder="Start Date">
                            </div>
                            <div class="input-group input-group-sm mr-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-calendar-alt"></i></span>
                                </div>
                                <input type="date" name="end_date" translate="no" class="form-control border-left-0" value="<?= esc($end_date) ?>" placeholder="End Date">
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

<section class="content mb-4">
    <div class="container-fluid">
        <div class="card bg-secondary text-white shadow-sm border-0" style="border-radius: 8px;">
            <div class="card-header border-0 bg-transparent pt-4 px-4 pb-0 d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center mb-2 mb-md-0">
                    <div class="rounded-circle p-3 mr-3 shadow" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                        <i class="fas fa-route fa-lg text-white"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 font-weight-bold text-white">Location Trajectory Synthesis &amp; Speed Profiler</h4>
                        <small class="text-light opacity-75">Automated GPS clustering, movement velocity calculations, &amp; cell tower fallback analysis</small>
                    </div>
                </div>
                <div>
                    <span class="badge badge-pill badge-primary px-3 py-2 shadow-sm" style="font-size: 0.85rem;">
                        <i class="fas fa-map-pin mr-1"></i> Recorded Waypoints: <?= count($locations) ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-4 col-md-5 mb-3 mb-md-0 border-right-md border-primary pr-md-4">
                        <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                            <h6 class="text-primary font-weight-bold mb-2" style="color: #60a5fa;"><i class="fas fa-database mr-2"></i> What Is Happening</h6>
                            <p class="mb-0 text-light opacity-90" style="font-size: 0.9rem; line-height: 1.5;">
                                Real-time processing of device coordinates, calculating Haversine distances to flag impossible velocity changes (>160 km/h) and cross-referencing cell tower IDs when GPS is unavailable.
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-7 pl-md-4">
                        <h6 class="text-warning font-weight-bold mb-2"><i class="fas fa-lightbulb mr-2"></i> Location Intelligence Summary</h6>
                        <ul class="list-unstyled mb-0" style="font-size: 0.9rem;">
                            <li class="mb-2 d-flex align-items-start">
                                <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                                <span><b><?= count($locations) ?> GPS points</b> captured across selected date filter window.</span>
                            </li>
                            <li class="mb-2 d-flex align-items-start">
                                <i class="fas fa-tachometer-alt text-warning mt-1 mr-2"></i>
                                <span><b><?= count($speed_anomalies ?? []) ?> speed anomalies</b> flagged for impossible transit speeds or location spoofing.</span>
                            </li>
                            <li class="mb-2 d-flex align-items-start">
                                <i class="fas fa-broadcast-tower text-info mt-1 mr-2"></i>
                                <span><b><?= count($cell_towers ?? []) ?> cellular tower pings</b> indexed for indoor / underground positioning.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<?= view('analysis/anomaly_alert_card', ['anomaly_alerts' => $anomaly_alerts ?? []]) ?>

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

                <!-- Enriched Location Intelligence Row: Speed Anomalies, Cell Towers, & Geofence Logs -->
                <div class="row mt-4">
                    <!-- Speed Anomalies Table -->
                    <div class="col-md-7">
                        <div class="card card-outline card-danger shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-tachometer-alt text-danger mr-2"></i> Velocity & Speed Anomaly Alerts</h3>
                            </div>
                            <div class="card-body p-0">
                                <?php if (!empty($speed_anomalies)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Timestamp</th>
                                                    <th>Distance</th>
                                                    <th>Est. Speed</th>
                                                    <th>Severity</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($speed_anomalies as $anom): ?>
                                                <tr>
                                                    <td><small><?= esc($anom['timestamp']) ?></small></td>
                                                    <td><?= esc($anom['distance_km']) ?> km (in <?= esc($anom['time_minutes']) ?> mins)</td>
                                                    <td><span class="badge badge-danger"><?= esc($anom['speed_kmh']) ?> km/h</span></td>
                                                    <td><small class="text-danger font-weight-bold"><?= esc($anom['severity']) ?></small></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="p-3 text-center text-muted"><i class="fas fa-check-circle text-success mr-1"></i> No travel speed anomalies detected.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Geofence & Cell Tower Fallbacks -->
                    <div class="col-md-5">
                        <div class="card card-outline card-warning shadow-sm mb-3">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-broadcast-tower text-warning mr-2"></i> Cell Tower Fallback Pings</h3>
                            </div>
                            <div class="card-body p-2" style="max-height: 180px; overflow-y: auto;">
                                <?php if (!empty($cell_towers)): ?>
                                    <ul class="list-group list-group-flush text-sm">
                                        <?php foreach (array_slice($cell_towers, 0, 5) as $cell): ?>
                                        <li class="list-group-item p-1 d-flex justify-content-between">
                                            <span><i class="fas fa-signal text-info mr-1"></i> LAC: <?= esc($cell['lac'] ?? 'N/A') ?>, CellID: <?= esc($cell['cell_id'] ?? 'N/A') ?></span>
                                            <span class="badge badge-light border"><?= esc($cell['signal_strength'] ?? '-75') ?> dBm</span>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <small class="text-muted">No cell tower fallback records captured.</small>
                                <?php endif; ?>
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
            const rawLocations = <?= json_encode($locations) ?>;
            const locations = rawLocations.filter(l => l.latitude != null && l.longitude != null && !isNaN(parseFloat(l.latitude)) && !isNaN(parseFloat(l.longitude)));
            
            if (locations.length === 0) {
                $('#map').html('<div class="d-flex h-100 align-items-center justify-content-center bg-light text-muted">No location data available for this user.</div>');
                return;
            }

            // Initialize Map
            const firstLoc = [parseFloat(locations[0].latitude), parseFloat(locations[0].longitude)];
            const map = L.map('map').setView(firstLoc, 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Prepare Heatmap Data
            const heatData = locations.map(l => [parseFloat(l.latitude), parseFloat(l.longitude), 0.5]);
            const heatLayer = L.heatLayer(heatData, {radius: 25, blur: 15, maxZoom: 17}).addTo(map);

            // Prepare Path Layer with Markers
            const pathPoints = locations.map(l => [parseFloat(l.latitude), parseFloat(l.longitude)]);
            const pathLayer = L.polyline(pathPoints, {color: '#007bff', weight: 3, opacity: 0.6});
            
            const markerGroup = L.layerGroup();
            locations.forEach((l, i) => {
                // Only add markers for every 5th point to avoid clutter, or if it's the start/end
                if (i % 5 === 0 || i === 0 || i === locations.length - 1) {
                    const marker = L.circleMarker([parseFloat(l.latitude), parseFloat(l.longitude)], {
                        radius: 5,
                        fillColor: i === 0 ? "#28a745" : (i === locations.length - 1 ? "#dc3545" : "#007bff"),
                        color: "#fff",
                        weight: 1,
                        opacity: 1,
                        fillOpacity: 0.8
                    }).bindPopup(`
                        <strong>Time:</strong> ${new Date(parseInt(l.location_time)).toLocaleString()}<br>
                        <strong>Accuracy:</strong> ${l.accuracy}m<br>
                        <a href="https://www.google.com/maps?q=${l.latitude},${l.longitude}" target="_blank">View on Google Maps</a>
                    `);
                    markerGroup.addLayer(marker);
                }
            });

            // Toggle Controls
            $('#toggleHeatmap').click(function() {
                $(this).addClass('active');
                $('#togglePaths').removeClass('active');
                map.removeLayer(pathLayer);
                map.removeLayer(markerGroup);
                heatLayer.addTo(map);
            });

            $('#togglePaths').click(function() {
                $(this).addClass('active');
                $('#toggleHeatmap').removeClass('active');
                map.removeLayer(heatLayer);
                pathLayer.addTo(map);
                markerGroup.addTo(map);
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
                    const coords = s[0];
                    list.append(`
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span><i class="fas fa-map-marker-alt text-primary mr-2"></i> ${label}</span>
                                <span class="badge badge-primary badge-pill">${s[1]} hits</span>
                            </div>
                            <div class="text-xs text-muted">
                                <i class="fas fa-external-link-alt mr-1"></i> 
                                <a href="https://www.google.com/maps?q=${coords}" target="_blank">Open in Google Maps</a>
                            </div>
                        </li>
                    `);
                });
            }

            identifyHotspots();
        });
    </script>

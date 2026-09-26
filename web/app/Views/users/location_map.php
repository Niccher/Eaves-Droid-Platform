<div class="content-wrapper">
  <!-- Content Header -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-map-marked-alt text-primary mr-2"></i>Detailed Map View</h1>
          <p class="text-muted mb-0">Interactive Leaflet mapping of device coordinates and tracking paths</p>
        </div>
        <div class="col-sm-6 text-right">
          <div class="btn-group btn-group-sm mb-2 mb-sm-0" role="group">
            <a href="<?= base_url('location') ?>" class="btn btn-outline-primary"><i class="fas fa-route mr-1"></i> Timeline</a>
            <a href="<?= base_url('location/map') ?>" class="btn btn-primary"><i class="fas fa-map-marked-alt mr-1"></i> Map View</a>
            <a href="<?= base_url('activities') ?>" class="btn btn-outline-primary"><i class="fas fa-running mr-1"></i> Activities</a>
          </div>
        </div>
      </div>

      <!-- AdminLTE Info Boxes -->
      <div class="row">
        <div class="col-12 col-sm-6 col-md-4">
          <div class="info-box shadow-sm">
            <span class="info-box-icon bg-primary"><i class="fas fa-map-marker-alt"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Mapped Points</span>
              <span class="info-box-number"><?= number_format($totalLocations) ?></span>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4">
          <div class="info-box shadow-sm">
            <span class="info-box-icon bg-success"><i class="fas fa-satellite-dish"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">GPS Provider Ratio</span>
              <span class="info-box-number">
                <?php
                $gpsCount = 0;
                foreach ($locations as $l) {
                    if (($l['provider'] ?? '') === 'gps') $gpsCount++;
                }
                $ratio = count($locations) > 0 ? round(($gpsCount / count($locations)) * 100) : 0;
                echo $ratio . '% GPS';
                ?>
              </span>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4">
          <div class="info-box shadow-sm">
            <span class="info-box-icon bg-info"><i class="fas fa-bullseye"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Avg. Precision</span>
              <span class="info-box-number">
                <?php
                $sum = 0; $count = 0;
                foreach ($locations as $l) {
                    if (isset($l['accuracy']) && $l['accuracy'] > 0) {
                        $sum += $l['accuracy'];
                        $count++;
                    }
                }
                echo $count > 0 ? round($sum / $count, 1) . ' m' : '—';
                ?>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

  <!-- Main Content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <!-- Interactive Map Column -->
        <div class="col-12">
          <div class="card card-primary card-outline shadow-sm">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-globe-africa mr-2"></i>Map Route Plotter</h3>
            </div>
            <div class="card-body p-0">
              <div id="leafletMap" style="height: 520px; width: 100%;"></div>
            </div>
          </div>
        </div>

        <!-- ── Map Diagnostics & Path Analytics ──────────────────────────── -->
        <?php
        // Pre-compute all stats from $locations array
        $high = $med = $low = 0;
        $speedSum = $speedCount = 0;
        $maxSpeed = 0;
        $providers = [];
        $hotspots = [];

        // GNSS GPS telemetry stats
        $satSum = $satCount = $maxSats = 0;
        $hdopSum = $hdopCount = 0;
        $minHdop = 999.0;
        $maxAlt = -9999.0;
        $vAccSum = $vAccCount = 0;
        $latestGnss = '—';
        $latestFloor = '—';

        foreach ($locations as $l) {
            // Accuracy precision tiers
            $acc = isset($l['accuracy']) ? (float)$l['accuracy'] : null;
            if ($acc !== null) {
                if ($acc <= 15)      $high++;
                elseif ($acc <= 50)  $med++;
                else                 $low++;
            }

            // Speed
            $spd = isset($l['speed_kmh']) && (float)$l['speed_kmh'] > 0 
                ? (float)$l['speed_kmh'] 
                : (isset($l['speed']) ? (float)$l['speed'] * 3.6 : null);

            if ($spd !== null && $spd >= 0) {
                $speedSum += $spd;
                $speedCount++;
                if ($spd > $maxSpeed) $maxSpeed = $spd;
            }

            // Provider footprint
            $prov = strtolower($l['provider'] ?? 'unknown');
            $providers[$prov] = ($providers[$prov] ?? 0) + 1;
            arsort($providers);

            // Hotspots
            $lat4 = $l['latitude']  !== null ? round((float)$l['latitude'],  4) : null;
            $lng4 = $l['longitude'] !== null ? round((float)$l['longitude'], 4) : null;
            if ($lat4 !== null && $lng4 !== null) {
                $key = $lat4 . ',' . $lng4;
                $hotspots[$key] = ($hotspots[$key] ?? 0) + 1;
            }

            // Satellites
            if (isset($l['satellite_count']) && $l['satellite_count'] !== null) {
                $satVal = (int)$l['satellite_count'];
                $satSum += $satVal;
                $satCount++;
                if ($satVal > $maxSats) $maxSats = $satVal;
            }

            // HDOP
            if (isset($l['hdop']) && $l['hdop'] !== null && (float)$l['hdop'] > 0) {
                $hdopVal = (float)$l['hdop'];
                $hdopSum += $hdopVal;
                $hdopCount++;
                if ($hdopVal < $minHdop) $minHdop = $hdopVal;
            }

            // Altitude
            if (isset($l['altitude']) && $l['altitude'] !== null) {
                $altVal = (float)$l['altitude'];
                if ($altVal > $maxAlt) $maxAlt = $altVal;
            }

            // Vertical accuracy
            if (isset($l['vertical_accuracy']) && $l['vertical_accuracy'] !== null && (float)$l['vertical_accuracy'] > 0) {
                $vAccSum += (float)$l['vertical_accuracy'];
                $vAccCount++;
            }

            // Latest GNSS receiver status
            if ($latestGnss === '—' && !empty($l['gnss_status'])) {
                $latestGnss = $l['gnss_status'];
            }
            // Latest floor level
            if ($latestFloor === '—' && !empty($l['floor_level'])) {
                $latestFloor = $l['floor_level'];
            }
        }
        arsort($hotspots);
        $avgSpeed = $speedCount > 0 ? round($speedSum / $speedCount, 1) : null;
        $maxSpeed = $maxSpeed > 0 ? round($maxSpeed, 1) : null;
        $topHotspots = array_slice($hotspots, 0, 5, true);
        $total = count($locations);

        $avgSats = $satCount > 0 ? round($satSum / $satCount, 1) : null;
        $avgHdop = $hdopCount > 0 ? round($hdopSum / $hdopCount, 2) : null;
        $minHdop = $minHdop !== 999.0 ? round($minHdop, 2) : null;
        $maxAlt = $maxAlt !== -9999.0 ? round($maxAlt, 1) : null;
        $avgVAcc = $vAccCount > 0 ? round($vAccSum / $vAccCount, 1) : null;
        ?>

        <!-- Row 1: Precision Spectrum (3 info-boxes) + Velocity -->
        <style>
          .telemetry-visual-box {
            width: 46px;
            height: 46px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
          }
          .bg-success-light { background-color: rgba(40, 167, 69, 0.08); color: #28a745; }
          .bg-warning-light { background-color: rgba(255, 193, 7, 0.08); color: #ffc107; }
          .bg-danger-light { background-color: rgba(220, 53, 69, 0.08); color: #dc3545; }
          .bg-info-light { background-color: rgba(23, 162, 184, 0.08); color: #17a2b8; }
        </style>

        <div class="col-12 mt-4">
          <div class="row">
            <!-- High Precision -->
            <div class="col-md-3 col-sm-6 mb-3">
              <div class="card card-outline card-success shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                  <div>
                    <span class="text-uppercase text-muted" style="font-size: 11px; font-weight: 700; letter-spacing: 0.8px;">High Precision</span>
                    <h3 class="mb-0 mt-1" style="font-size: 26px; font-weight: 800; color: #1e2022;"><?= $high ?></h3>
                    <div class="progress mt-2" style="height: 4px; width: 100px;">
                      <div class="progress-bar bg-success" style="width: <?= $total > 0 ? round($high/$total*100) : 0 ?>%"></div>
                    </div>
                    <small class="text-muted d-block mt-1">&le; 15 m accuracy</small>
                    <p class="text-xs text-muted mb-0 mt-2" style="font-size: 11px; line-height: 1.3; color:#6c757d;">
                      Highly accurate locks, pinning the device position within standard street width.
                    </p>
                  </div>
                  <div class="telemetry-visual-box bg-success-light">
                    <i class="fas fa-crosshairs"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Medium Precision -->
            <div class="col-md-3 col-sm-6 mb-3">
              <div class="card card-outline card-warning shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                  <div>
                    <span class="text-uppercase text-muted" style="font-size: 11px; font-weight: 700; letter-spacing: 0.8px;">Medium Precision</span>
                    <h3 class="mb-0 mt-1" style="font-size: 26px; font-weight: 800; color: #1e2022;"><?= $med ?></h3>
                    <div class="progress mt-2" style="height: 4px; width: 100px;">
                      <div class="progress-bar bg-warning" style="width: <?= $total > 0 ? round($med/$total*100) : 0 ?>%"></div>
                    </div>
                    <small class="text-muted d-block mt-1">16 – 50 m accuracy</small>
                    <p class="text-xs text-muted mb-0 mt-2" style="font-size: 11px; line-height: 1.3; color:#6c757d;">
                      Moderate accuracy locks, pinning location to within a block or neighborhood zone.
                    </p>
                  </div>
                  <div class="telemetry-visual-box bg-warning-light">
                    <i class="fas fa-bullseye"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Low Precision -->
            <div class="col-md-3 col-sm-6 mb-3">
              <div class="card card-outline card-danger shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                  <div>
                    <span class="text-uppercase text-muted" style="font-size: 11px; font-weight: 700; letter-spacing: 0.8px;">Low Precision</span>
                    <h3 class="mb-0 mt-1" style="font-size: 26px; font-weight: 800; color: #1e2022;"><?= $low ?></h3>
                    <div class="progress mt-2" style="height: 4px; width: 100px;">
                      <div class="progress-bar bg-danger" style="width: <?= $total > 0 ? round($low/$total*100) : 0 ?>%"></div>
                    </div>
                    <small class="text-muted d-block mt-1">&gt; 50 m accuracy</small>
                    <p class="text-xs text-muted mb-0 mt-2" style="font-size: 11px; line-height: 1.3; color:#6c757d;">
                      Coarse locks. Usually occurs indoors or via cell tower triangulation approximations.
                    </p>
                  </div>
                  <div class="telemetry-visual-box bg-danger-light">
                    <i class="fas fa-map-pin"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Velocity -->
            <div class="col-md-3 col-sm-6 mb-3">
              <div class="card card-outline card-info shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                  <div>
                    <span class="text-uppercase text-muted" style="font-size: 11px; font-weight: 700; letter-spacing: 0.8px;">Velocity</span>
                    <h3 class="mb-0 mt-1" style="font-size: 26px; font-weight: 800; color: #1e2022;"><?= $avgSpeed !== null ? $avgSpeed . ' km/h' : '—' ?></h3>
                    <div class="progress mt-2" style="height: 4px; width: 100px;">
                      <div class="progress-bar bg-info" style="width: <?= $maxSpeed !== null && $maxSpeed > 0 ? min(100, round($avgSpeed/$maxSpeed*100)) : 0 ?>%"></div>
                    </div>
                    <small class="text-muted d-block mt-1">avg &bull; max <?= $maxSpeed !== null ? $maxSpeed . ' km/h' : '—' ?></small>
                    <p class="text-xs text-muted mb-0 mt-2" style="font-size: 11px; line-height: 1.3; color:#6c757d;">
                      Speed of the device while moving. Helps distinguish walking, running, and vehicular travel.
                    </p>
                  </div>
                  <div class="telemetry-visual-box bg-info-light">
                    <i class="fas fa-tachometer-alt"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Row 1.5: GNSS Hardware Telemetry -->
        <style>
          .bg-purple-light { background-color: rgba(111, 66, 193, 0.08); color: #6f42c1; }
          .bg-secondary-light { background-color: rgba(108, 117, 125, 0.08); color: #6c757d; }
        </style>
        <div class="col-12 mt-2">
          <div class="row">
            <!-- GPS Satellites -->
            <div class="col-md-3 col-sm-6 mb-3">
              <div class="card card-outline card-success shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                  <div>
                    <span class="text-uppercase text-muted" style="font-size: 11px; font-weight: 700; letter-spacing: 0.8px;">GPS Satellites</span>
                    <h3 class="mb-0 mt-1" style="font-size: 26px; font-weight: 800; color: #1e2022;">
                      <?= $avgSats !== null ? $avgSats : '—' ?> <span style="font-size:14px; font-weight:600; color:#8c98a5;">avg</span>
                    </h3>
                    <small class="text-muted d-block mt-2">
                      <i class="fas fa-arrow-up mr-1 text-success"></i> Peak: <?= $maxSats ?> satellites
                    </small>
                    <p class="text-xs text-muted mb-0 mt-2" style="font-size: 11px; line-height: 1.3; color:#6c757d;">
                      Number of space satellites queried by the device receiver. More satellites mean better lock.
                    </p>
                  </div>
                  <div class="telemetry-visual-box bg-success-light">
                    <i class="fas fa-satellite"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Signal Precision (HDOP) -->
            <div class="col-md-3 col-sm-6 mb-3">
              <div class="card card-outline card-primary shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                  <div>
                    <span class="text-uppercase text-muted" style="font-size: 11px; font-weight: 700; letter-spacing: 0.8px;">HDOP Precision</span>
                    <h3 class="mb-0 mt-1" style="font-size: 26px; font-weight: 800; color: #1e2022;">
                      <?= $avgHdop !== null ? $avgHdop : '—' ?>
                    </h3>
                    <small class="text-muted d-block mt-2">
                      <i class="fas fa-check mr-1 text-primary"></i> Best fix: <?= $minHdop !== null ? $minHdop : '—' ?>
                    </small>
                    <p class="text-xs text-muted mb-0 mt-2" style="font-size: 11px; line-height: 1.3; color:#6c757d;">
                      Horizontal Dilution of Precision. Values below 1.5 represent an excellent signal alignment.
                    </p>
                  </div>
                  <div class="telemetry-visual-box bg-info-light">
                    <i class="fas fa-signal"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Altitude & Verticality -->
            <div class="col-md-3 col-sm-6 mb-3">
              <div class="card card-outline card-purple shadow-sm h-100 mb-0" style="border-top: 3px solid #6f42c1;">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                  <div>
                    <span class="text-uppercase text-muted" style="font-size: 11px; font-weight: 700; letter-spacing: 0.8px;">Verticality</span>
                    <h3 class="mb-0 mt-1" style="font-size: 26px; font-weight: 800; color: #1e2022;">
                      <?= $maxAlt !== null ? number_format($maxAlt) . ' m' : '—' ?>
                    </h3>
                    <small class="text-muted d-block mt-2">
                      Avg Vert. Acc: <?= $avgVAcc !== null ? $avgVAcc . ' m' : '—' ?>
                    </small>
                    <p class="text-xs text-muted mb-0 mt-2" style="font-size: 11px; line-height: 1.3; color:#6c757d;">
                      Height coordinates above sea level. Vertical accuracy denotes elevation reading error margins.
                    </p>
                  </div>
                  <div class="telemetry-visual-box bg-purple-light">
                    <i class="fas fa-mountain"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Receiver Status & Floor level -->
            <div class="col-md-3 col-sm-6 mb-3">
              <div class="card card-outline card-secondary shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                  <div>
                    <span class="text-uppercase text-muted" style="font-size: 11px; font-weight: 700; letter-spacing: 0.8px;">Receiver Status</span>
                    <h3 class="mb-0 mt-1" style="font-size: 20px; font-weight: 800; color: #6c757d;">
                      <?= esc(strtoupper($latestGnss)) ?>
                    </h3>
                    <small class="text-muted d-block mt-2">
                      <i class="fas fa-layer-group mr-1"></i> Floor: <?= esc($latestFloor) ?>
                    </small>
                    <p class="text-xs text-muted mb-0 mt-2" style="font-size: 11px; line-height: 1.3; color:#6c757d;">
                      Lock state of the GPS receiver module inside the mobile device, and vertical building level.
                    </p>
                  </div>
                  <div class="telemetry-visual-box bg-secondary-light">
                    <i class="fas fa-microchip"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Row 2: Provider Footprint + Top Hotspots -->
        <div class="col-12 mt-3">
          <div class="row">
            <!-- Provider Footprint -->
            <div class="col-md-6">
              <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                  <h3 class="card-title"><i class="fas fa-satellite-dish mr-2"></i>Provider Footprint</h3>
                </div>
                <div class="card-body p-0">
                  <?php if (empty($providers)): ?>
                    <div class="text-center py-4 text-muted"><i class="fas fa-satellite fa-2x mb-2 d-block"></i>No data</div>
                  <?php else: ?>
                    <ul class="list-group list-group-flush">
                      <?php foreach ($providers as $prov => $cnt): ?>
                        <?php
                          $pct = $total > 0 ? round($cnt / $total * 100) : 0;
                          $icon = $prov === 'gps' ? 'fa-satellite text-success' : ($prov === 'network' ? 'fa-wifi text-info' : 'fa-broadcast-tower text-secondary');
                          $bar  = $prov === 'gps' ? 'bg-success' : ($prov === 'network' ? 'bg-info' : 'bg-secondary');
                        ?>
                        <li class="list-group-item px-3 py-2">
                          <div class="d-flex justify-content-between align-items-center mb-1">
                            <span><i class="fas <?= $icon ?> mr-2"></i><strong><?= strtoupper(esc($prov)) ?></strong></span>
                            <span class="badge badge-light border"><?= $cnt ?> <small class="text-muted">(<?= $pct ?>%)</small></span>
                          </div>
                          <div class="progress" style="height:5px;">
                            <div class="progress-bar <?= $bar ?>" style="width:<?= $pct ?>%"></div>
                          </div>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- Top Hotspots -->
            <div class="col-md-6">
              <div class="card card-outline card-success shadow-sm">
                <div class="card-header">
                  <h3 class="card-title"><i class="fas fa-map-marked-alt mr-2"></i>Top Visited Hotspots</h3>
                  <small class="card-subtitle text-muted ml-2">&approx; 10 m radius clusters</small>
                </div>
                <div class="card-body p-0">
                  <?php if (empty($topHotspots)): ?>
                    <div class="text-center py-4 text-muted"><i class="fas fa-map-marker fa-2x mb-2 d-block"></i>No data</div>
                  <?php else: ?>
                    <ul class="list-group list-group-flush">
                      <?php $rank = 1; foreach ($topHotspots as $coords => $visits): ?>
                        <?php [$hlat, $hlng] = explode(',', $coords); ?>
                        <li class="list-group-item px-3 py-2">
                          <div class="d-flex justify-content-between align-items-center">
                            <div>
                              <span class="badge badge-secondary mr-2">#<?= $rank ?></span>
                              <a href="https://www.google.com/maps?q=<?= $hlat ?>,<?= $hlng ?>" target="_blank"
                                 class="text-primary" style="font-size:12px;font-family:monospace;">
                                <?= $hlat ?>, <?= $hlng ?>
                              </a>
                            </div>
                            <span class="badge badge-success"><?= $visits ?> visit<?= $visits > 1 ? 's' : '' ?></span>
                          </div>
                        </li>
                      <?php $rank++; endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var locations = <?= json_encode($locations) ?>;

    // Filter valid coordinate pairs
    var coords = [];
    var markers = [];

    locations.forEach(function(l) {
        if (l.latitude && l.longitude) {
            var lat = parseFloat(l.latitude);
            var lng = parseFloat(l.longitude);
            if (!isNaN(lat) && !isNaN(lng)) {
                coords.push([lat, lng]);
                markers.push({
                    lat: lat,
                    lng: lng,
                    provider: l.provider || 'unknown',
                    accuracy: l.accuracy || 'unknown',
                    speed: l.speed ? (parseFloat(l.speed)*3.6).toFixed(1) : null,
                    time: l.location_time ? new Date(parseInt(l.location_time)).toLocaleString() : '—'
                });
            }
        }
    });

    // Default center if no coordinates
    var defaultCenter = coords.length > 0 ? coords[0] : [0.0, 0.0];
    var zoomLevel = coords.length > 0 ? 14 : 2;

    var map = L.map('leafletMap').setView(defaultCenter, zoomLevel);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Plot markers and build route path
    var pathCoords = [];
    markers.forEach(function(m, idx) {
        pathCoords.push([m.lat, m.lng]);
        
        var popupContent = `
            <strong>GPS Fix #${idx + 1}</strong><br>
            <b>Time:</b> ${m.time}<br>
            <b>Provider:</b> ${m.provider.toUpperCase()}<br>
            <b>Accuracy:</b> ${m.accuracy} m<br>
            ${m.speed ? `<b>Speed:</b> ${m.speed} km/h<br>` : ''}
            <a href="https://maps.google.com/?q=${m.lat},${m.lng}" target="_blank" class="btn btn-xs btn-primary text-white mt-2 d-inline-block">View on Google Maps</a>
        `;

        L.marker([m.lat, m.lng]).addTo(map).bindPopup(popupContent);
    });

    if (pathCoords.length > 1) {
        // Draw polyline connecting coordinates
        var polyline = L.polyline(pathCoords, {color: '#007bff', weight: 4, opacity: 0.7}).addTo(map);
        map.fitBounds(polyline.getBounds());
    }
});
</script>
<?php include __DIR__ . '/partials/_delete_confirm.php'; ?>

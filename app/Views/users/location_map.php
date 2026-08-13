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

        <!-- Coordinates Data Table -->
        <div class="col-12 mt-4">
          <div class="card card-secondary shadow-sm">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-list mr-2"></i>Location Coordinate Logs</h3>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered mb-0">
                  <thead class="thead-light">
                    <tr>
                      <th>Coordinates</th>
                      <th>Accuracy</th>
                      <th>Provider</th>
                      <th>Speed</th>
                      <th>Altitude</th>
                      <th>Timestamp</th>
                      <th class="text-center">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($locations)): ?>
                      <tr>
                        <td colspan="7" class="text-center py-5">
                          <i class="fas fa-map-marker-alt fa-3x text-muted mb-3 d-block"></i>
                          <h5 class="text-muted">No coordinate logs found</h5>
                        </td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($locations as $l): ?>
                        <tr>
                          <td>
                            <code class="text-dark bg-light p-2 rounded d-inline-block font-weight-bold">
                              <?= esc($l['latitude']) ?>, <?= esc($l['longitude']) ?>
                            </code>
                          </td>
                          <td>
                            <span class="badge badge-<?= isset($l['accuracy']) && $l['accuracy'] <= 50 ? 'success' : 'warning' ?>">
                              <?= isset($l['accuracy']) ? esc($l['accuracy']) . ' m' : '—' ?>
                            </span>
                          </td>
                          <td>
                            <span class="badge badge-info text-uppercase"><?= esc($l['provider'] ?? 'unknown') ?></span>
                          </td>
                          <td><?= isset($l['speed']) ? round((float)$l['speed']*3.6, 1) . ' km/h' : '—' ?></td>
                          <td><?= isset($l['altitude']) ? round((float)$l['altitude'], 1) . ' m' : '—' ?></td>
                          <td><?= !empty($l['location_time']) ? date('D, M d, Y H:i:s', (int)($l['location_time'] / 1000)) : '—' ?></td>
                          <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger delete-row"
                                    data-id="<?= $l['counter'] ?? $l['id'] ?? '' ?>"
                                    data-url="<?= base_url('location/delete') ?>"
                                    title="Delete coordinate">
                              <i class="fas fa-trash"></i>
                            </button>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card-footer">
              <div class="float-right my-2">
                <?php if (isset($pager)): ?>
                  <?= $pager->links('default', 'bootstrap5_full') ?>
                <?php endif; ?>
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

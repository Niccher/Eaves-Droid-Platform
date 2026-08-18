<?php
/**
 * FilesController View
 *
 * Displays all files with categorization. Navigation allows switching between All, Images, Videos, Documents.
 * Modeled after sms.php and apps_all.php patterns.
 *
 * @var array $files_dump Array of file information
 * @var string $files_head Page header title
 * @var string $files_urls Navigation button group HTML
 * @var int $totalFiles Total number of files
 * @var int $currentPage Current pagination page
 * @var int $perPage Items per page
 * @var object $pager Pagination object
 */
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                        <div class="d-flex align-items-center">
                            <h1 class="h2 mb-0">
                                <i class="fas fa-folder text-primary mr-2"></i>
                                <?php echo $files_head ?? 'All Files' ?>
                            </h1>
                            <div class="ml-3 d-flex flex-wrap" style="gap: 5px;">
                                <span class="badge badge-light border p-2">
                                    <i class="fas fa-folder text-primary mr-1"></i>
                                    Total: <b><?php echo $filesCounts['all'] ?? 0 ?></b>
                                </span>
                                <span class="badge badge-light border p-2">
                                    <i class="fas fa-photo-video text-info mr-1"></i>
                                    Media: <b><?php echo $filesCounts['media'] ?? 0 ?></b>
                                </span>
                                <span class="badge badge-light border p-2">
                                    <i class="fas fa-file-alt text-success mr-1"></i>
                                    Documents: <b><?php echo $filesCounts['documents'] ?? 0 ?></b>
                                </span>
                                <span class="badge badge-light border p-2">
                                    <i class="fas fa-music text-warning mr-1"></i>
                                    Audio: <b><?php echo $filesCounts['audio'] ?? 0 ?></b>
                                </span>
                                <span class="badge badge-light border p-2">
                                    <i class="fas fa-file-archive text-purple mr-1"></i>
                                    Archives: <b><?php echo $filesCounts['archives'] ?? 0 ?></b>
                                </span>
                                <span class="badge badge-light border p-2">
                                    <i class="fas fa-ellipsis-h text-secondary mr-1"></i>
                                    Others: <b><?php echo $filesCounts['others'] ?? 0 ?></b>
                                </span>
                            </div>
                        </div>
                        <p class="text-muted mt-2 mb-0"><?php echo $files_desc ?? 'View and manage files on the device' ?></p>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="float-right mt-2 mb-2">
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <?php echo $files_urls; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <!-- Main Card -->
                    <div class="card card-secondary shadow-sm">
                        <div class="card-header d-flex align-items-center">
                             <h3 class="card-title">
                                 <i class="fas fa-file mr-2"></i>
                                 Files
                                 <small class="text-white ml-2">Showing <?php echo count($files_dump) ?>
                                     of <?php echo $totalFiles ?? 0 ?> files</small>
                             </h3>
                             <div class="card-tools ml-auto">
                                 <button type="button" class="btn btn-success btn-sm" id="pdfExport" title="Export PDF">
                                     <i class="fas fa-file-pdf mr-1"></i> Export
                                 </button>
                                 <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                     <i class="fas fa-minus"></i>
                                 </button>
                             </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="border-bottom px-3 py-2">
                            <div class="input-group input-group-sm" style="max-width:350px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" class="form-control table-search" placeholder="Search by name or path..." data-table="table-sortable">
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered mb-0 table-sortable">
                                    <thead class="thead-light">
                                    <tr>
                                        <th width="45%">Name / Path</th>
                                        <th width="12%">Size</th>
                                        <th width="18%">Last Modified</th>
                                        <th width="12%">Category</th>
                                        <th width="13%">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($files_dump)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="empty-state">
                                                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                                    <h4>No files found</h4>
                                                    <p class="text-muted">Files will appear here once the device syncs.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($files_dump as $index => $fileinfo): ?>
                                            <?php
                                            // Format date
                                            if (is_numeric($fileinfo['last_modified'])) {
                                                $dt = date('Y-m-d H:i:s', $fileinfo['last_modified'] / 1000);
                                                $dateOnly = date('M d, Y', $fileinfo['last_modified'] / 1000);
                                                $timeOnly = date('H:i:s', $fileinfo['last_modified'] / 1000);
                                                $dayName = date('D', $fileinfo['last_modified'] / 1000);
                                            } else {
                                                $dt = $fileinfo['last_modified'];
                                                $dateOnly = date('M d, Y', strtotime($fileinfo['last_modified']));
                                                $timeOnly = date('H:i:s', strtotime($fileinfo['last_modified']));
                                                $dayName = date('D', strtotime($fileinfo['last_modified']));
                                            }

                                            // File category with icons and colors
                                            $category = $fileinfo['category'] ?? 'Other';
                                            $categoryLower = strtolower($category);

                                            $categoryConfig = [
                                                'image' => [
                                                    'icon' => 'image',
                                                    'color' => 'primary',
                                                    'bg' => 'bg-primary',
                                                    'label' => 'Image',
                                                    'pulse' => ''
                                                ],
                                                'video' => [
                                                    'icon' => 'video',
                                                    'color' => 'info',
                                                    'bg' => 'bg-info',
                                                    'label' => 'Video',
                                                    'pulse' => ''
                                                ],
                                                'document' => [
                                                    'icon' => 'file-alt',
                                                    'color' => 'success',
                                                    'bg' => 'bg-success',
                                                    'label' => 'Document',
                                                    'pulse' => ''
                                                ],
                                                'spreadsheet' => [
                                                    'icon' => 'file-excel',
                                                    'color' => 'success',
                                                    'bg' => 'bg-success',
                                                    'label' => 'Spreadsheet',
                                                    'pulse' => ''
                                                ],
                                                'presentation' => [
                                                    'icon' => 'file-powerpoint',
                                                    'color' => 'success',
                                                    'bg' => 'bg-success',
                                                    'label' => 'Presentation',
                                                    'pulse' => ''
                                                ],
                                                'ebook' => [
                                                    'icon' => 'book',
                                                    'color' => 'success',
                                                    'bg' => 'bg-success',
                                                    'label' => 'Ebook',
                                                    'pulse' => ''
                                                ],
                                                'audio' => [
                                                    'icon' => 'music',
                                                    'color' => 'warning',
                                                    'bg' => 'bg-warning',
                                                    'label' => 'Audio',
                                                    'pulse' => ''
                                                ],
                                                'archive' => [
                                                    'icon' => 'file-archive',
                                                    'color' => 'purple',
                                                    'bg' => 'bg-purple',
                                                    'label' => 'Archive',
                                                    'pulse' => ''
                                                ],
                                                'code' => [
                                                    'icon' => 'code',
                                                    'color' => 'dark',
                                                    'bg' => 'bg-dark',
                                                    'label' => 'Code',
                                                    'pulse' => ''
                                                ],
                                                'font' => [
                                                    'icon' => 'font',
                                                    'color' => 'secondary',
                                                    'bg' => 'bg-secondary',
                                                    'label' => 'Font',
                                                    'pulse' => ''
                                                ],
                                                'application' => [
                                                    'icon' => 'mobile-alt',
                                                    'color' => 'danger',
                                                    'bg' => 'bg-danger',
                                                    'label' => 'Application',
                                                    'pulse' => ''
                                                ],
                                                'other' => [
                                                    'icon' => 'file',
                                                    'color' => 'secondary',
                                                    'bg' => 'bg-secondary',
                                                    'label' => 'Other',
                                                    'pulse' => ''
                                                ]
                                            ];

                                            $typeInfo = $categoryConfig[$categoryLower] ?? [
                                                    'icon' => 'question-circle',
                                                    'color' => 'secondary',
                                                    'bg' => 'bg-secondary',
                                                    'label' => $category,
                                                    'pulse' => ''
                                                ];

                                            // Generate avatar/icon from file name/extension
                                            $fileName = $fileinfo['name'];
                                            $extension = $fileinfo['extension'] ?? '';
                                            $avatarText = strtoupper(substr($fileName, 0, 1));

                                            // Row background based on category
                                            $rowBgClass = '';
                                            if ($categoryLower === 'image') {
                                                $rowBgClass = 'bg-light-blue';
                                            } elseif ($categoryLower === 'video') {
                                                $rowBgClass = 'bg-light-info';
                                            } elseif ($categoryLower === 'document') {
                                                $rowBgClass = 'bg-light-green';
                                            }
                                            ?>

                                            <tr class="<?php echo $rowBgClass; ?>">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3">
                                                            <div class="avatar-circle-sm <?php echo $typeInfo['bg']; ?> text-white shadow-sm">
                                                                <i class="fas fa-<?php echo $typeInfo['icon']; ?>"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="font-weight-bold text-dark">
                                                                <?php echo htmlspecialchars($fileName); ?>
                                                            </div>
                                                            <div class="text-muted font-italic"><?php echo htmlspecialchars($fileinfo['path']); ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($fileinfo['formatted_size']); ?>
                                                </td>
                                                <td>
                                                    <div class="text-dark">
                                                        <i class="fas fa-calendar-day text-primary mr-1"></i>
                                                        <?php echo $dateOnly; ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock text-secondary mr-1"></i>
                                                        <?php echo $timeOnly; ?> (<?php echo $dayName; ?>)
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $typeInfo['bg']; ?> text-white p-2 <?php echo $typeInfo['pulse']; ?>">
                                                        <i class="fas fa-<?php echo $typeInfo['icon']; ?> mr-1"></i>
                                                        <?php echo $typeInfo['label']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-info" 
                                                            data-toggle="modal" 
                                                            data-target="#fileDetailsModal"
                                                            onclick="showFileDetails(<?php echo htmlspecialchars(json_encode($fileinfo), ENT_QUOTES, 'UTF-8'); ?>)">
                                                        <i class="fas fa-info-circle"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-row ml-1"
                                                            data-id="<?= $fileinfo['id'] ?? '' ?>"
                                                            data-url="<?= base_url('files/delete') ?>"
                                                            data-name="<?= htmlspecialchars($fileinfo['name'] ?? '') ?>"
                                                            title="Delete file entry">
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
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="entry-info">
                                        Showing <?php echo (($currentPage - 1) * $perPage) + 1 ?>
                                        to <?php echo min($currentPage * $perPage, $totalFiles ?? 0) ?>
                                        of <?php echo $totalFiles ?? 0 ?> entries
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="float-right">
                                        <?php if (isset($pager) && $totalFiles > $perPage): ?>
                                            <?= $pager->links('default', 'bootstrap5_full') ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->

<!-- File Details Modal -->
<div class="modal fade" id="fileDetailsModal" tabindex="-1" role="dialog" aria-labelledby="fileDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="fileDetailsModalLabel">
                    <i class="fas fa-file-alt mr-2"></i>File Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <!-- File Name -->
                    <div class="row mb-3 pb-3 border-bottom">
                        <div class="col-md-4">
                            <strong><i class="fas fa-file text-primary mr-2"></i>File Name</strong>
                        </div>
                        <div class="col-md-8">
                            <span id="modal-file-name" class="text-dark"></span>
                        </div>
                    </div>

                    <!-- File Extension -->
                    <div class="row mb-3 pb-3 border-bottom">
                        <div class="col-md-4">
                            <strong><i class="fas fa-code text-success mr-2"></i>Extension</strong>
                        </div>
                        <div class="col-md-8">
                            <code id="modal-file-extension" class="bg-light p-1"></code>
                        </div>
                    </div>

                    <!-- File Path -->
                    <div class="row mb-3 pb-3 border-bottom">
                        <div class="col-md-4">
                            <strong><i class="fas fa-folder-open text-warning mr-2"></i>Path</strong>
                        </div>
                        <div class="col-md-8">
                            <code id="modal-file-path" class="bg-light p-1 d-block text-wrap"></code>
                        </div>
                    </div>

                    <!-- File Size -->
                    <div class="row mb-3 pb-3 border-bottom">
                        <div class="col-md-4">
                            <strong><i class="fas fa-weight text-info mr-2"></i>Size</strong>
                        </div>
                        <div class="col-md-8">
                            <span id="modal-file-size" class="badge badge-info p-2"></span>
                            <small class="text-muted ml-2">(<span id="modal-file-size-bytes"></span> bytes)</small>
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="row mb-3 pb-3 border-bottom">
                        <div class="col-md-4">
                            <strong><i class="fas fa-tag text-purple mr-2"></i>Category</strong>
                        </div>
                        <div class="col-md-8">
                            <span id="modal-file-category"></span>
                        </div>
                    </div>

                    <!-- Last Modified -->
                    <div class="row mb-3 pb-3 border-bottom">
                        <div class="col-md-4">
                            <strong><i class="fas fa-calendar-alt text-danger mr-2"></i>Last Modified</strong>
                        </div>
                        <div class="col-md-8">
                            <span id="modal-file-modified" class="text-dark"></span>
                        </div>
                    </div>

                    <!-- Device ID -->
                    <div class="row mb-3 pb-3 border-bottom">
                        <div class="col-md-4">
                            <strong><i class="fas fa-mobile-alt text-secondary mr-2"></i>Device ID</strong>
                        </div>
                        <div class="col-md-8">
                            <code id="modal-device-id" class="bg-light p-1"></code>
                        </div>
                    </div>

                    <!-- Created At -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="fas fa-clock text-muted mr-2"></i>Uploaded</strong>
                        </div>
                        <div class="col-md-8">
                            <span id="modal-created-at" class="text-muted"></span>
                        </div>
                    </div>

                    <!-- Is Directory -->
                    <div class="row mb-3" id="modal-is-directory-row" style="display: none;">
                        <div class="col-md-4">
                            <strong><i class="fas fa-folder text-warning mr-2"></i>Type</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="badge badge-warning">Directory</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">

                <?php
                // Pass plan tier to JS — buttons always visible, JS gates the action
                $userPlanTier = 'free';
                if (function_exists('auth') && auth()->loggedIn()) {
                    $planGate = new \App\Services\PlanGate();
                    $userPlanTier = $planGate->currentPlanKey((int) auth()->user()->id);
                }
                ?>
                <!-- Plan tier constant for JS -->
                <script>const USER_PLAN_TIER = '<?= $userPlanTier ?>';</script>

                <!-- ─── Remote action buttons (always visible) ─────────────────────────── -->
                <div class="d-flex flex-wrap" style="gap:6px;">
                    <button type="button" class="btn btn-success btn-sm" id="btn-fcm-download"
                            onclick="fcmFetchFile()"
                            title="Download this file from the mobile device to the server">
                        <i class="fas fa-cloud-download-alt mr-1"></i>Download this File
                        <?php if ($userPlanTier !== 'platinum'): ?>
                            <i class="fas fa-crown ml-1 text-warning" style="font-size:0.75em;" title="Platinum feature"></i>
                        <?php endif; ?>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="btn-fcm-delete"
                            onclick="fcmDeleteFile()"
                            title="Permanently delete this file from the mobile device">
                        <i class="fas fa-trash-alt mr-1"></i>Delete this File from Mobile Device
                        <?php if ($userPlanTier !== 'platinum'): ?>
                            <i class="fas fa-crown ml-1 text-warning" style="font-size:0.75em;" title="Platinum feature"></i>
                        <?php endif; ?>
                    </button>
                </div>

                <!-- ─── Live status bar (shown only after a successful dispatch) ────────── -->
                <div id="fcm-action-status" style="display:none; flex:1; min-width:200px;">
                    <div class="d-flex align-items-center" style="gap:8px;">
                        <i id="fcm-status-icon" class="fas fa-circle-notch fa-spin text-muted"></i>
                        <div style="flex:1;">
                            <div id="fcm-status-msg" class="small font-weight-bold">Sending command…</div>
                            <div id="fcm-status-timeline" class="small text-muted" style="font-size:0.78em; display: none;"></div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

</div>
<!-- /.content-wrapper -->

<style>
    .avatar-circle-sm {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: bold;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
    }

    .empty-state i {
        opacity: 0.5;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Row background colors */
    .bg-light-blue {
        background-color: #f0f9ff !important; /* Light blue for images */
    }

    .bg-light-info {
        background-color: #f0fdfa !important; /* Light teal for videos */
    }

    .bg-light-green {
        background-color: #f0fff4 !important; /* Light green for documents */
    }

    /* Purple color for archives */
    .bg-purple {
        background-color: #6f42c1 !important;
    }

    /* Hover effects */
    tr:hover {
        background-color: #f8f9fa !important;
    }

    .avatar-circle-sm:hover {
        transform: scale(1.1);
        transition: transform 0.2s ease;
    }

    @media (max-width: 768px) {
        .entry-info {
            text-align: center;
            margin-bottom: 1rem;
        }

        .float-right {
            float: none !important;
            text-align: center;
        }

        .avatar-circle-sm {
            width: 32px;
        .entry-info { text-align: center; margin-bottom: 1rem; }
        .float-right { float: none !important; text-align: center; }
        .avatar-circle-sm { width: 32px; height: 32px; font-size: 14px; }
    }
    .table-sortable thead th { cursor: pointer; user-select: none; }
    .table-sortable thead th.sort-asc::after { content: ' \25B2'; font-size: 0.7em; }
    .table-sortable thead th.sort-desc::after { content: ' \25BC'; font-size: 0.7em; }
</style>
<script>
    // ─── State ──────────────────────────────────────────────────────────────────
    let _activeFileDeviceId = null;
    let _activeFilePath     = null;
    let _activeFileName     = null;
    let _activeFileSize     = 0;
    let _pollTimer          = null;   // setInterval handle
    const FCM_STATUS_BASE   = '<?= base_url('api/v1/fcm-status') ?>';
    const FCM_CMD_BASE      = '<?= base_url('api/v1/fcm-commands') ?>';
    const POLL_INTERVAL_MS  = 3000;  // poll every 3 seconds
    const POLL_MAX_TRIES    = 45;    // 45 × 3s = 135s max wait

    // ─── showFileDetails ────────────────────────────────────────────────────────
    function showFileDetails(fileData) {
        _activeFileDeviceId = fileData.device_id || null;
        _activeFilePath     = fileData.path       || null;
        _activeFileName     = fileData.name       || 'file';
        _activeFileSize     = fileData.size_bytes  || 0;        // Reset live-status panel
        _stopPolling();
        _hideStatus();

        // Re-enable action buttons if Platinum user
        const isDir = fileData.is_directory == 1;
        const dlBtn = document.getElementById('btn-fcm-download');
        const delBtn = document.getElementById('btn-fcm-delete');
        if (dlBtn)  dlBtn.disabled  = isDir || !_activeFileDeviceId;
        if (delBtn) delBtn.disabled = !_activeFileDeviceId;

        // Populate modal fields
        document.getElementById('modal-file-name').textContent = fileData.name || 'N/A';
        
        // Extension
        document.getElementById('modal-file-extension').textContent = fileData.extension ? '.' + fileData.extension : 'N/A';
        
        // Path
        document.getElementById('modal-file-path').textContent = fileData.path || 'N/A';
        
        // Size
        document.getElementById('modal-file-size').textContent = fileData.formatted_size || 'N/A';
        document.getElementById('modal-file-size-bytes').textContent = fileData.size_bytes ? fileData.size_bytes.toLocaleString() : '0';
        
        // Category with icon
        const categoryIcons = {
            'Image': 'fa-image text-primary',
            'Video': 'fa-video text-info',
            'Document': 'fa-file-alt text-success',
            'Spreadsheet': 'fa-file-excel text-success',
            'Presentation': 'fa-file-powerpoint text-success',
            'Ebook': 'fa-book text-success',
            'Audio': 'fa-music text-warning',
            'Archive': 'fa-file-archive text-purple',
            'Code': 'fa-code text-dark',
            'Font': 'fa-font text-secondary',
            'Application': 'fa-mobile-alt text-danger',
            'Other': 'fa-file text-secondary'
        };
        
        const category = fileData.category || 'Other';
        const iconClass = categoryIcons[category] || 'fa-file text-secondary';
        const categoryBadgeColors = {
            'Image': 'badge-primary',
            'Video': 'badge-info',
            'Document': 'badge-success',
            'Spreadsheet': 'badge-success',
            'Presentation': 'badge-success',
            'Ebook': 'badge-success',
            'Audio': 'badge-warning',
            'Archive': 'badge-purple',
            'Code': 'badge-dark',
            'Font': 'badge-secondary',
            'Application': 'badge-danger',
            'Other': 'badge-secondary'
        };
        const badgeClass = categoryBadgeColors[category] || 'badge-secondary';
        
        document.getElementById('modal-file-category').innerHTML = 
            '<span class="badge ' + badgeClass + ' p-2"><i class="fas ' + iconClass + ' mr-1"></i>' + category + '</span>';
        
        // Last Modified
        if (fileData.last_modified) {
            const modDate = new Date(parseInt(fileData.last_modified));
            document.getElementById('modal-file-modified').textContent = modDate.toLocaleString();
        } else if (fileData.formatted_date) {
            document.getElementById('modal-file-modified').textContent = fileData.formatted_date;
        } else {
            document.getElementById('modal-file-modified').textContent = 'N/A';
        }
        
        // Device ID
        document.getElementById('modal-device-id').textContent = fileData.device_id || 'N/A';
        
        // Created At
        document.getElementById('modal-created-at').textContent = fileData.created_at || 'N/A';
        
        document.getElementById('modal-is-directory-row').style.display = (fileData.is_directory == 1) ? '' : 'none';
    }

    // ─── Status panel helpers ────────────────────────────────────────────────────
    function _hideStatus() {
        document.getElementById('fcm-action-status').style.display = 'none';
        document.getElementById('fcm-status-timeline').textContent = '';
    }

    function _showStatus(msg, state) {
        const panel  = document.getElementById('fcm-action-status');
        const iconEl = document.getElementById('fcm-status-icon');
        const msgEl  = document.getElementById('fcm-status-msg');
        panel.style.display = '';
        msgEl.textContent   = msg;
        const iconMap = {
            pending: 'fas fa-circle-notch fa-spin text-muted',
            success: 'fas fa-check-circle text-success',
            error:   'fas fa-exclamation-triangle text-danger',
            timeout: 'fas fa-clock text-warning'
        };
        iconEl.className = iconMap[state] || iconMap.pending;
    }

    function _appendTimeline(entry) {
        const tl   = document.getElementById('fcm-status-timeline');
        const time = new Date().toLocaleTimeString();
        tl.innerHTML += '<div>' + time + ' — ' + entry + '</div>';
    }

    // ─── Polling engine ──────────────────────────────────────────────────────────
    function _startPolling(logId) {
        let tries = 0;
        _appendTimeline('Command dispatched. Waiting for device…');
        _pollTimer = setInterval(function() {
            tries++;
            if (tries > POLL_MAX_TRIES) {
                _stopPolling();
                _showStatus('Device did not respond within 2 minutes. It may be offline.', 'timeout');
                _appendTimeline('Timed out — no ACK received.');
                _setActionBtnsDisabled(false);
                return;
            }
            $.getJSON(FCM_STATUS_BASE + '/' + logId, function(resp) {
                const status = resp.status || 'pending';
                const uiState = status === 'pending' ? 'pending'
                              : status === 'ack_success' ? 'success'
                              : status === 'timeout'     ? 'timeout' : 'error';
                _showStatus(resp.message || '…', uiState);
                if (status === 'ack_success') {
                    _stopPolling();
                    _appendTimeline('✅ Device ACK received — ' + (resp.acked_at || ''));
                    _setActionBtnsDisabled(false);
                } else if (status === 'ack_failed') {
                    _stopPolling();
                    _appendTimeline('❌ Device reported failure — ' + (resp.acked_at || ''));
                    _setActionBtnsDisabled(false);
                } else if (status === 'timeout') {
                    _stopPolling();
                    _appendTimeline('⏰ Server timeout — device never responded.');
                    _setActionBtnsDisabled(false);
                } else {
                    _appendTimeline('Still waiting… (' + (resp.elapsed || tries * 3) + 's)');
                }
            }).fail(function() {
                _appendTimeline('Polling error on try ' + tries + '…');
            });
        }, POLL_INTERVAL_MS);
    }

    function _stopPolling() {
        if (_pollTimer) { clearInterval(_pollTimer); _pollTimer = null; }
    }

    $('#fileDetailsModal').on('hidden.bs.modal', function() { _stopPolling(); _hideStatus(); });

    function _setActionBtnsDisabled(state) {
        const dl  = document.getElementById('btn-fcm-download');
        const del = document.getElementById('btn-fcm-delete');
        if (dl)  dl.disabled  = state;
        if (del) del.disabled = state;
    }
    // ─── FCM dispatch helper ─────────────────────────────────────────────────────
    function _dispatchFcmCommand(command, confirmOpts) {
        // ── Upgrade gate: non-Platinum users see a friendly upgrade prompt ───────
        if (USER_PLAN_TIER !== 'platinum') {
            Swal.fire({
                icon:  'info',
                title: '<i class="fas fa-crown text-warning mr-2"></i>Platinum Feature',
                html:  'Downloading or deleting files directly from the mobile device '
                     + 'requires a <strong>Platinum</strong> plan.<br><br>'
                     + 'Upgrade your plan to remotely manage files on your device.',
                showCancelButton:   true,
                confirmButtonColor: '#f59e0b',
                confirmButtonText:  '<i class="fas fa-crown mr-1"></i>Upgrade to Platinum',
                cancelButtonText:   'Maybe later',
                customClass: { confirmButton: 'btn btn-warning' }
            }).then(function(r) {
                if (r.isConfirmed) window.location.href = '<?= base_url('billing') ?>';
            });
            return;
        }

        // ── Platinum: proceed with normal guard + dispatch ───────────────────
        if (!_activeFileDeviceId || !_activeFilePath) {
            Swal.fire({ icon:'warning', title:'No device linked',
                text:'This file has no associated device ID.', timer:3000, showConfirmButton:false });
            return;
        }

        // Size check for download (200MB limit check)
        if (command === 'cmd_fetch_file' && _activeFileSize > 209715200) {
            Swal.fire({
                icon: 'error',
                title: 'File Size Exceeded',
                text: 'This file exceeds the maximum allowed transfer limit of 200MB.'
            });
            return;
        }

        const execute = () => {
            _setActionBtnsDisabled(true);
            _hideStatus();
            _showStatus('Sending command to device…', 'pending');
            const url = FCM_CMD_BASE + '/' + encodeURIComponent(_activeFileDeviceId) + '/' + command;
            $.ajax({
                url: url, type: 'POST',
                data: { payload: _activeFilePath },
                dataType: 'json',
                success: function(resp) {
                    if (resp && resp.success) {
                        _showStatus('Command dispatched (log #' + resp.action_log_id + '). Awaiting device…', 'pending');
                        _startPolling(resp.action_log_id);
                    } else {
                        _showStatus('Dispatch failed: ' + JSON.stringify(resp.messages || resp.message || 'Unknown'), 'error');
                        _setActionBtnsDisabled(false);
                    }
                },
                error: function(xhr) {
                    let msg = 'Server error';
                    try { const r = JSON.parse(xhr.responseText); msg = r.messages ? JSON.stringify(r.messages) : (r.message || msg); } catch(e){}
                    _showStatus('Failed: ' + msg, 'error');
                    _setActionBtnsDisabled(false);
                }
            });
        };

        if (confirmOpts) {
            Swal.fire(confirmOpts).then(function(result) {
                if (result.isConfirmed) execute();
            });
        } else {
            execute();
        }
    }

    // ─── Public: Download from Device ────────────────────────────────────────────
    function fcmFetchFile() {
        _dispatchFcmCommand('cmd_fetch_file', null);
    }
    // ─── Public: Delete from Device ──────────────────────────────────────────────
    function fcmDeleteFile() {
        _dispatchFcmCommand('cmd_delete_file', {
            title: 'Delete from Device?',
            html:  '<span class="text-danger"><strong>Warning:</strong></span> This will permanently delete '
                 + '<strong>' + _activeFileName + '</strong> from the Android device.<br><br>'
                 + 'This action <u>cannot be undone</u>.',
            icon:  'warning',
            showCancelButton:   true,
            confirmButtonColor: '#dc3545',
            confirmButtonText:  '<i class="fas fa-trash-alt mr-1"></i>Yes, Delete on Device',
            cancelButtonText:   'Cancel'
        });
    }
</script>

<script>
$(document).ready(function() {
    // Real-time table search
    $('.table-search').on('keyup', function() {
        var keyword = $(this).val().toLowerCase();
        var table = $(this).data('table');
        $('.' + table + ' tbody tr').each(function() {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(keyword) > -1);
        });
    });

    // Column sorting
    $('.table-sortable thead th').on('click', function() {
        var table = $(this).closest('table');
        var tbody = table.find('tbody');
        var index = $(this).index();
        var rows = tbody.find('tr').toArray();
        var asc = !$(this).hasClass('sort-asc');
        table.find('thead th').removeClass('sort-asc sort-desc');
        $(this).toggleClass('sort-asc', asc).toggleClass('sort-desc', !asc);
        rows.sort(function(a, b) {
            var aVal = $(a).find('td').eq(index).text().trim();
            var bVal = $(b).find('td').eq(index).text().trim();
            var aNum = parseFloat(aVal), bNum = parseFloat(bVal);
            if (!isNaN(aNum) && !isNaN(bNum)) return asc ? aNum - bNum : bNum - aNum;
            return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
        });
        tbody.append(rows);
});

    // PDF Export
    $('#pdfExport').on('click', function () {
        var element = document.querySelector('.table-sortable');
        if (!element) return;
        Swal.fire({
            title: 'Generating PDF...',
            text: 'Please wait while we prepare your document',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        html2pdf().set({
            margin:       10,
            filename:     'files_export_' + Date.now() + '.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, letterRendering: true },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
        }).from(element).save().then(function () {
            Swal.close();
            Swal.fire({ icon: 'success', title: 'Export Complete', text: 'PDF has been downloaded', timer: 2000, showConfirmButton: false });
        }).catch(function () {
            Swal.close();
            Swal.fire({ icon: 'error', title: 'Export Failed', text: 'Could not generate PDF', timer: 3000, showConfirmButton: false });
        });
    });
});
</script>
<?php include __DIR__ . '/../partials/_delete_confirm.php'; ?>
<?php
/**
 * Files View
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
                        <div class="ml-3">
                            <span class="badge badge-light border p-2">
                                <i class="fas fa-chart-bar text-primary mr-1"></i>
                                Total: <b><?php echo $totalFiles ?? 0 ?></b>
                            </span>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">View and manage files on the device</p>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="float-right mt-2">
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
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file mr-2"></i>
                                Files
                                <small class="text-muted ml-2">Showing <?php echo count($files_dump) ?>
                                    of <?php echo $totalFiles ?? 0 ?> files</small>
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 tabledump">
                                    <thead class="thead-light">
                                    <tr>
                                        <th width="20%">Name</th>
                                        <th width="25%">Path</th>
                                        <th width="12%">Size</th>
                                        <th width="18%">Last Modified</th>
                                        <th width="12%">Category</th>
                                        <th width="13%">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($files_dump)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="empty-state">
                                                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                                    <h4>No files found</h4>
                                                    <p class="text-muted">Files will appear here</p>
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
                                                'audio' => [
                                                    'icon' => 'music',
                                                    'color' => 'warning',
                                                    'bg' => 'bg-warning',
                                                    'label' => 'Audio',
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
                                                            <div class="text-dark"><?php echo htmlspecialchars($fileName); ?></div>
                                                            <small class="text-muted">.<?php echo htmlspecialchars($extension); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <code class="text-muted"><?php echo htmlspecialchars($fileinfo['path']); ?></code>
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
                                                        <i class="fas fa-info-circle"></i> Details
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
                                    <div class="dataTables_info" role="status">
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
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
        .dataTables_info {
            text-align: center;
            margin-bottom: 1rem;
        }

        .float-right {
            float: none !important;
            text-align: center;
        }

        .avatar-circle-sm {
            width: 32px;
            height: 32px;
            font-size: 14px;
        }
    }
</style>
<script>
    /**
     * Populate file details modal with data
     */
    function showFileDetails(fileData) {
        // File name
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
        
        // Is Directory
        if (fileData.is_directory == 1) {
            document.getElementById('modal-is-directory-row').style.display = '';
        } else {
            document.getElementById('modal-is-directory-row').style.display = 'none';
        }
    }
</script>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3">
                            <i class="fas fa-mobile-alt text-primary mr-2"></i>
                            <?php echo $apps_head ?? 'Installed Apps' ?>
                        </h1>
                        <div class="d-flex align-items-center mt-2 mt-sm-0">
                            <span class="badge badge-light border p-2 mr-2">
                                <i class="fas fa-boxes text-primary mr-1"></i>
                                Total: <b><?php echo $totalApps ?? 0 ?></b>
                            </span>
                            <span class="badge badge-light border p-2 mr-2">
                                <i class="fas fa-shield-alt text-info mr-1"></i>
                                System: <b><?php echo $systemAppsCount ?? 0 ?></b>
                            </span>
                            <span class="badge badge-light border p-2">
                                <i class="fas fa-user text-success mr-1"></i>
                                User: <b><?php echo $userAppsCount ?? 0 ?></b>
                            </span>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">View and manage apps installed on the device</p>
                </div>
                <div class="col-lg-4 col-md-6">
                    <nav aria-label="breadcrumb" class="float-right mt-2">
                        <ol class="breadcrumb bg-transparent p-0 mb-0">
                            <?php echo $apps_urls; ?>
                        </ol>
                    </nav>
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
                                <i class="fas fa-th-large mr-2"></i>
                                Applications
                                <small class="text-muted ml-2">Showing <?php echo count($apps_dump) ?> of <?php echo $totalApps ?> apps</small>
                            </h3>
                            <div class="card-tools my-2">
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
                                <input type="text" class="form-control table-search" placeholder="Search by app name or package..." data-table="table-sortable">
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 table-sortable">
                                    <thead class="thead-light">
                                    <tr>
                                        <th width="35%">App Name</th>
                                        <th width="35%">Package Name</th>
                                        <th width="15%">Version</th>
                                        <th width="15%">App Code</th>
                                        <th width="10%">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($apps_dump)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="empty-state">
                                                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                                    <h4>No apps found</h4>
                                                    <p class="text-muted">No applications are installed on the device</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($apps_dump as $app): ?>
                                            <?php
                                            // Use the correct column names from SQL
                                            $appName = $app['Name'] ?? '';
                                            $packageName = $app['Package'] ?? '';
                                            $versionCode = $app['Code'] ?? '';
                                            $versionName = $app['version_name'] ?? '';

                                            // Generate app icon based on first letter of app name
                                            $firstLetter = strtoupper(substr($appName, 0, 1));

                                            // Color scheme for app icons
                                            $colorSchemes = [
                                                'primary' => ['bg' => 'bg-primary', 'text' => 'text-white'],
                                                'success' => ['bg' => 'bg-success', 'text' => 'text-white'],
                                                'info' => ['bg' => 'bg-info', 'text' => 'text-white'],
                                                'warning' => ['bg' => 'bg-warning', 'text' => 'text-dark'],
                                                'danger' => ['bg' => 'bg-danger', 'text' => 'text-white'],
                                                'purple' => ['bg' => 'bg-purple', 'text' => 'text-white'],
                                                'pink' => ['bg' => 'bg-pink', 'text' => 'text-white'],
                                                'teal' => ['bg' => 'bg-teal', 'text' => 'text-white'],
                                                'orange' => ['bg' => 'bg-orange', 'text' => 'text-white'],
                                                'indigo' => ['bg' => 'bg-indigo', 'text' => 'text-white']
                                            ];

                                            $colorKeys = array_keys($colorSchemes);
                                            $colorIndex = crc32($appName) % count($colorKeys);
                                            $selectedColor = $colorKeys[$colorIndex];
                                            $iconBg = $colorSchemes[$selectedColor]['bg'];
                                            $iconTextColor = $colorSchemes[$selectedColor]['text'];

                                            // Determine app category/type based on package name or keywords
                                            $packageLower = strtolower($packageName);
                                            $appType = 'general';
                                            $typeIcon = 'cube';

                                            if (strpos($packageLower, 'com.google') !== false) {
                                                $appType = 'google';
                                                $typeIcon = 'google';
                                            } elseif (strpos($packageLower, 'com.facebook') !== false ||
                                                strpos($packageLower, 'com.instagram') !== false ||
                                                strpos($packageLower, 'com.twitter') !== false) {
                                                $appType = 'social';
                                                $typeIcon = 'users';
                                            } elseif (strpos($packageLower, 'com.android') !== false) {
                                                $appType = 'system';
                                                $typeIcon = 'cogs';
                                            } elseif (strpos($packageLower, 'game') !== false ||
                                                strpos($appName, 'Game') !== false) {
                                                $appType = 'game';
                                                $typeIcon = 'gamepad';
                                            } elseif (strpos($packageLower, 'music') !== false ||
                                                strpos($appName, 'Music') !== false ||
                                                strpos($appName, 'Player') !== false) {
                                                $appType = 'media';
                                                $typeIcon = 'music';
                                            } elseif (strpos($packageLower, 'camera') !== false ||
                                                strpos($appName, 'Camera') !== false ||
                                                strpos($appName, 'Photo') !== false) {
                                                $appType = 'camera';
                                                $typeIcon = 'camera';
                                            }

                                            // Check if it's a system app
                                            $isSystem = $app['is_system_app'] ?? 0;
                                            $status = $isSystem ? 'System' : 'Active';

                                            $statusConfig = [
                                                'Active' => [
                                                    'icon' => 'check-circle',
                                                    'color' => 'success',
                                                    'bg' => 'bg-success-light',
                                                    'text' => 'text-success'
                                                ],
                                                'System' => [
                                                    'icon' => 'shield-alt',
                                                    'color' => 'info',
                                                    'bg' => 'bg-info-light',
                                                    'text' => 'text-info'
                                                ]
                                            ];

                                            $statusInfo = $statusConfig[$status] ?? $statusConfig['Active'];
                                            ?>

                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3">
                                                            <div class="avatar-circle-sm <?php echo $iconBg; ?> <?php echo $iconTextColor; ?> shadow-sm">
                                                                <?php echo $firstLetter; ?>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="text-dark font-weight-bold"><?php echo htmlspecialchars($appName); ?></div>
                                                            <small class="text-muted">
                                                                <i class="fas fa-<?php echo $typeIcon; ?> mr-1 text-primary"></i>
                                                                <?php echo ucfirst($appType); ?> App
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="package-name">
                                                        <code class="text-dark bg-light p-2 rounded d-inline-block">
                                                            <?php echo htmlspecialchars($packageName); ?>
                                                        </code>
                                                        <div class="mt-1">
                                                            <small class="text-muted">
                                                                <i class="fas fa-info-circle mr-1"></i>
                                                                Package identifier
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $statusInfo['bg']; ?> <?php echo $statusInfo['text']; ?> p-2">
                                                        <i class="fas fa-<?php echo $statusInfo['icon']; ?> mr-1"></i>
                                                        <?php echo $status; ?>
                                                    </span>
                                                    <?php if ($versionName): ?>
                                                        <div class="mt-1">
                                                            <small class="text-muted">v<?php echo htmlspecialchars($versionName); ?></small>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="app-code">
                                                        <span class="badge badge-light border p-2 font-monospace">
                                                            <i class="fas fa-hashtag mr-1 text-secondary"></i>
                                                            <?php echo htmlspecialchars($versionCode); ?>
                                                        </span>
                                                        <div class="mt-1">
                                                            <small class="text-muted">Version Code</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-app"
                                                            data-id="<?php echo $app['ID'] ?? $app['counter'] ?? ''; ?>"
                                                            data-name="<?php echo htmlspecialchars($appName); ?>"
                                                            title="Delete app entry">
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
                                        Showing 1 to <?php echo count($apps_dump) ?>
                                        of <?php echo $totalApps ?> entries
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="float-right">
                                        <?php if (isset($pager) && $totalApps > $perPage): ?>
                                            <?php echo $pager->links('bootstrap5_full', 'bootstrap5_full'); ?>
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

    $(document).on('click', '.delete-app', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        Swal.fire({
            title: 'Delete App Entry?',
            text: 'Are you sure you want to delete "' + name + '"?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Cancel',
            confirmButtonText: '<i class="fas fa-trash"></i> Delete'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url('apps/delete/' + id),
                    type: 'POST',
                    success: function(response) {
                        Swal.fire('Deleted!', 'App entry has been deleted.', 'success').then(function() {
                            location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to delete app entry.', 'error');
                    }
                });
            }
        });
    });
});
</script>
</div>
<!-- /.content-wrapper -->
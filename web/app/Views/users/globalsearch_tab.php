<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center">
                        <h1 class="h2 mb-0">
                            <i class="<?= esc($tabIcon) ?> text-primary mr-2"></i>
                            <?= esc($tabLabel) ?> Results
                        </h1>
                        <div class="ml-3">
                            <span class="badge badge-light border p-2">
                                Query: <b>"<?= esc($query) ?>"</b>
                            </span>
                            <span class="badge badge-<?= esc($tabBadge) ?> border p-2 ml-2">
                                <?= esc($tabLabel) ?>: <b><?= $totalRows ?></b>
                            </span>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">Showing results for "<b><?= esc($query) ?>"</b> across all categories</p>
                </div>
                <div class="col-lg-4 col-md-6">
                    <nav aria-label="breadcrumb" class="float-right mt-2">
                        <ol class="breadcrumb bg-transparent p-0 mb-0">
                            <li class="breadcrumb-item"><a href="<?= base_url('home') ?>"><i class="fas fa-home mr-1"></i>Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('globalsearch?q=' . urlencode($query)) ?>">Search</a></li>
                            <li class="breadcrumb-item active"><?= esc($tabLabel) ?></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <?php if ($totalCount == 0): ?>
                <div class="alert alert-warning shadow-sm border-0">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    No results found for "<b><?= esc($query) ?></b>". Try searching with different keywords or numbers.
                </div>
            <?php else: ?>

                <!-- Stat Cards Row -->
                <div class="row">
                    <?php foreach ($counts as $key => $count): ?>
                        <?php $tab = $tabs[$key]; ?>
                        <div class="col-lg-2 col-md-3 col-6">
                            <div class="small-box bg-<?= $tab['color'] ?>">
                                <div class="inner">
                                    <h3><?= $count ?></h3>
                                    <p><?= $tab['label'] ?></p>
                                </div>
                                <div class="icon">
                                    <i class="<?= $tab['icon'] ?>"></i>
                                </div>
                                <?php if ($count > 0): ?>
                                    <a href="<?= base_url('globalsearch/' . $key . '?q=' . urlencode($query)) ?>" class="small-box-footer">
                                        View All <i class="fas fa-arrow-circle-right"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="small-box-footer">
                                        No results <i class="fas fa-arrow-circle-right"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Tab Navigation Pills -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card card-outline card-primary shadow-sm">
                            <div class="card-body py-2">
                                <ul class="nav nav-pills flex-wrap">
                                    <?php foreach ($tabs as $key => $tab): ?>
                                        <?php if ($counts[$key] > 0): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?= ($activeTab === $key) ? 'active' : '' ?>"
                                                   href="<?= base_url('globalsearch/' . $key . '?q=' . urlencode($query)) ?>">
                                                    <i class="<?= $tab['icon'] ?> mr-1"></i>
                                                    <?= $tab['label'] ?>
                                                    <span class="badge badge-<?= $tab['badge'] ?> ml-1"><?= $counts[$key] ?></span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Table -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card card-outline card-<?= esc($tabBadge) ?> shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="<?= esc($tabIcon) ?> mr-2"></i>
                                    <?= esc($tabLabel) ?> Results
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
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped mb-0 table-search-results">
                                        <thead class="thead-light">
                                            <tr>
                                                <?php if ($activeTab === 'sms'): ?>
                                                    <th>Number</th>
                                                    <th>Message</th>
                                                    <th>Date</th>
                                                    <th>Type</th>
                                                <?php elseif ($activeTab === 'calls'): ?>
                                                    <th>Name</th>
                                                    <th>Number</th>
                                                    <th>Date</th>
                                                    <th>Type</th>
                                                    <th>Duration</th>
                                                <?php elseif ($activeTab === 'contacts'): ?>
                                                    <th>Name</th>
                                                    <th>Phone Numbers</th>
                                                    <th>Last Contacted</th>
                                                <?php elseif ($activeTab === 'files'): ?>
                                                    <th>File Name</th>
                                                    <th>Path</th>
                                                    <th>Size</th>
                                                    <th>Type</th>
                                                    <th>Modified</th>
                                                <?php elseif ($activeTab === 'apps'): ?>
                                                    <th>App</th>
                                                    <th>Package</th>
                                                    <th>Version</th>
                                                    <th>Size</th>
                                                    <th>Type</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($rows)): ?>
                                                <tr>
                                                    <td colspan="100%" class="text-center py-5">
                                                        <div class="empty-state">
                                                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                                            <h4>No <?= esc($tabLabel) ?> results</h4>
                                                            <p class="text-muted">No matching records found for this category.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($rows as $row): ?>
                                                    <tr>
                                                        <?php if ($activeTab === 'sms'): ?>
                                                            <td class="font-weight-bold"><?= esc($row['Number']) ?></td>
                                                            <td style="max-width:400px;"><?= esc($row['Message']) ?></td>
                                                            <td><span class="text-muted"><?= date('M d, Y H:i', $row['Date'] / 1000) ?></span></td>
                                                            <td><span class="badge badge-<?= $row['Type'] == 'sent' ? 'success' : 'info' ?>"><?= ucfirst($row['Type']) ?></span></td>
                                                        <?php elseif ($activeTab === 'calls'): ?>
                                                            <td class="font-weight-bold"><?= esc($row['Name'] ?: 'Unknown') ?></td>
                                                            <td><?= esc($row['Number']) ?></td>
                                                            <td><span class="text-muted"><?= date('M d, Y H:i', $row['Date'] / 1000) ?></span></td>
                                                            <td><span class="badge badge-light border font-weight-normal"><?= ucfirst($row['Type']) ?></span></td>
                                                            <td><?= gmdate("H:i:s", $row['Duration']) ?></td>
                                                        <?php elseif ($activeTab === 'contacts'): ?>
                                                            <td class="font-weight-bold"><?= esc($row['Name']) ?></td>
                                                            <td>
                                                                <?php
                                                                    $phones = json_decode($row['Number'], true);
                                                                    echo !empty($phones) ? implode(', ', $phones) : 'N/A';
                                                                ?>
                                                            </td>
                                                            <td><span class="text-muted"><?= $row['last_contacted'] > 0 ? date('M d, Y H:i', $row['last_contacted'] / 1000) : 'Never' ?></span></td>
                                                        <?php elseif ($activeTab === 'files'): ?>
                                                            <td class="font-weight-bold"><?= esc($row['file_name']) ?></td>
                                                            <td class="text-sm" title="<?= esc($row['file_path']) ?>"><?= esc(substr($row['file_path'], 0, 50)) ?>...</td>
                                                            <td><?= number_format($row['file_size'] / 1024, 2) ?> KB</td>
                                                            <td><span class="badge badge-light"><?= esc($row['file_type']) ?></span></td>
                                                            <td class="text-muted"><?= date('M d, Y', $row['last_modified'] / 1000) ?></td>
                                                        <?php elseif ($activeTab === 'apps'): ?>
                                                            <td class="font-weight-bold">
                                                                <?php if (!empty($row['app_icon'])): ?>
                                                                    <img src="data:image/png;base64,<?= esc($row['app_icon']) ?>" class="rounded-circle mr-2" style="width:24px;height:24px;" onerror="this.style.display='none';">
                                                                <?php endif; ?>
                                                                <?= esc($row['Name']) ?>
                                                            </td>
                                                            <td class="text-sm"><?= esc($row['Package']) ?></td>
                                                            <td><?= esc($row['Version'] ?: 'N/A') ?></td>
                                                            <td><?= $row['AppSize'] > 0 ? number_format($row['AppSize'] / 1048576, 2) . ' MB' : '—' ?></td>
                                                            <td>
                                                                <span class="badge badge-<?= $row['IsSystem'] ? 'success' : 'info' ?>">
                                                                    <?= $row['IsSystem'] ? 'System' : 'User' ?>
                                                                </span>
                                                            </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Card Footer with Pagination -->
                            <?php if ($totalRows > 0): ?>
                            <div class="card-footer clearfix">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="entry-info">
                                            Showing <?= (($currentPage - 1) * $perPage) + 1 ?>
                                            to <?= min($currentPage * $perPage, $totalRows) ?>
                                            of <?= $totalRows ?> entries
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="float-right">
                                            <?= $pager->links('default', 'bootstrap5_full') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<script>
    $(function () {
        // Initialize DataTable without search box (no 'f' in dom) and without buttons
        $('.table-search-results').DataTable({
            "paging": false,
            "lengthChange": false,
            "info": true,
            "autoWidth": false,
            "order": [],
            "dom": 'rt<"row"<"col-sm-6"i><"col-sm-6"p>>'
        });

        // PDF Export from card-tools
        $('#pdfExport').on('click', function () {
            var element = document.querySelector('.table-search-results');
            if (!element) return;
            Swal.fire({
                title: 'Generating PDF...',
                text: 'Please wait while we prepare your document',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            html2pdf().set({
                margin:       10,
                filename:     'export_' + Date.now() + '.pdf',
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
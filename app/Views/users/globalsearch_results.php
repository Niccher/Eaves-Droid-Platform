    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-8 col-md-6">
                        <div class="d-flex align-items-center">
                            <h1 class="h2 mb-0">
                                <i class="fas fa-search text-primary mr-2"></i>
                                Search Results
                            </h1>
                            <div class="ml-3">
                                <span class="badge badge-light border p-2">
                                    Query: <b>"<?= esc($query) ?>"</b>
                                </span>
                                <span class="badge badge-info border p-2 ml-2">
                                    Found: <b><?= $totalCount ?></b> matches
                                </span>
                            </div>
                        </div>
                        <p class="text-muted mt-2 mb-0">Showing universal search results across all data categories</p>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <nav aria-label="breadcrumb" class="float-right mt-2">
                            <ol class="breadcrumb bg-transparent p-0 mb-0">
                                <li class="breadcrumb-item"><a href="<?= base_url('home') ?>"><i class="fas fa-home mr-1"></i>Home</a></li>
                                <li class="breadcrumb-item active">Search</li>
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
                    
                    <!-- SMS Results -->
                    <?php if (!empty($results['sms'])): ?>
                    <div class="card card-outline card-danger shadow-sm mb-4">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-sms mr-2"></i>
                                SMS Messages (<?= count($results['sms']) ?>)
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 tabledump">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Number</th>
                                            <th>Message</th>
                                            <th>Date</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results['sms'] as $sms): ?>
                                        <tr>
                                            <td class="font-weight-bold"><?= esc($sms['Number']) ?></td>
                                            <td style="max-width: 400px;"><?= esc($sms['Message']) ?></td>
                                            <td><span class="text-muted"><?= date('M d, Y H:i', $sms['Date'] / 1000) ?></span></td>
                                            <td><span class="badge badge-<?= $sms['Type'] == 'sent' ? 'success' : 'info' ?>"><?= ucfirst($sms['Type']) ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Call Log Results -->
                    <?php if (!empty($results['calls'])): ?>
                    <div class="card card-outline card-success shadow-sm mb-4">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-phone-alt mr-2"></i>
                                Call Logs (<?= count($results['calls']) ?>)
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 tabledump">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Number</th>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results['calls'] as $call): ?>
                                        <tr>
                                            <td class="font-weight-bold"><?= esc($call['Name'] ?: 'Unknown') ?></td>
                                            <td><?= esc($call['Number']) ?></td>
                                            <td><span class="text-muted"><?= date('M d, Y H:i', $call['Date'] / 1000) ?></span></td>
                                            <td><span class="badge badge-light border font-weight-normal"><?= ucfirst($call['Type']) ?></span></td>
                                            <td><?= gmdate("H:i:s", $call['Duration']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Contact Results -->
                    <?php if (!empty($results['contacts'])): ?>
                    <div class="card card-outline card-warning shadow-sm mb-4">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-id-card mr-2"></i>
                                Contacts (<?= count($results['contacts']) ?>)
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 tabledump">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Phone Numbers</th>
                                            <th>Last Contacted</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results['contacts'] as $contact): ?>
                                        <tr>
                                            <td class="font-weight-bold"><?= esc($contact['Name']) ?></td>
                                            <td>
                                                <?php 
                                                    $phones = json_decode($contact['Number'], true); 
                                                    echo !empty($phones) ? implode(', ', $phones) : 'N/A';
                                                ?>
                                            </td>
                                            <td><span class="text-muted"><?= $contact['last_contacted'] > 0 ? date('M d, Y H:i', $contact['last_contacted'] / 1000) : 'Never' ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- File Results -->
                    <?php if (!empty($results['files'])): ?>
                    <div class="card card-outline card-secondary shadow-sm mb-4">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file mr-2"></i>
                                FilesController (<?= count($results['files']) ?>)
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 tabledump">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>File Name</th>
                                            <th>Path</th>
                                            <th>Size</th>
                                            <th>Type</th>
                                            <th>Modified</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results['files'] as $file): ?>
                                        <tr>
                                            <td class="font-weight-bold"><?= esc($file['file_name']) ?></td>
                                            <td class="text-sm" title="<?= esc($file['file_path']) ?>"><?= esc(substr($file['file_path'], 0, 50)) ?>...</td>
                                            <td><?= number_format($file['file_size'] / 1024, 2) ?> KB</td>
                                            <td><span class="badge badge-light"><?= esc($file['file_type']) ?></span></td>
                                            <td class="text-muted"><?= date('M d, Y', $file['last_modified'] / 1000) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- App Results -->
                    <?php if (!empty($results['apps'])): ?>
                    <div class="card card-outline card-info shadow-sm mb-4">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fab fa-android mr-2"></i>
                                Apps (<?= count($results['apps']) ?>)
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 tabledump">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>App</th>
                                            <th>Package</th>
                                            <th>Version</th>
                                            <th>Size</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results['apps'] as $app): ?>
                                        <tr>
                                            <td class="font-weight-bold">
                                                <?php if (!empty($app['app_icon'])): ?>
                                                    <img src="data:image/png;base64,<?= esc($app['app_icon']) ?>" class="rounded-circle mr-2" style="width:24px;height:24px;" onerror="this.style.display='none';">
                                                <?php endif; ?>
                                                <?= esc($app['Name']) ?>
                                            </td>
                                            <td class="text-sm"><?= esc($app['Package']) ?></td>
                                            <td><?= esc($app['Version'] ?: 'N/A') ?></td>
                                            <td><?= $app['AppSize'] > 0 ? number_format($app['AppSize'] / 1048576, 2) . ' MB' : '—' ?></td>
                                            <td>
                                                <span class="badge badge-<?= $app['IsSystem'] ? 'success' : 'info' ?>">
                                                    <?= $app['IsSystem'] ? 'System' : 'User' ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                <?php endif; ?>

            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

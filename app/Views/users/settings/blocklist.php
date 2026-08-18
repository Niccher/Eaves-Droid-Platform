<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-ban text-danger mr-2"></i> Data Blocklist</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Blocklist</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            
            <?php if(session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-check"></i> Success!</h5>
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>
            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-ban"></i> Error!</h5>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <!-- Explanation / Help Banner -->
            <div class="card card-outline card-info shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h3 class="card-title text-info font-weight-bold">
                        <i class="fas fa-shield-alt mr-2"></i> ClientController Privacy Filter & Data Blocklist
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-9">
                            <p class="lead text-dark mb-3" style="font-size: 1.1rem;">
                                The <strong>Data Blocklist</strong> allows you to block specific identifiers from rendering on this platform. When you add a rule below, matching records are dynamically filtered out of all listings, charts, feeds, and analytics in real-time.
                            </p>
                            <div class="row">
                                <div class="col-sm-6 mb-2">
                                    <i class="fas fa-eye-slash text-danger mr-2"></i><strong>Hidden From View:</strong> Matching records are omitted from the dashboard, SMS list, call logs, app metrics, notifications, and intelligence feeds.
                                </div>
                                <div class="col-sm-6 mb-2">
                                    <i class="fas fa-database text-success mr-2"></i><strong>Data Integrity:</strong> Blocklist filters only affect visualization in the web interface; the underlying data remains intact.
                                </div>
                                <div class="col-sm-6 mb-2">
                                    <i class="fas fa-history text-info mr-2"></i><strong>Instant Unblocking:</strong> Click "Unblock" at any time to instantly restore the matching data to all listings.
                                </div>
                                <div class="col-sm-6 mb-2">
                                    <i class="fas fa-filter text-primary mr-2"></i><strong>Accurate Filtering:</strong> Prevents specific personal numbers or sensitive packages from showing up in public reports.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mt-3 mt-md-0 border-left d-none d-md-block">
                            <div class="p-3">
                                <h6 class="text-uppercase text-muted font-weight-bold mb-2" style="font-size: 0.8rem; letter-spacing: 0.5px;">Active Rules Count</h6>
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <span class="text-xs text-muted d-block">SMS</span>
                                        <span class="badge badge-info px-2 py-1" style="font-size: 0.9rem;"><?= count($blocks['sms'] ?? []) ?></span>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <span class="text-xs text-muted d-block">Calls</span>
                                        <span class="badge badge-success px-2 py-1" style="font-size: 0.9rem;"><?= count($blocks['call'] ?? []) ?></span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-xs text-muted d-block">Notifs</span>
                                        <span class="badge badge-danger px-2 py-1" style="font-size: 0.9rem;"><?= count($blocks['notification'] ?? []) ?></span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-xs text-muted d-block">Usage</span>
                                        <span class="badge badge-warning px-2 py-1" style="font-size: 0.9rem;"><?= count($blocks['app_usage'] ?? []) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card card-primary card-outline card-outline-tabs shadow-sm">
                        <div class="card-header p-0 border-bottom-0">
                            <ul class="nav nav-tabs" id="blocklist-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="tab-sms" data-toggle="tab" href="#content-sms" role="tab">
                                        <i class="fas fa-sms mr-1"></i> SMS Senders
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-call" data-toggle="tab" href="#content-call" role="tab">
                                        <i class="fas fa-phone mr-1"></i> Call Numbers
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-notification" data-toggle="tab" href="#content-notification" role="tab">
                                        <i class="fas fa-bell mr-1"></i> App Notifications
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-app_usage" data-toggle="tab" href="#content-app_usage" role="tab">
                                        <i class="fas fa-mobile-alt mr-1"></i> App Usage
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="blocklist-tabs-content">

                                <?php 
                                $tabs = [
                                    [
                                        'id' => 'sms',
                                        'name' => 'SMS Sender',
                                        'icon' => 'fas fa-sms',
                                        'hint' => 'e.g., Safaricom, KCB, +123456789',
                                        'desc' => 'Blocks any SMS messages where the sender name or phone number matches this identifier. Matched entries will be omitted from SMS tables, chat bubbles, global search results, and financial parsing dashboards.',
                                        'match_type' => 'Exact String Match (Case-Insensitive)'
                                    ],
                                    [
                                        'id' => 'call',
                                        'name' => 'Phone Number',
                                        'icon' => 'fas fa-phone',
                                        'hint' => 'e.g., +123456789',
                                        'desc' => 'Hides all call log records associated with this phone number. It filters out matching incoming, outgoing, missed, or rejected calls from listings, dashboard metrics, and intelligence reports.',
                                        'match_type' => 'Exact Phone Number String'
                                    ],
                                    [
                                        'id' => 'notification',
                                        'name' => 'Notification Package',
                                        'icon' => 'fas fa-bell',
                                        'hint' => 'e.g., com.whatsapp',
                                        'desc' => 'Prevents device notification history from being displayed for the specified Android package ID (e.g., WhatsApp, Messenger). Useful for filtering out sensitive message previews from dashboards.',
                                        'match_type' => 'Android Package ID (e.g., com.example.app)'
                                    ],
                                    [
                                        'id' => 'app_usage',
                                        'name' => 'App Usage Package',
                                        'icon' => 'fas fa-mobile-alt',
                                        'hint' => 'e.g., com.facebook.katana',
                                        'desc' => 'Omits application launch activities and daily/weekly duration statistics for the matching package identifier. The app will not appear in the Digital Wellbeing reports, app usage timelines, or analytics.',
                                        'match_type' => 'Android Package ID (e.g., com.example.app)'
                                    ]
                                ];
                                
                                $first = true;
                                foreach($tabs as $tab): 
                                    $cat        = $tab['id'];
                                    $blockItems = (isset($blocks[$cat]) && is_array($blocks[$cat])) ? $blocks[$cat] : [];
                                ?>
                                <div class="tab-pane fade <?= $first ? 'show active' : '' ?>"
                                     id="content-<?= $cat ?>"
                                     role="tabpanel"
                                     aria-labelledby="tab-<?= $cat ?>">

                                    <!-- Help Info Alert for specific Tab -->
                                    <div class="alert alert-light border shadow-sm mb-3 mt-3">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <i class="<?= $tab['icon'] ?> fa-2x text-muted"></i>
                                            </div>
                                            <div class="col">
                                                <strong class="text-dark">How this filter works:</strong> <span class="text-secondary"><?= $tab['desc'] ?></span>
                                                <div class="mt-1">
                                                    <span class="text-xs text-muted"><i class="fas fa-search-plus mr-1"></i> Match Rule: <strong><?= $tab['match_type'] ?></strong></span>
                                                    <span class="mx-2 text-muted">|</span>
                                                    <span class="text-xs text-muted"><i class="fas fa-info-circle mr-1"></i> Example input: <code><?= $tab['hint'] ?></code></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Add form -->
                                    <div class="card card-light card-outline">
                                        <div class="card-header">
                                            <h3 class="card-title"><i class="fas fa-plus-circle mr-1 text-primary"></i> Add <?= $tab['name'] ?> Block</h3>
                                        </div>
                                        <div class="card-body">
                                            <form action="<?= base_url('analysis/blocklist/add') ?>" method="POST" class="form-row align-items-end">
                                                <input type="hidden" name="category" value="<?= $cat ?>">
                                                <div class="col-md-5 form-group mb-0">
                                                    <label class="text-muted text-sm"><?= $tab['name'] ?> to Block</label>
                                                    <input type="text" name="identifier" class="form-control" placeholder="<?= $tab['hint'] ?>" required>
                                                </div>
                                                <div class="col-md-5 form-group mb-0">
                                                    <label class="text-muted text-sm">Reason (Optional)</label>
                                                    <input type="text" name="description" class="form-control" placeholder="Why are you blocking this?">
                                                </div>
                                                <div class="col-md-2 form-group mb-0">
                                                    <button type="submit" class="btn btn-primary btn-block">
                                                        <i class="fas fa-ban mr-1"></i> Block
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Blocked items table -->
                                    <div class="card card-light card-outline">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-list mr-1 text-danger"></i>
                                                Blocked <?= $tab['name'] ?>s
                                                <span class="badge badge-danger ml-2"><?= count($blockItems) ?></span>
                                            </h3>
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table table-hover table-sm mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Blocked <?= $tab['name'] ?></th>
                                                        <th>Description</th>
                                                        <th>Blocked On</th>
                                                        <th class="text-right">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if(empty($blockItems)): ?>
                                                        <tr>
                                                            <td colspan="4" class="text-center text-muted py-4">
                                                                <i class="fas fa-info-circle mr-1"></i> No blocks active for this category.
                                                            </td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach($blockItems as $b): ?>
                                                        <tr>
                                                            <td class="align-middle">
                                                                <span class="badge badge-secondary px-2 py-1"><?= esc($b['identifier']) ?></span>
                                                            </td>
                                                            <td class="text-muted align-middle"><?= esc($b['description'] ?? '—') ?></td>
                                                            <td class="text-muted align-middle"><?= date('M d, Y', strtotime($b['created_at'])) ?></td>
                                                            <td class="text-right align-middle">
                                                                <form action="<?= base_url('analysis/blocklist/delete/'.$b['id']) ?>" method="POST" style="display:inline;">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                        <i class="fas fa-trash-alt mr-1"></i> Unblock
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                                <?php 
                                $first = false;
                                endforeach; 
                                ?>

                            </div><!-- /.tab-content -->
                        </div><!-- /.card-body -->
                    </div><!-- /.card -->
                </div>
            </div>

        </div>
    </section>
</div>

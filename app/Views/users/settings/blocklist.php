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

            <div class="row">
                <div class="col-12">
                    <div class="card card-primary card-outline card-outline-tabs">
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
                                    ['id' => 'sms',          'name' => 'SMS Sender',           'hint' => 'e.g., Safaricom, KCB, +123456789'],
                                    ['id' => 'call',         'name' => 'Phone Number',          'hint' => 'e.g., +123456789'],
                                    ['id' => 'notification', 'name' => 'Notification Package',  'hint' => 'e.g., com.whatsapp'],
                                    ['id' => 'app_usage',    'name' => 'App Usage Package',     'hint' => 'e.g., com.facebook.katana']
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

                                    <!-- Add form -->
                                    <div class="card card-light card-outline mt-3">
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

<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-bullhorn mr-2 text-danger"></i>System Broadcasts</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item active">Broadcasts</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <?php if (session()->getFlashdata('message')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i><?= session()->getFlashdata('message') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i><?= session()->getFlashdata('error') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Compose New Broadcast -->
                <div class="col-lg-5 mb-4">
                    <div class="card card-outline card-danger shadow-sm h-100">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title font-weight-bold mb-0 text-dark">
                                <i class="fas fa-paper-plane mr-2 text-danger"></i>Compose Broadcast
                            </h5>
                        </div>
                        <form action="<?= base_url('admin/broadcasts/send') ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="broadcastTitle" class="font-weight-bold">Broadcast Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="broadcastTitle" class="form-control" placeholder="e.g. Scheduled System Maintenance" required>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="broadcastType" class="font-weight-bold">Severity / Style</label>
                                        <select name="type" id="broadcastType" class="form-control">
                                            <option value="info">Info (Blue)</option>
                                            <option value="warning">Warning (Yellow)</option>
                                            <option value="danger">Critical / Alert (Red)</option>
                                            <option value="success">Success / Update (Green)</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="broadcastTarget" class="font-weight-bold">Target Audience</label>
                                        <select name="target" id="broadcastTarget" class="form-control">
                                            <option value="all">All Users &amp; Admins</option>
                                            <option value="users">Standard Users Only</option>
                                            <option value="admins">Admins Only</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="broadcastMessage" class="font-weight-bold">Message Content <span class="text-danger">*</span></label>
                                    <textarea name="message" id="broadcastMessage" class="form-control" rows="4" placeholder="Write your broadcast announcement message here..." required></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="broadcastExpires" class="font-weight-bold">Expires At (Optional)</label>
                                    <input type="datetime-local" name="expires_at" id="broadcastExpires" class="form-control">
                                    <small class="form-text text-muted">Leave blank for indefinite announcement.</small>
                                </div>
                            </div>
                            <div class="card-footer bg-light text-right">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-bullhorn mr-1"></i> Publish Broadcast
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Broadcasts History List -->
                <div class="col-lg-7 mb-4">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="card-title font-weight-bold mb-0 text-dark">
                                <i class="fas fa-history mr-2 text-secondary"></i>Published Broadcasts
                            </h5>
                            <span class="badge badge-pill badge-secondary"><?= count($broadcasts) ?> Total</span>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($broadcasts)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-bullhorn fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No broadcasts published yet</h5>
                                    <p class="text-muted small">Use the form on the left to send an announcement across the system.</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($broadcasts as $bc): ?>
                                        <?php
                                            $typeClass = match($bc['type'] ?? 'info') {
                                                'danger'  => 'badge-danger',
                                                'warning' => 'badge-warning',
                                                'success' => 'badge-success',
                                                default   => 'badge-info',
                                            };
                                            $isActive = $bc['active'] ?? true;
                                        ?>
                                        <div class="list-group-item p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <span class="badge <?= $typeClass ?> text-uppercase mr-2"><?= esc($bc['type'] ?? 'info') ?></span>
                                                    <strong class="text-dark"><?= esc($bc['title']) ?></strong>
                                                    <?php if ($isActive): ?>
                                                        <span class="badge badge-success ml-2">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary ml-2">Inactive</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-right">
                                                    <a href="<?= base_url('admin/broadcasts/toggle/' . $bc['id']) ?>" class="btn btn-xs <?= $isActive ? 'btn-outline-warning' : 'btn-outline-success' ?> mr-1" title="<?= $isActive ? 'Deactivate' : 'Activate' ?>">
                                                        <i class="fas <?= $isActive ? 'fa-pause' : 'fa-play' ?>"></i>
                                                    </a>
                                                    <a href="<?= base_url('admin/broadcasts/delete/' . $bc['id']) ?>" class="btn btn-xs btn-outline-danger" onclick="return confirm('Are you sure you want to delete this broadcast?')" title="Delete Broadcast">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <p class="text-muted small mb-2"><?= nl2br(esc($bc['message'])) ?></p>
                                            <div class="small text-muted d-flex justify-content-between border-top pt-2">
                                                <span><i class="fas fa-users mr-1"></i> Target: <strong><?= ucfirst(esc($bc['target'] ?? 'all')) ?></strong></span>
                                                <span><i class="fas fa-user-edit mr-1"></i> By: <?= esc($bc['created_by'] ?? 'Admin') ?> &bull; <?= date('M d, Y H:i', strtotime($bc['created_at'])) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-headset mr-2 text-primary"></i>Support Tickets & Chat</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item active">Support</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Client Conversations</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body p-0">
                    <?php if (empty($conversations)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No support tickets found</h5>
                            <p class="text-muted small">Clients have not initiated any support requests yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($conversations as $conv): ?>
                                <?php
                                $avatar = $conv['profile_image'] ? base_url('uploads/profiles/' . $conv['profile_image']) : null;
                                $unread = (int)$conv['unread_count'];
                                $isUnread = $unread > 0;
                                $timeStr = date('M d, Y H:i:s A', strtotime($conv['created_at']));
                                ?>
                                <a href="<?= base_url('admin/support/thread/' . $conv['client_id']) ?>" 
                                   class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 <?= $isUnread ? 'bg-light font-weight-bold' : '' ?>">
                                    <div class="d-flex align-items-center overflow-hidden">
                                        <?php if ($avatar): ?>
                                            <img src="<?= $avatar ?>" 
                                                 class="rounded-circle border mr-3" 
                                                 style="width: 48px; height: 48px; object-fit: cover;" 
                                                 alt="Avatar">
                                        <?php else: ?>
                                            <?php
                                            $colors = ['#f56954', '#f39c12', '#0073b7', '#00c0ef', '#00a65a', '#3c8dbc', '#39cccc', '#605ca8', '#ff851b'];
                                            $colorIndex = abs(crc32($conv['username'])) % count($colors);
                                            $avatarColor = $colors[$colorIndex];
                                            $initials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $conv['username']), 0, 2));
                                            if (empty($initials)) {
                                                $initials = 'UD';
                                            }
                                            ?>
                                            <div class="rounded-circle border mr-3 d-flex align-items-center justify-content-center text-white font-weight-bold text-uppercase" 
                                                 style="background-color: <?= $avatarColor ?>; width: 48px; height: 48px; font-size: 16px; min-width: 48px; display: flex !important;">
                                                <?= esc($initials) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="text-truncate">
                                            <div class="d-flex align-items-center">
                                                <h6 class="mb-0 text-dark font-weight-bold">
                                                    <?= esc(ucwords($conv['username'])) ?>
                                                </h6>
                                                <small class="text-muted ml-2">#<?= esc($conv['client_id']) ?></small>
                                            </div>
                                            <p class="mb-0 text-muted small text-truncate" style="max-width: 450px;">
                                                <?php if (!empty($conv['message'])): ?>
                                                    <?= esc($conv['message']) ?>
                                                <?php else: ?>
                                                    <span class="text-success"><i class="fas fa-file-image mr-1"></i> Sent an image attachment</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right ml-3 flex-shrink-0">
                                        <small class="text-muted d-block mb-1"><?= $timeStr ?></small>
                                        <?php if ($isUnread): ?>
                                            <span class="badge badge-danger badge-pill py-1 px-2"><?= $unread ?> unread</span>
                                        <?php else: ?>
                                            <span class="badge badge-light border badge-pill py-1 px-2 text-muted">Read</span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </section>
</div>

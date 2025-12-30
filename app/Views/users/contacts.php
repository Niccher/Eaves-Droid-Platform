    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-8 col-md-6">
                        <div class="d-flex align-items-center">
                            <h1 class="h2 mb-0">
                                <i class="fas fa-address-book text-primary mr-2"></i>
                                <?php echo ucfirst($pag) ?? 'Saved Contacts' ?>
                            </h1>
                            <div class="ml-3">
                                <span class="badge badge-light border p-2">
                                    <i class="fas fa-users text-primary mr-1"></i>
                                    Total: <b><?php echo $totalContacts ?? 0 ?></b>
                                </span>
                            </div>
                        </div>
                        <p class="text-muted mt-2 mb-0">Manage your saved contacts and their communication history</p>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="float-right mt-2">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0">
                                    <li class="breadcrumb-item">
                                        <a href="<?= base_url("home") ?>">
                                            <i class="fas fa-home"></i> Home
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item active">Contacts</li>
                                </ol>
                            </nav>
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
                                    <i class="fas fa-users mr-2"></i>
                                    Contact List
                                    <small class="text-muted ml-2">Showing <?php echo count($contacts_dump) ?> of <?php echo $totalContacts ?? 0 ?> contacts</small>
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
                                    <table class="table table-hover table-striped mb-0">
                                        <thead class="thead-light">
                                        <tr>
                                            <th width="35%">Contact</th>
                                            <th width="20%">Phone Number</th>
                                            <th width="15%">Contact ID</th>
                                            <th width="30%">Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (empty($contacts_dump)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-5">
                                                    <div class="empty-state">
                                                        <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                                                        <h4>No contacts found</h4>
                                                        <p class="text-muted">Your saved contacts will appear here</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php
                                            $encrypter = model('Mod_Crypt');
                                            foreach ($contacts_dump as $contact):
                                                $id = $encrypter->var_crypt($contact['ID'], "encrypt");
                                                $enc_id = $encrypter->base64url_encode($id);

                                                // Determine contact avatar background color based on name
                                                $initial = strtoupper(substr($contact['Name'], 0, 1));
                                                $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
                                                $colorIndex = ord($initial) % count($colors);
                                                $avatarColor = $colors[$colorIndex];
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="mr-3">
                                                                <div class="avatar-circle-sm bg-<?php echo $avatarColor; ?> text-white">
                                                                    <?php echo $initial; ?>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <div class="text-dark font-weight-bold"><?php echo htmlspecialchars($contact['Name']); ?></div>
                                                                <small class="text-muted">ID: <?php echo $contact['ID']; ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-dark font-weight-bold"><?php echo htmlspecialchars($contact['Number']); ?></div>
                                                        <small class="text-muted">
                                                            <i class="fas fa-mobile-alt mr-1"></i> Phone
                                                        </small>
                                                    </td>
                                                    <td>
                                                            <span class="badge bg-light border text-dark p-2">
                                                                <i class="fas fa-hashtag mr-1"></i>
                                                                <?php echo $contact['ID']; ?>
                                                            </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <a href="<?php echo base_url('contacts/analyze/sms/' . $enc_id); ?>"
                                                               class="btn btn-info"
                                                               title="View SMS History">
                                                                <i class="fas fa-comments mr-1"></i> SMS
                                                            </a>
                                                            <a href="<?php echo base_url('contacts/analyze/calls/' . $enc_id); ?>"
                                                               class="btn btn-success"
                                                               title="View Call History">
                                                                <i class="fas fa-phone mr-1"></i> Calls
                                                            </a>
                                                            <a href="<?php echo base_url('contacts/view/' . $enc_id); ?>"
                                                               class="btn btn-outline-primary"
                                                               title="View Contact Details">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </div>
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
                                    </div>
                                    <div class="col-md-6">
                                        <div class="float-right">
                                            <?php if (isset($pager) && $totalContacts > $perPage): ?>
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
    </div>
    <!-- /.content-wrapper -->

    <style>
        .avatar-circle-sm {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
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

        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
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

            .btn-group .btn {
                margin-bottom: 0.25rem;
            }

            .btn-group {
                flex-wrap: wrap;
            }
        }
    </style>
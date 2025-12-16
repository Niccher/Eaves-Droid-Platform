    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle bg-primary text-white mr-3">
                                        <i class="fas fa-phone fa-lg"></i>
                                    </div>
                                    <div>
                                        <h2 class="h4 mb-0"><?php echo ucfirst($pag) ?? 'Saved Contacts' ?></h2>
                                        <p class="text-muted mb-0 mt-1">
                                            <i class="fas fa-mobile mr-1"></i> All Contacts:
                                            <span class="font-weight-bold text-primary"><?php echo $totalContacts ?? 0 ?></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 mt-3 mt-lg-0">
                                <div class="d-flex justify-content-end">
                                    <ol class="breadcrumb float-sm-right">
                                        <li class="breadcrumb-item">
                                            <a href="<?= base_url("home") ?>">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">Contacts</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    .icon-circle {
                        width: 56px;
                        height: 56px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                </style>
            </div>
            <!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <!-- Default box -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <!-- ./card-header -->
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover tabledump">
                                    <thead>
                                    <tr>
                                        <th>Saved</th>
                                        <th>Number</th>
                                        <th>Contact_ID</th>
                                        <th>Analyze</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $encrypter = model('Mod_Crypt');
                                    foreach ($contacts_dump as $contact) {
                                        $id = ($encrypter->var_crypt($contact['ID'], "encrypt"));
                                        $enc_id = ($encrypter->base64url_encode($id));
                                        echo '
                                        <tr data-widget="expandable-table" aria-expanded="false">
                                            <td>' . $contact['Name'] . '</td>
                                            <td>' . $contact['Number'] . '</td>
                                            <td>' . $contact['ID'] . '</td>
                                            <td>
                                                <a href="' . base_url('contacts/analyze/sms/' . $enc_id) . '" class="btn btn-sm bg-teal"><i class="fas fa-comments"></i></a>
                                                <a href="' . base_url('contacts/analyze/calls/' . $enc_id) . '" class="btn btn-sm btn-primary"><i class="fas fa-phone"></i></a>
                                            </td>
                                        </tr>';
                                    }

                                    if (empty($contacts_dump)) {
                                        echo '<tr><td colspan="4" class="text-center">No contacts found.</td></tr>';
                                    }
                                    ?>
                                    </tbody>
                                </table>

                                <br>

                                <div class="d-flex justify-content-end">
                                    <?php if ($totalContacts > count($contacts_dump)) {
                                        echo $pager->links('bootstrap5_full', 'bootstrap5_full');
                                    } ?>
                                </div>
                            </div>

                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Contacts (<b><?php echo count($contacts_dump) ?></b>)</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Contacts</li>
                        </ol>
                    </div>
                </div>
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
                                //$encrypter = \Config\Services::encrypter();
                                $encrypter = model('Mod_Crypt');
                                foreach ($contacts_dump as $contact) {
                                    //$id =  ($encrypter->var_crypt($contact['Contact_ID'], "encrypt"));
                                    $id = ($encrypter->var_crypt($contact['ID'], "encrypt"));
                                    $enc_id = ($encrypter->base64url_encode($id));
                                    //$dec_id =  ($encrypter->base64url_decode($enc_id));
                                    echo '
                                                                        <tr data-widget="expandable-table" aria-expanded="false">
                                                                            <td>' . $contact['Name'] . '</td>
                                                                            <td>' . $contact['Number'] . '</td>
                                                                            <td>' . $contact['ID'] . '</td>
                                                                            <td>
                                                                            <a href="' . base_url('contacts/analyze/sms/' . $enc_id) . '" class="btn btn-sm bg-teal"><i class="fas fa-comments"></i></a>
                                                                            <a href="' . base_url('contacts/analyze/calls/' . $enc_id) . '" class="btn btn-sm btn-primary"><i class="fas fa-phone"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    ';

                                }
                                ?>
                                </tbody>
                            </table>
                            <br>
                            <?php //echo $links; ?>
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



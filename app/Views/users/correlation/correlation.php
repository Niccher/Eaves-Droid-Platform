    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Intelligent Analysis</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Analysis</li>
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
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">SMS</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <ul class="nav nav-pills flex-column">
                                    <li class="nav-item active">
                                        <a href="<?php echo base_url('analysis/sms/finance'); ?>" class="nav-link">
                                            <i class="fas fa-lg fa-comments-dollar"> &nbsp;</i>Money
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?php echo base_url('analysis/sms/promotion'); ?>" class="nav-link">
                                            <i class="fas fa-lg fa-ad"></i> &nbsp;Promotional SMS
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?php echo base_url('analysis/sms/malicious'); ?>" class="nav-link">
                                            <i class="fas fa-lg fa-user-shield"> &nbsp;</i>Malicious
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Calls</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body p-0">
                                <ul class="nav nav-pills flex-column">
                                    <li class="nav-item">
                                        <a href="<?php echo base_url('analysis/calls/family'); ?>" class="nav-link">
                                            <i class="fas fa-lg fa-user-friends"> &nbsp;</i>Family & Friends
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?php echo base_url('analysis/calls/new'); ?>" class="nav-link">
                                            <i class="fas fa-lg fa-phone"> &nbsp;</i>New
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                    <div class="col-md-9">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">Intelligent Analysis</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body p-0">
                                <div class="mailbox-read-info">
                                    <h5><i class="fas fa-sms"></i>&nbsp;SMS intelligence</h5>
                                    <h6 class="text-muted">This logic looks at the data, and categorizes into classes that represent financial transactions, promotional messages, phishing and scamming messages among others.
                                    </h6>
                                    <div class="card-footer">
                                        Select one of these category to proceed &nbsp;&nbsp;&nbsp;
                                        <a href="<?php echo base_url('analysis/sms/finance'); ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-lg fa-comments-dollar"> &nbsp;</i>Money
                                        </a>&nbsp;
                                        <a href="<?php echo base_url('analysis/sms/promotions'); ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-lg fa-ad"></i> &nbsp;Promotional SMS
                                        </a>&nbsp;
                                        <a href="<?php echo base_url('analysis/sms/malicious'); ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-lg fa-user-shield"> &nbsp;</i>Malicious
                                        </a>
                                    </div>
                                </div>

                                <div class="mailbox-read-info">
                                    <h5><i class="fas fa-phone-alt"></i>&nbsp;Calls intelligence.</h5>
                                    <h6 class="text-muted">This logic looks at the data, and categorizes calls into the following category, Close Friends and Family, New Callers, etc.
                                    </h6>
                                    <div class="card-footer">
                                        Select one of these category to proceed &nbsp;&nbsp;&nbsp;
                                        <a href="<?php echo base_url('analysis/sms/family'); ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-lg fa-user-friends"></i> &nbsp;Family and Friends
                                        </a>&nbsp;
                                        <a href="<?php echo base_url('analysis/calls/new'); ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-lg fa-phone"> &nbsp;</i>New
                                        </a>
                                    </div>
                                </div>

                            </div>
                            <!-- /.card-body -->
                            <!-- /.card-footer -->
                            <div class="card-footer">
                            </div>
                            <!-- /.card-footer -->
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
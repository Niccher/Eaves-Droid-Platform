        <?php date_default_timezone_set('Africa/Nairobi'); ?>
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Call Logs Threadview</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Call Logs</a></li>
                                <li class="breadcrumb-item active">Analyze</li>
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
                        <!-- /.col -->
                        <div class="col-md-8 offset-md-2">
                            <div class="card">
                                <div class="card-header">
                                    <h4><a href="<?php echo base_url('contacts'); ?>" class="btn btn-info btn-sm"><i class="icon fa fa-reply icon-only"></i></a> &nbsp;&nbsp;Recipient <a href="#"><?php echo $log_person; ?></a> as <b><?php echo $log_saved; ?></b></h4>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class="tab-content">
                                        <div class="active tab-pane" id="log_web">

                                            <?php
                                            echo '<div class="timeline timeline-inverse">';
                                            foreach ($log_thread as $log=>$element) {
                                                if ($element['Type'] == "Incoming") {
                                                    $type = '<i class="fas  fa-arrow-circle-down bg-primary"></i>';
                                                    $source = 'Called by <b><a href ="#"> '. $element['Caller'].'</a></b>';
                                                }else if ($element['Type'] == "Outgoing") {
                                                    $type = '<i class="fas fa-arrow-circle-up bg-success"></i>';
                                                    $source = 'Called <b><a href ="#"> '. $element['Caller'].'</a></b>';
                                                }else if ($element['Type'] == "Missed") {
                                                    $type = '<i class="fas fa-times-circle bg-info"></i>';
                                                    $source = 'Missed call from <b><a href ="#"> '. $element['Caller'].'</a></b>';
                                                }else if ($element['Type'] == "Rejected") {
                                                    $type = '<i class="fas fa-times-rectangle bg-warning"></i>';
                                                    $source = 'Rejected call from <b><a href ="#"> '. $element['Caller'].'</a></b>';
                                                }else if ($element['Type'] == "Blocked") {
                                                    $type = '<i class="fas fa-ban bg-danger"></i>';
                                                    $source = 'Blocked <b><a href ="#"> '. $element['Caller'].'</a></b>';
                                                }

                                                echo '
                                                        <div class="time-label">
                                                            <span class="bg-gray">
                                                            '.($element['Timestamp']).'
                                                            </span>
                                                        </div>
                                                        <div>
                                                            '.$type.'
                                                            <div class="timeline-item">
                                                                <span class="time font-weight-bold text-monospace"><i class="far fa-clock"></i> '.$element['Timestamp'].'</span>
                                                                <h3 class="timeline-header">'.$source.'</h3>
                                                                <div class="timeline-body">
                                                                    '.gmdate("i:s", $element['Durations']).' 
                                                                </div>
                                                                <div class="timeline-footer"></div>
                                                            </div>
                                                        </div>
                                                    ';
                                            }
                                            echo '
                                                    <div>
                                                        <i class="far fa-clock bg-gray"></i>
                                                    </div>
                                                </div>';
                                            ?>
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



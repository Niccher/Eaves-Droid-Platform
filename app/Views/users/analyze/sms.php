        <?php date_default_timezone_set('Africa/Nairobi'); ?>
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>SMS Threadview</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Sms</a></li>
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
                                    <h4><a href="<?php echo base_url('contacts'); ?>" class="btn btn-info btn-sm"><i class="icon fa fa-reply icon-only"></i></a> &nbsp;&nbsp;Recipient <a href="#"><?php echo $sms_person; ?></a> as <b><?php echo $sms_saved; ?></b></h4>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class="tab-content">
                                        <div class="active tab-pane" id="log_web">

                                            <?php
                                            echo '<div class="timeline timeline-inverse">';
                                            foreach ($sms_thread as $sms=>$element) {

                                                if ($element['sms_type'] == "inbox") {
                                                    $type = '<i class="fas fa-comments bg-primary"></i>';
                                                    $source = 'Received from <b><a href ="#"> '. $element['sms_number'].'</a></b>';
                                                }else if ($element['sms_type'] == "sent") {
                                                    $type = '<i class="fas fa-comment bg-warning"></i>';
                                                    $source = 'Sent to <b><a href ="#"> '. $element['sms_number'].'</a></b>';	                                                	}

                                                echo '
                                                        <div class="time-label">
                                                            <span class="bg-gray"> '.$element['sms_time'].'</span>
                                                        </div>
                                                        <div>
                                                            '.$type.'
                                                            <div class="timeline-item">
                                                                <span class="time font-weight-bold text-monospace"><i class="far fa-clock"></i> '.$element['sms_time'].'</span>
                                                                <h3 class="timeline-header">'.$source.'</h3>
                                                                <div class="timeline-body">
                                                                    '.base64_decode($element['sms_body']).' 
                                                                </div>
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



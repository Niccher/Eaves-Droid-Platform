    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Access Logs</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Account</a></li>
                            <li class="breadcrumb-item active">Access Logs</li>
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
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header p-2">
                                <ul class="nav nav-pills">
                                    <li class="nav-item"><a class="nav-link active" href="#log_web" data-toggle="tab">Web
                                            Platform</a></li>
                                    <li class="nav-item"><a class="nav-link" href="#log_android"
                                                            data-toggle="tab">Android</a></li>
                                </ul>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <div class="tab-content">
                                    <div class="active tab-pane" id="log_web">
                                        <?php
                                        echo '<div class="timeline timeline-inverse">';
                                        foreach ($user_logs as $logs => $entry) {
                                            $date = date('d D M Y', $entry['Timestamps']);
                                            $dat2 = date('H:i:s a', $entry['Timestamps']);
                                            echo '<div class="time-label">
                                                                    <span class="bg-success">
                                                                    ' . $date . '
                                                                    </span>
                                                                </div>';
                                            echo '<div>
                                                                    <i class="fas fa-info bg-primary"></i>
                                                                    <div class="timeline-item">
                                                                        <span class="time"><i class="far fa-clock"></i>' . $dat2 . '</span>
                                                                        <h3 class="timeline-header">' . $entry['Action'] . '</h3>
                                                                    </div>
                                                                </div>';
                                        }
                                        echo '<div>
                                                                <i class="far fa-clock bg-gray"></i>
                                                            </div>';
                                        echo '</div>';

                                        ?>
                                    </div>
                                    <!-- /.tab-pane -->

                                    <div class="tab-pane" id="log_android">
                                        <?php
                                        echo '<div class="timeline timeline-inverse">';
                                        foreach ($user_logs as $logs => $entry) {
                                            $date = date('d D M Y', $entry['Timestamps']);
                                            $dat2 = date('H:i:s a', $entry['Timestamps']);
                                            echo '<div class="time-label">
                                                                    <span class="bg-primary">
                                                                    ' . $date . '
                                                                    </span>
                                                                </div>';
                                            echo '<div>
                                                                    <i class="fas fa-info bg-success"></i>
                                                                    <div class="timeline-item">
                                                                        <span class="time"><i class="far fa-clock"></i>' . $dat2 . '</span>
                                                                        <h3 class="timeline-header">' . $entry['Action'] . '</h3>
                                                                    </div>
                                                                </div>';
                                        }
                                        echo '<div>
                                                                <i class="far fa-clock bg-gray"></i>
                                                            </div>';
                                        echo '</div>';

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



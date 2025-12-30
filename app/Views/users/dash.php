    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Dashboard</h1>
                    </div>
                    <!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Small boxes (Stat box) -->
                <div class="row">
                    <div class="col-lg-2 col-3">
                        <!-- small box -->
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo $total_sms; ?></h3>
                                <p>SMS</p>
                            </div>
                            <div class="icon">
                                <i class="nav-icon fas fa-sms"></i>
                            </div>
                            <a href="<?php echo base_url('sms'); ?>" class="small-box-footer">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-2 col-3">
                        <!-- small box -->
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?php echo $total_calls; ?></h3>
                                <p>Call Logs</p>
                            </div>
                            <div class="icon">
                                <i class="nav-icon fas fa-phone-alt"></i>
                            </div>
                            <a href="<?php echo base_url('call_logs'); ?>" class="small-box-footer">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-2 col-3">
                        <!-- small box -->
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?php echo $total_contacts; ?></h3>
                                <p>Contacts</p>
                            </div>
                            <div class="icon">
                                <i class="nav-icon fas fa-id-card"></i>
                            </div>
                            <a href="<?php echo base_url('contacts'); ?>" class="small-box-footer">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-2 col-3">
                        <!-- small box -->
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo $total_apps; ?></h3>
                                <p>Apps</p>
                            </div>
                            <div class="icon">
                                <i class="nav-icon fas fa-mobile-alt"></i>
                            </div>
                            <a href="<?php echo base_url('apps'); ?>" class="small-box-footer">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-2 col-3">
                        <!-- small box -->
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>65</h3>
                                <p>Media</p>
                            </div>
                            <div class="icon">
                                <i class="nav-icon fas fa-photo-video"></i>
                            </div>
                            <a href="<?php echo base_url('media'); ?>" class="small-box-footer">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-2 col-3">
                        <!-- small box -->
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>65</h3>
                                <p>Files</p>
                            </div>
                            <div class="icon">
                                <i class="nav-icon fas fa-file"></i>
                            </div>
                            <a href="<?php echo base_url('files'); ?>" class="small-box-footer">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <!-- ./col -->
                </div>
                <!-- /.row -->
                <!-- Main row -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header border-transparent">
                                <h3 class="card-title">Most Frequent Calls</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table m-0">
                                        <thead>
                                        <tr>
                                            <th>Number</th>
                                            <th>Saved</th>
                                            <th>Interactions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        foreach ($active_calls as $calls => $callsinfo) {
                                            echo '
                                            <tr>
                                                <td><a href="#">' . $callsinfo['Caller'] . '</a></td>
                                                <td>' . $callsinfo['Saved'] . '</td>
                                                <td><span class="badge badge-success">' . $callsinfo['Totals'] . '</span></td>
                                            </tr>
                                            ';
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /.table-responsive -->
                            </div>
                            <!-- /.card-body -->
                            <div class="card-footer clearfix">
                            </div>
                            <!-- /.card-footer -->
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header border-transparent">
                                <h3 class="card-title">Most Frequent SMS</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table m-0">
                                        <thead>
                                        <tr>
                                            <th>Number</th>
                                            <th>Saved</th>
                                            <th>Thread ID</th>
                                            <th>Interacted</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php

//                                        use App\Models\Mod_Finder;
//
//                                        if (!function_exists('get_contact_name')) {
//                                            function get_contact_name($old_number)
//                                            {
//                                                $modFinder = new Mod_Finder();
//                                                $contactInfo = $modFinder->get_contact_info($old_number);
//                                                return $contactInfo['name'] ?? '';
//                                            }
//                                        }
//
//                                        foreach ($active_sms as $sms => $smsinfo) {
//                                            $number = substr($smsinfo['sms_number'], 0, 1);
//                                            $old_number = $smsinfo['sms_number'];
//
//                                            if ($number == 0) {
//                                                $old_number = substr($smsinfo['sms_number'], 1);
//                                            } else if ($number == "+") {
//                                                $old_number = substr($smsinfo['sms_number'], 4);
//                                            }
//
//                                            if (is_numeric($smsinfo['sms_number'])) {
//                                                $modFinder = new \App\Models\Mod_Finder();
//                                                $name = $modFinder->get_contact($old_number);
//
//                                                if (empty($name)) {
//                                                    $nom = '<i class="text-danger">Unsaved</i>';
//                                                } else {
//                                                    $nom = $name['Name'];
//                                                }
//                                            } else {
//                                                $nom = $smsinfo['sms_number'];
//                                            }
//
//                                            echo '
//                                                <tr>
//                                                    <td><a href="#">' . $smsinfo['sms_number'] . '</a></td>
//                                                    <td>' . $nom . '</td>
//                                                    <td>' . $smsinfo['sms_thread_id'] . '</td>
//                                                    <td><span class="badge badge-success">' . $smsinfo['Totals'] . '</span></td>
//                                                </tr>
//                                                ';
//                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /.table-responsive -->
                            </div>
                            <!-- /.card-body -->
                            <div class="card-footer clearfix">
                            </div>
                            <!-- /.card-footer -->
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- USERS LIST -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Caller distribution pie-chart</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body p-0">
                                <canvas id="canvas_call_distribution"></canvas>
                                <!-- /.users-list -->
                            </div>
                            <!-- /.card-body -->
                            <div class="card-footer text-center">
                                <a href="javascript:void(0)" class="btn btn-sm btn-info">Analyze Call Logs</a>
                            </div>
                            <!-- /.card-footer -->
                        </div>
                        <!--/.card -->
                    </div>


                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">SMS distribution pie-chart</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body p-0">
                                <canvas id="canvas_sms_distribution"></canvas>
                            </div>
                            <!-- /.card-body -->
                            <div class="card-footer text-center">
                                <a href="javascript:void(0)" class="btn btn-sm btn-primary">Analyze SMS</a>
                            </div>
                            <!-- /.card-footer -->
                        </div>
                    </div>
                </div>
                <!-- /.row (main row) -->
            </div>
            <!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

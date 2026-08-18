
    <link href="<?php echo base_url('assets/plugins/select2/select2.min.css'); ?>" rel="stylesheet" />
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
                    <!-- ./col -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box bg-gradient-teal">
                            <span class="info-box-icon"><i class="fa fa-download"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Received</span>
                                <span class="info-box-number">41,410</span>
                                <div class="">
                                    <div class="text-right">
                                        <span class="text">First Time()</span><br>
                                        <span class="text">Latest Time()</span>
                                    </div>
                                </div>

                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>

                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box bg-gradient-success">
                            <span class="info-box-icon"><i class="fa fa-upload"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Sent</span>
                                <span class="info-box-number">41,410</span>
                                <div class="">
                                    <div class="text-right">
                                        <span class="text">First Time()</span><br>
                                        <span class="text">Latest Time()</span>
                                    </div>
                                </div>

                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <!-- ./col -->

                    <div class="col-lg-6 col-6">
                        <!-- small box -->
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>44</h3>
                                <p>Data Points( eg Mpesa,KCB)</p>
                            </div>
                            <div class="icon" data-toggle="modal" data-target="#pop_add_money_point">
                                <i class="ion ion-plus-circled"></i>
                            </div>
                            <a href="#" class="small-box-footer" data-toggle="modal" data-target="#pop_add_money_point">Add a data points <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>


                </div>
                <!-- /.row -->
                <!-- Main row -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header border-transparent">
                                <h3 class="card-title">Source selection</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body p-0">
                                <div class="d-flex justify-content-center">
                                    <div class="text-muted">To see the transactions, select a provider to proceed.</div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-2"></div>
                                    <div class="col-md-8">
                                        <?php
                                            echo $sms_data_points_source;
                                        ?>
                                    </div> <!-- /.form-group -->
                                    <div class="col-md-2"></div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-2"></div>
                                    <div class="col-md-8" id="">
                                        <button class="btn btn-block btn-primary init_calculations">Proceed</button>
                                    </div> <!-- /.form-group -->
                                    <div class="col-md-2"></div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-2"></div>
                                    <div class="col-md-8" id="">
                                        <button class="btn btn-block btn-info" data-toggle="modal" data-target="#pop_add_money_point">Add a new entry</button>
                                    </div> <!-- /.form-group -->
                                    <div class="col-md-2"></div>
                                </div>
                                <br>

                                <!-- /.table-responsive -->
                            </div>
                            <!-- /.card-body -->
                            <div class="card-footer clearfix">
                                <div class="col-md-2"></div>
                            </div>
                            <!-- /.card-footer -->
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Transaction Table</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <div id="example1_wrapper" class="dataTables_wrapper dt-bootstrap4">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <table id="finance_datapoints" class="table table-bordered table-striped dataTable dtr-inline" role="grid" aria-describedby="example1_info">
                                                <thead>
                                                    <tr role="row">
                                                        <th>Sender</th>
                                                        <th>Time</th>
                                                        <th>SmsController</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                    foreach ($sms_good_sms as $sms_item) {
                                                        echo '
                                                            <tr>
                                                                <td>'.$sms_item['sms_number'].'</td>
                                                                <td>'.$sms_item['sms_time'].'</td>
                                                                <td>'.base64_decode($sms_item['sms_body']).'</td>
                                                            </tr>
                                                        ';
                                                    }
                                                ?>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>

                    </div>

                </div>
                <!-- /.row (main row) -->

                <div class="modal fade" id="pop_add_money_point" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Add new Data points to be used for financial analysis.</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="col-md-12">
                                    <div class="form-group data_points" id="data_points">
                                        <?php
                                            echo $sms_data_points;
                                        ?>
                                    </div> <!-- /.form-group -->
                                </div> <!-- /.col -->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary source_clear_data_points">Close</button>
                                <button type="button" class="btn btn-primary source_save_data_points">Save changes</button>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
            <!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
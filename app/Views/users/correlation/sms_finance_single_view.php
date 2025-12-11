
<link href="<?php echo base_url('assets/plugins/select2/select2.min.css'); ?>" rel="stylesheet" />
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Finance Intelligence
                        <span class="small text-muted">
                            at <?php
                                    //$interest = $this->model_cryption->Dec_String(base64_decode(urldecode($this->uri->segment(4))));
                                    echo $page_info_url;
                                ?>
                        </span>
                    </h1>
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
                <div class="col-md-4">
                    <div class="card card-row card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                Received
                            </h3>
                        </div>
                        <div class="card-body">
                            <strong><i class="fas fa-book mr-1"></i> Education</strong>
                            <p class="text-muted">
                                B.S. in Computer Science from the University of Tennessee at Knoxville
                            </p>
                            <hr>
                            <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>
                            <p class="text-muted">Malibu, California</p>
                            <hr>
                            <strong><i class="fas fa-pencil-alt mr-1"></i> Skills</strong>
                            <p class="text-muted">
                                <span class="tag tag-danger">UI Design</span>
                                <span class="tag tag-success">Coding</span>
                                <span class="tag tag-info">Javascript</span>
                                <span class="tag tag-warning">PHP</span>
                                <span class="tag tag-primary">Node.js</span>
                            </p>
                            <hr>
                            <strong><i class="far fa-file-alt mr-1"></i> Notes</strong>
                            <p class="text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam fermentum enim neque.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-row card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                Sent
                            </h3>
                        </div>
                        <div class="card-body">
                            <strong><i class="fas fa-book mr-1"></i> Education</strong>
                            <p class="text-muted">
                                B.S. in Computer Science from the University of Tennessee at Knoxville
                            </p>
                            <hr>
                            <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>
                            <p class="text-muted">Malibu, California</p>
                            <hr>
                            <strong><i class="fas fa-pencil-alt mr-1"></i> Skills</strong>
                            <p class="text-muted">
                                <span class="tag tag-danger">UI Design</span>
                                <span class="tag tag-success">Coding</span>
                                <span class="tag tag-info">Javascript</span>
                                <span class="tag tag-warning">PHP</span>
                                <span class="tag tag-primary">Node.js</span>
                            </p>
                            <hr>
                            <strong><i class="far fa-file-alt mr-1"></i> Notes</strong>
                            <p class="text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam fermentum enim neque.</p>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-row card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                Failed
                            </h3>
                        </div>
                        <div class="card-body">
                            <strong><i class="fas fa-book mr-1"></i> Education</strong>
                            <p class="text-muted">
                                B.S. in Computer Science from the University of Tennessee at Knoxville
                            </p>
                            <hr>
                            <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>
                            <p class="text-muted">Malibu, California</p>
                            <hr>
                            <strong><i class="fas fa-pencil-alt mr-1"></i> Skills</strong>
                            <p class="text-muted">
                                <span class="tag tag-danger">UI Design</span>
                                <span class="tag tag-success">Coding</span>
                                <span class="tag tag-info">Javascript</span>
                                <span class="tag tag-warning">PHP</span>
                                <span class="tag tag-primary">Node.js</span>
                            </p>
                            <hr>
                            <strong><i class="far fa-file-alt mr-1"></i> Notes</strong>
                            <p class="text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam fermentum enim neque.</p>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>

            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Set Rules</h3>
                        </div>
                        <!-- /.card-header -->
                        <!-- form start -->
                        <?php
                            //echo validation_errors();
                            //echo form_open('analysis/set_rules' );
                        ?>
                            <div class="card-body">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Transaction Type</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Starts With</label>
                                            <input type="text" class="form-control" placeholder="Starts with" name="tag_start_with">
                                        </div>

                                        <div class="form-check">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <input type="radio" class="form-check-input" name="tag_type">
                                                    <label class="form-check-label" for="exampleCheck1">Sent</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="radio" class="form-check-input" name="tag_type">
                                                    <label class="form-check-label" for="exampleCheck1">Receive</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="radio" class="form-check-input" name="tag_type">
                                                    <label class="form-check-label" for="exampleCheck1">Other</label>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="text" class="form-control hide d-none" name="tag_from" value="<?php echo "hehe";//$this->uri->segment(4);?>">
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Transaction Meta</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="exampleInputPassword1">Amount</label>
                                            <input type="text" class="form-control" placeholder="Amount" name="tag_amount">
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputPassword1">Time of Day</label>
                                            <input type="text" class="form-control" placeholder="Date" name="tag_date">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4"></div>
                                    <div class="col-md-4">
                                        <input type="submit" class="btn btn-primary btn-block" value="Submit" name="tags_send">
                                    </div>
                                    <div class="col-md-4"></div>
                                </div>

                            </div>
                            <!-- /.card-body -->
                            <div class="card-footer">
                                <?php
                                    $var = "QG7113LKHJ Confirmed. Ksh200.00 sent to IRENE MAINA 0768460893 on 7/7/22 at 7:29 AM. New M-PESA balance is Ksh923.88. Transaction cost, Ksh6.00. Amount you can transact within the day is 299,800.00. Get Stamped M-PESA Statement for free, dial *334# >My Account>M-PESA statement. To reverse, forward this message to 456.";
                                    $receive = "Confirmed.You have received Ksh";
                                    $sent = " Confirmed. Ksh";
                                    if (stripos($var, "Confirmed. Ksh")){
                                        echo "exists";
                                    }else{
                                        echo "No present";
                                    }
                                ?>
                            </div>
                        </form>
                    </div>

                </div>
                <div class="col-md-6">
                    <div class="col-sm-12">
                        <table id="finance_datapoints" class="table table-bordered table-striped dataTable dtr-inline" role="grid" aria-describedby="example1_info">
                            <thead>
                                <tr role="row">
                                    <th>Sender</th>
                                    <th>Time</th>
                                    <th>Sms</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    foreach ($sms_good_sms as $sms_item) {
                                        $receive = "Confirmed.You have received Ksh";
                                        $sent = " Confirmed. Ksh";
                                        $msg = base64_decode($sms_item['sms_body']);
                                        $tag = "";

                                        if (stripos($msg, $sent)){
                                            $tag = '<br><span class="badge badge-success">Sent</span>';
                                        }else if (stripos($msg, $receive)){
                                            $tag = '<br><span class="badge badge-primary">Received</span>';
                                        }else{
                                            $tag = '<br><span class="badge badge-danger">Unknown</span>';
                                        }
                                        echo '
                                            <tr>
                                                <td>'.$sms_item['sms_number'].'</td>
                                                <td>'.$sms_item['sms_time'].$tag.'</td>
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
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
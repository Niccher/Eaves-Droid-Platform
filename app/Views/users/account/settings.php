    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Setting and APIs</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Account</a></li>
                            <li class="breadcrumb-item active">Setting and APIs</li>
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
                    <!-- left column -->
                    <div class="col-md-12">
                        <!-- general form elements -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Android client tokens.</h3>
                            </div>
                            <!-- /.card-header -->
                            <!-- form start -->
                            <div>
                                <div class="card-body">
                                    <div class="form-group row">

                                        <label class="col-2 col-form-label">Android Token.</label>
                                        <div class="col-10">
                                            <input type="text" disabled="" class="form-control is-valid"
                                                   placeholder="Current Token" value="<?php echo $user_token["Token"]; ?>">
                                        </div>
                                    </div>
                                    <div class="callout callout-success">
                                        <h5>Use this code when the android app requests a token. Note this value is used to
                                            track a device and all of its activities.</h5>
                                    </div>
                                </div>
                                <!-- /.card-body -->
                                <div class="card-footer">
                                    <a href="<?php echo base_url('account/setting/token_generate'); ?>">
                                        <button type="submit" class="btn btn-primary">Invalidate current and Regenerate new
                                            Token
                                        </button>
                                    </a>

                                </div>
                            </div>
                        </div>
                        <!-- /.card -->
                    </div>
                    <!--/.col (left) -->
                    <!-- right column -->
                    <div class="col-md-12">
                        <!-- Form Element sizes -->
                        <div class="card">
                            <div class="card-header p-2">
                                <ul class="nav nav-pills">
                                    <li class="nav-item"><a class="nav-link active" href="#interaction_devices"
                                                            data-toggle="tab">Connected Devices</a></li>
                                    <li class="nav-item"><a class="nav-link" href="#interaction_update" data-toggle="tab">Data
                                            Update</a></li>
                                    <li class="nav-item"><a class="nav-link" href="#interaction_all" data-toggle="tab">All
                                            Interactions</a></li>
                                </ul>
                            </div>
                            <!-- /.card-header -->

                            <div class="card-body">
                                <div class="tab-content">
                                    <div class="tab-pane active" id="interaction_devices">
                                        <table class="table table-striped">
                                            <thead>
                                            <tr>
                                                <th>IP Address</th>
                                                <th>Last Connected</th>
                                                <th>Last Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            foreach ($user_devices as $device) {
                                                //$dt = date('Y M D d H:i:s a', $device['Timestamps']);
                                                $ips = $device['IP'];
                                                $action = $device['Action'];
                                                echo '<tr>
                                                                        <td>' . $ips . '</td>
                                                                        <td>' . $device['Timestamps'] . '</td>
                                                                        <td>' . $action . '</td>
                                                                    </tr>';
                                            }
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- /.tab-pane -->
                                    <div class="tab-pane" id="interaction_update">
                                    </div>
                                    <!-- /.tab-pane -->
                                    <div class="tab-pane" id="interaction_all">
                                    </div>
                                    <!-- /.tab-pane -->
                                </div>
                                <!-- /.tab-content -->
                            </div>
                            <!-- /.card-body -->
                            <!-- /.card-body -->
                        </div>

                    </div>
                    <!--/.col (right) -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->




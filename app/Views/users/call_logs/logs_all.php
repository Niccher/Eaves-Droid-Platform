<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Call Logs</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <?php echo $call_urls; ?>
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
                    <div class="card card-secondary">
                        <div class="card-header p-2">
                            <h3 class="card-title"> All Call logs </h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table class="table table-bordered table-hover tabledump">
                                <thead>
                                <tr>
                                    <th>Saved</th>
                                    <th>Caller</th>
                                    <th>Type</th>
                                    <th>Time</th>
                                    <th>Durations</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($call_logs_dump as $call_log) {
                                    //$dt = date('Y M D j, H:i:s a', $call_log['Timestamp']/1000);
                                    $dt = $call_log['Timestamp'];
                                    $duration = date("i:s", $call_log['Durations']);
                                    $name = "";
                                    $type = "";

                                    if (empty($call_log['Saved'])){
                                        $name = '<i><span class="description-percentage text-danger">Unsaved</span></i>';
                                    }else{
                                        $name = '<b><span class="description-percentage text-success">'.$call_log['Saved'].'</span></b>';
                                    }

                                    $timestamp = '<b><span class="text-success">'.$duration.' Minutes</span></b>';

                                    if ($call_log['Durations'] > 10){
                                        $timestamp = '<b><span class="text-muted">'.$duration.' Minutes</span></b>';
                                    }
                                    if ($call_log['Durations'] > 30){
                                        $timestamp = '<b><span class="text-info">'.$duration.' Minutes</span></b>';
                                    }
                                    if ($call_log['Durations'] > 90){
                                        $timestamp = '<b><span class="text-primary">'.$duration.' Minutes</span></b>';
                                    }
                                    if ($call_log['Durations'] > 180){
                                        $timestamp = '<b><span class="text-teal">'.$duration.' Minutes</span></b>';
                                    }
                                    if ($call_log['Durations'] > 300){
                                        $timestamp = '<b><span class="text-warning">'.$duration.' Minutes</span></b>';
                                    }
                                    if ($call_log['Durations'] > 600){
                                        $timestamp = '<b><span class="text-danger">'.$duration.' Minutes</span></b>';
                                    }

                                    $type = '<b><span class="text-muted"><i class="fas fa-phone-slash"></i> Missed</span></b>';
                                    if ($call_log['Type'] == "Incoming"){
                                        $type = '<b><span class="text-muted"><i class="fas fa-arrow-circle-down"></i> Incoming</span></b>';
                                    }
                                    if ($call_log['Type'] == "Outgoing"){
                                        $type = '<b><span class="text-muted"><i class="fas fa-arrow-circle-up"></i> Outgoing</span></b>';
                                    }
                                    if ($call_log['Type'] == "Rejected"){
                                        $type = '<b><span class="text-muted"><i class="fas fa-times"></i> Rejected</span></b>';
                                    }
                                    if ($call_log['Type'] == "Blocked"){
                                        $type = '<b><span class="text-blocked"><i class="fas fa-shield"></i> Blocked</span></b>';
                                    }

                                    echo '
                                            <tr data-widget="expandable-table" aria-expanded="false">
                                                <td>'.$name.'</td>
                                                <td>'.$call_log['Caller'].'</td>
                                                <td>'.$type.'</td>
                                                <td>'.$dt.'</td>
                                                <td>'.$timestamp.'</td>
                                            </tr>
                                            ';
                                }
                                ?>
                                </tbody>
                            </table>
                            <br>
                            <?php //echo $links; ?>
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
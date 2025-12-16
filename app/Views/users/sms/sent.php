
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle bg-primary text-white mr-3">
                                        <i class="fas fa-sms fa-lg"></i>
                                    </div>
                                    <div>
                                        <h2 class="h4 mb-0"><?php echo $sms_head ?? 'SMS Messages' ?></h2>
                                        <p class="text-muted mb-0 mt-1">
                                            <i class="fas fa-envelope mr-1"></i> Total Messages:
                                            <span class="font-weight-bold text-primary"><?php echo $totalSmsSent ?? 0 ?></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 mt-3 mt-lg-0">
                                <div class="d-flex justify-content-end">
                                    <?php echo $sms_urls; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    .icon-circle {
                        width: 56px;
                        height: 56px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                </style>
            </div>
            <!-- /.container-fluid -->
        </section>
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card card-secondary">
                            <div class="card-header">
                                <h3 class="card-title"><?php echo $sms_head; ?></h3>
                            </div>
                            <!-- ./card-header -->
                            <div class="card-body">
                                <table class="table table-bordered table-hover tabledump">

                                    <thead>
                                    <tr>
                                        <th>Contact</th>
                                        <th>Type</th>
                                        <th>Time</th>
                                        <th>Message</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    <?php
                                    foreach ($sms_dump as $smsinfo) {
                                        //$dt = date('Y M D j, H:i:s a', $smsinfo['sms_time']/1000);
                                        $dt = $smsinfo['sms_time'];
                                        $msg = base64_decode($smsinfo['sms_body']);
                                        echo '
                                                                        <tr data-widget="expandable-table" aria-expanded="false">
                                                                            <td>'.$smsinfo['sms_number'].'</td>
                                                                            <td>'.$smsinfo['sms_type'].'</td>
                                                                            <td>'.$dt.'</td>
                                                                            <td class="expandable-body">
                                                                                <a href="javascript:void(0)">
                                                                                    <b>'.character_limiter($msg, 30).'</b>
                                                                                </a>
                                                                                <div class="float-right"><i class="far fa-eye"></i><div>
                                                                            </td>
                                                                        </tr>
                                                                        <tr class="expandable-body">
                                                                            <td colspan="5">
                                                                                <blockquote class="quote-secondary">
                                                                                    <p>'.$msg.'</p>
                                                                                </blockquote>
                                                                            </td>
                                                                        </tr>
                                                                ';
                                    }
                                    ?>
                                    </tbody>

                                </table>
                                <br>
                                <div class="d-flex justify-content-end">
                                    <?php if ($totalSmsSent > count($sms_dump)) {
                                        echo $pager->links('bootstrap5_full', 'bootstrap5_full');
                                    } ?>
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                </div>
            </div>
            <!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->



<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>SMS</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <?php echo $sms_urls; ?>
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
                <div class="col-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title"><?php echo $sms_head; ?></h3>
                        </div>
                        <!-- ./card-header -->
                        <div class="card-body">
                            <?php
                            $uri = \Config\Services::uri();
                            if ($uri->getSegment(2) == "sent"){
                                $var = "Receiver";
                            }
                            if ($uri->getSegment(2) == "inbox"){
                                $var = "Sender";
                            }
                            ?>
                            <table class="table table-bordered table-hover tabledump">
                                <thead>
                                    <tr>
                                        <th><?php echo $var;?></th>
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
                            <?php //echo $links;?>
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

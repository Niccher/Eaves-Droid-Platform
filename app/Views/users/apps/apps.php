
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1>Apps</h1>
                            </div>
                            <div class="col-sm-6">
                                <ol class="breadcrumb float-sm-right">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item active">Apps</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <!-- /.container-fluid -->
                </section>
                <!-- Main content -->
                <section class="content">
                    <!-- Default box -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Apps installed on device (<b><?php echo count($apps_dump);?> apps.</b>) </h3>
                            
                        </div>
                        <div class="card-body p-0">
                            <br>
                            <table class="table table-striped table-hover tabledump">
                                <thead>
                                    <tr>
                                        <th>App Name</th>
                                        <th>Package Name</th>
                                        <th>Used</th>
                                        <th>App Code</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        foreach ($apps_dump as $app) {
                                            echo '
                                            <tr>
                                                <td>'.$app['Name'].'</td>
                                                <td>'.$app['Package'].'</td>
                                                <td><span class="badge badge-success">Active</span></td>
                                                <td>'.$app['Code'].'</td>
                                            </tr>
                                            ';
                                        }
                                    ?>
                                </tbody>
                            </table>
                            <br>
                            <?php
                                if ($pager){
                                    $pagination_path='apps';
                                    $pager->setPath($pagination_path);
                                    $pager->links();
                                }else{
                                    echo "Empty";
                                }
                            ?>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </section>
                <!-- /.content -->
            </div>
            <!-- /.content-wrapper -->
            
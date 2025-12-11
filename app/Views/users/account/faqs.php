
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1>FAQs</h1>
                            </div>
                            <div class="col-sm-6">
                                <ol class="breadcrumb float-sm-right">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item active">FAQs</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <!-- /.container-fluid -->
                </section>
                <!-- Main content -->
                <section class="content">
                    <div class="row">
                        <div class="col-12" id="accordion">
                            <div class="card card-info card-outline">
                                <a class="d-block w-100" data-toggle="collapse" href="#collapseOne">
                                    <div class="card-header">
                                        <h4 class="card-title w-100">
                                            1. Getting started.
                                        </h4>
                                    </div>
                                </a>
                                <div id="collapseOne" class="collapse show" data-parent="#accordion">
                                    <div class="card-body">
                                        How do I get started?
                                        <div class="text-muted">After reaching this point, download the app and use login to start enjoying the benefits of this platform.</div>
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="card card-primary card-outline">
                                <a class="d-block w-100" data-toggle="collapse" href="#collapseTwo">
                                    <div class="card-header">
                                        <h4 class="card-title w-100">
                                            2. The data is not getting reflected on the web.
                                        </h4>
                                    </div>
                                </a>
                                <div id="collapseTwo" class="collapse" data-parent="#accordion">
                                    <div class="card-body">
                                        Data not showed on the web interface.
                                        <div class="text-muted">If the data is not being shown here, that means the data is sent by the android app.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card card-warning card-outline">
                                <a class="d-block w-100" data-toggle="collapse" href="#collapseThree">
                                    <div class="card-header">
                                        <h4 class="card-title w-100">
                                            3. Deleting my personal data.
                                        </h4>
                                    </div>
                                </a>
                                <div id="collapseThree" class="collapse" data-parent="#accordion">
                                    <div class="card-body">
                                        Can I delete all of my personal data?
                                        <div class="text-muted">Data from an extracted from the target android device can be removed/deleted anytime the you want it.</div>
                                        <div class="text-muted">To delete the data please visit <a href="<?php echo base_url('account/profile#delete') ?> "><b>this</b></a> and proceed to delete at your pleasure.</div>
                                        <div class="text-muted">Note this action deletes only the data from android devices, the account is not affected.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card card-success card-outline">
                                <a class="d-block w-100" data-toggle="collapse" href="#collapseFour">
                                    <div class="card-header">
                                        <h4 class="card-title w-100">
                                            4. Exporting data.
                                        </h4>
                                    </div>
                                </a>
                                <div id="collapseFour" class="collapse" data-parent="#accordion">
                                    <div class="card-body">
                                        How do I export my data.
                                        <div class="text-muted">Data generated by an android client is exportable into many formats. Some of them include JSON and CSV for the textal data.</div>
                                        <div class="text-muted">To export the data please visit <a href="<?php echo base_url('account/profile#export') ?> "><b>this</b></a> and follow the steps.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card card-danger card-outline">
                                <a class="d-block w-100" data-toggle="collapse" href="#collapseFive">
                                    <div class="card-header">
                                        <h4 class="card-title w-100">
                                            5. Delete my account.
                                        </h4>
                                    </div>
                                </a>
                                <div id="collapseFive" class="collapse" data-parent="#accordion">
                                    <div class="card-body">
                                        How do I delete my account.
                                        <div class="text-muted">Deleting account will delete all of your data and this action cannot be undone.</div>
                                        <div class="text-muted">To delete the account please visit <a href="<?php echo base_url('account/profile#export') ?> "><b>this</b></a> and follow the steps.</div>
                                        <div class="text-muted">Once the account has been deleted, you will be logged out and will not login without creating another user account again.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- /.content -->
            </div>
            <!-- /.content-wrapper -->
            
            
    <!-- Hero Section -->
    <div class="bg-primary py-5 shadow-sm" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
        <div class="container text-center text-white">
            <h1 class="display-4 font-weight-bold">Pricing Plans</h1>
            <p class="lead">Choose the plan that fits your analytical needs</p>
        </div>
    </div>

    <!-- Pricing Section -->
    <section class="content py-5">
        <div class="container">
            <div class="row">
                <!-- Individual Plan -->
                <div class="col-lg-4 mb-4">
                    <div class="card card-primary card-outline shadow-sm h-100 text-center">
                        <div class="card-header">
                            <h3 class="card-title text-bold" style="float:none"><i class="fas fa-user mr-2"></i>Individual</h3>
                        </div>
                        <div class="card-body">
                            <h2 class="text-primary">$0<span class="h4">/mo</span></h2>
                            <p class="text-muted small">Perfect for personal use</p>
                            <hr>
                            <ul class="list-unstyled mb-4 text-left">
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Full Access to Web Platform</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> One Android Client</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Basic Analytics</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Data Export (CSV)</li>
                            </ul>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <a href="<?= url_to('download') ?>" class="btn btn-primary btn-block shadow-sm">Get Started</a>
                        </div>
                    </div>
                </div>

                <!-- Enterprise Plan -->
                <div class="col-lg-4 mb-4">
                    <div class="card card-success card-outline shadow-sm h-100 text-center">
                        <div class="card-header">
                            <h3 class="card-title text-bold text-success" style="float:none"><i class="fas fa-briefcase mr-2"></i>Enterprise</h3>
                        </div>
                        <div class="card-body">
                            <h2 class="text-success">$0<span class="h4">/mo</span></h2>
                            <p class="text-muted small">For small to medium teams</p>
                            <hr>
                            <ul class="list-unstyled mb-4 text-left">
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Full Access to Web Platform</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Three Android Clients</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Advanced Analytics</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Data Export (CSV, JSON)</li>
                            </ul>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <a href="<?= url_to('download') ?>" class="btn btn-success btn-block shadow-sm">Get Started</a>
                        </div>
                    </div>
                </div>

                <!-- Premium Plan -->
                <div class="col-lg-4 mb-4">
                    <div class="card card-warning card-outline shadow-sm h-100 text-center">
                        <div class="card-header">
                            <h3 class="card-title text-bold text-warning" style="float:none"><i class="fas fa-crown mr-2"></i>Premium</h3>
                        </div>
                        <div class="card-body">
                            <h2 class="text-warning">$0<span class="h4">/mo</span></h2>
                            <p class="text-muted small">For large organizations</p>
                            <hr>
                            <ul class="list-unstyled mb-4 text-left">
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Unlimited Android Clients</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Premium Analytics</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Priority Support</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> All Export Formats</li>
                            </ul>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <a href="<?= url_to('download') ?>" class="btn btn-warning btn-block shadow-sm">Get Started</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comparison Table -->
            <div class="card mt-5 shadow-sm">
                <div class="card-header bg-light">
                    <h3 class="card-title text-bold">Detailed Comparison</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-valign-middle mb-0">
                        <thead>
                            <tr>
                                <th>Feature</th>
                                <th class="text-center">Individual</th>
                                <th class="text-center">Enterprise</th>
                                <th class="text-center">Premium</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Android Clients</td>
                                <td class="text-center"><span class="badge badge-primary">1</span></td>
                                <td class="text-center"><span class="badge badge-success">3</span></td>
                                <td class="text-center"><span class="badge badge-warning">Unlimited</span></td>
                            </tr>
                            <tr>
                                <td>Data Export</td>
                                <td class="text-center">CSV</td>
                                <td class="text-center">CSV, JSON</td>
                                <td class="text-center">All Formats</td>
                            </tr>
                            <tr>
                                <td>Analytics</td>
                                <td class="text-center">Basic</td>
                                <td class="text-center">Advanced</td>
                                <td class="text-center">Full Intelligence</td>
                            </tr>
                            <tr>
                                <td>Support</td>
                                <td class="text-center">Standard</td>
                                <td class="text-center">Standard</td>
                                <td class="text-center">Priority 24/7</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>


<?php //include('footer_landing.php'); ?>
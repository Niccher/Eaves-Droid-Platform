<?php //include('head_landing.php'); ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 font-weight-bold mb-4">Pricing Plans</h1>
                    <p class="lead mb-4">Choose the plan that fits your needs</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Pricing Cards -->
                    <div class="row">
                        <!-- Individual Plan -->
                        <div class="col-lg-4 mb-4">
                            <div class="pricing-card card text-center h-100">
                                <div class="card-header bg-primary text-white py-4">
                                    <h3 class="mb-0">
                                        <i class="fas fa-user mr-2"></i>Individual
                                    </h3>
                                </div>
                                <div class="card-body py-4">
                                    <h2 class="text-primary mb-0">$0<span class="h4">/mo</span></h2>
                                    <p class="text-muted mb-4">Perfect for personal use</p>

                                    <ul class="list-unstyled mb-4">
                                        <li class="mb-3">
                                            <i class="fas fa-check text-success mr-2"></i>
                                            Full Access to Web Platform
                                        </li>
                                        <li class="mb-3">
                                            <i class="fas fa-check text-success mr-2"></i>
                                            One Android Client
                                        </li>
                                        <li class="mb-3">
                                            <i class="fas fa-check text-success mr-2"></i>
                                            Basic Analytics
                                        </li>
                                        <li class="mb-3">
                                            <i class="fas fa-check text-success mr-2"></i>
                                            Data Export (CSV)
                                        </li>
                                    </ul>

                                    <a href="<?php echo base_url("download"); ?>" class="btn btn-primary btn-block">
                                        <i class="fas fa-download mr-2"></i>Get Started
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Enterprise Plan -->
                        <div class="col-lg-4 mb-4">
                            <div class="pricing-card card text-center h-100 border-primary">
                                <div class="card-header bg-success text-white py-4">
                                    <h3 class="mb-0">
                                        <i class="fas fa-briefcase mr-2"></i>Enterprise
                                    </h3>
                                </div>
                                <div class="card-body py-4">
                                    <h2 class="text-success mb-0">$0<span class="h4">/mo</span></h2>
                                    <p class="text-muted mb-4">For small to medium teams</p>

                                    <ul class="list-unstyled mb-4">
                                        <li class="mb-3">
                                            <i class="fas fa-check text-success mr-2"></i>
                                            Full Access to Web Platform
                                        </li>
                                        <li class="mb-3">
                                            <i class="fas fa-check text-success mr-2"></i>
                                            Three Android Clients
                                        </li>
                                        <li class="mb-3">
                                            <i class="fas fa-check text-success mr-2"></i>
                                            Advanced Analytics
                                        </li>
                                        <li class="mb-3">
                                            <i class="fas fa-check text-success mr-2"></i>
                                            Data Export (CSV, JSON)
                                        </li>
                                    </ul>

                                    <a href="<?php echo base_url("download"); ?>" class="btn btn-success btn-block">
                                        <i class="fas fa-rocket mr-2"></i>Get Started
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Premium Plan -->
                        <div class="col-lg-4 mb-4">
                            <div class="pricing-card card text-center h-100">
                                <div class="card-header bg-warning text-white py-4">
                                    <h3 class="mb-0">
                                        <i class="fas fa-crown mr-2"></i>Premium
                                    </h3>
                                </div>
                                <div class="card-body py-4">
                                    <h2 class="text-warning mb-0">$0<span class="h4">/mo</span></h2>
                                    <p class="text-muted mb-4">For large organizations</p>

                                    <ul class="list-unstyled mb-4">
                                        <li class="mb-3">
                                            <i class="fas fa-check text-success mr-2"></i>
                                            Full Access to Web Platform
                                        </li>
                                        <li class="mb-3">
                                            <i class="fas fa-check text-success mr-2"></i>
                                            Unlimited Android Clients
                                        </li>
                                        <li class="mb-3">
                                            <i class="fas fa-check text-success mr-2"></i>
                                            Premium Analytics
                                        </li>
                                        <li class="mb-3">
                                            <i class="fas fa-check text-success mr-2"></i>
                                            Data Export (CSV, JSON, PDF)
                                        </li>
                                        <li class="mb-3">
                                            <i class="fas fa-check text-success mr-2"></i>
                                            Priority Support
                                        </li>
                                    </ul>

                                    <a href="<?php echo base_url("download"); ?>" class="btn btn-warning btn-block">
                                        <i class="fas fa-star mr-2"></i>Get Started
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comparison Table -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-body">
                            <h3 class="section-title mb-4">Features Comparison</h3>

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>Feature</th>
                                        <th class="text-center text-primary">Individual</th>
                                        <th class="text-center text-success">Enterprise</th>
                                        <th class="text-center text-warning">Premium</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>Android Clients</td>
                                        <td class="text-center">1</td>
                                        <td class="text-center">3</td>
                                        <td class="text-center">Unlimited</td>
                                    </tr>
                                    <tr>
                                        <td>Data Export Formats</td>
                                        <td class="text-center">CSV</td>
                                        <td class="text-center">CSV, JSON</td>
                                        <td class="text-center">CSV, JSON, PDF</td>
                                    </tr>
                                    <tr>
                                        <td>Support Level</td>
                                        <td class="text-center">Standard</td>
                                        <td class="text-center">Standard</td>
                                        <td class="text-center">Priority</td>
                                    </tr>
                                    <tr>
                                        <td>Analytics Depth</td>
                                        <td class="text-center">Basic</td>
                                        <td class="text-center">Advanced</td>
                                        <td class="text-center">Premium</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php //include('footer_landing.php'); ?>
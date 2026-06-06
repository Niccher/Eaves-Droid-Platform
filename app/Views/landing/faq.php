    <!-- Hero Section -->
    <div class="bg-primary py-5 shadow-sm" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
        <div class="container text-center text-white">
            <h1 class="display-4 font-weight-bold">Frequently Asked Questions</h1>
            <p class="lead">Find answers to common questions about Prj Images</p>
        </div>
    </div>

    <!-- FAQ Section -->
    <section class="content py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <!-- FAQ Navigation -->
                    <div class="card card-primary card-outline shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title text-bold">Categories</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="nav nav-pills flex-column">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="pill" href="#general">
                                        <i class="fas fa-question-circle mr-2"></i> General
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="pill" href="#technical">
                                        <i class="fas fa-cogs mr-2"></i> Technical
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="pill" href="#privacy">
                                        <i class="fas fa-shield-alt mr-2"></i> Privacy
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card bg-light shadow-sm mt-4">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-headset fa-2x text-primary mb-3"></i>
                            <h5 class="text-bold">Need Help?</h5>
                            <p class="small text-muted mb-3">Our support team is available to assist you.</p>
                            <a href="<?= url_to('contact') ?>" class="btn btn-primary btn-sm btn-block shadow-sm">Contact Us</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="tab-content">
                        <!-- General FAQ -->
                        <div class="tab-pane fade show active" id="general">
                            <h3 class="font-weight-light mb-4">General Questions</h3>
                            <div class="accordion" id="generalAccordion">
                                <div class="card card-primary card-outline shadow-sm mb-3">
                                    <div class="card-header p-0" id="headingOne">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link btn-block text-left py-3 text-bold text-dark" type="button" data-toggle="collapse" data-target="#collapseOne">
                                                <i class="fas fa-database mr-2 text-primary"></i> Is my data stored forever?
                                            </button>
                                        </h5>
                                    </div>
                                    <div id="collapseOne" class="collapse show" data-parent="#generalAccordion">
                                        <div class="card-body">
                                            <p>You maintain complete control over your information:</p>
                                            <ul class="mb-0">
                                                <li>Delete individual data points from your dashboard</li>
                                                <li>Schedule automatic data purging</li>
                                                <li>Completely delete your account and all associated data</li>
                                            </ul>
                                            <div class="callout callout-info mt-3 small">
                                                <i class="fas fa-info-circle mr-2 text-info"></i> Account deletion is permanent and irreversible.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Technical FAQ -->
                        <div class="tab-pane fade" id="technical">
                            <h3 class="font-weight-light mb-4 text-success">Technical Questions</h3>
                            <div class="accordion" id="technicalAccordion">
                                <div class="card card-success card-outline shadow-sm mb-3">
                                    <div class="card-header p-0">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link btn-block text-left py-3 text-bold text-dark" type="button" data-toggle="collapse" data-target="#collapseAndroid">
                                                <i class="fab fa-android mr-2 text-success"></i> What Android versions are supported?
                                            </button>
                                        </h5>
                                    </div>
                                    <div id="collapseAndroid" class="collapse show" data-parent="#technicalAccordion">
                                        <div class="card-body">
                                            <p>Our Android app supports Android 8.0 (Oreo) and above. This covers over 95% of active Android devices.</p>
                                            <div class="mt-3">
                                                <span class="text-bold small">Version Compatibility:</span>
                                                <div class="progress progress-sm mb-2 mt-1 shadow-sm">
                                                    <div class="progress-bar bg-success" style="width: 95%"></div>
                                                </div>
                                                <small class="text-muted">95% of active Android device distribution.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Privacy FAQ -->
                        <div class="tab-pane fade" id="privacy">
                            <h3 class="font-weight-light mb-4 text-info">Privacy Questions</h3>
                            <div class="accordion" id="privacyAccordion">
                                <div class="card card-info card-outline shadow-sm mb-3">
                                    <div class="card-header p-0">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link btn-block text-left py-3 text-bold text-dark" type="button" data-toggle="collapse" data-target="#collapseEncryption">
                                                <i class="fas fa-lock mr-2 text-info"></i> How is my data encrypted?
                                            </button>
                                        </h5>
                                    </div>
                                    <div id="collapseEncryption" class="collapse show" data-parent="#privacyAccordion">
                                        <div class="card-body">
                                            <p>We use industry-standard encryption protocols to protect your data:</p>
                                            <ul class="mb-0">
                                                <li><strong>Transport:</strong> TLS 1.3 encryption for all data in transit.</li>
                                                <li><strong>Storage:</strong> AES-256 encryption for data at rest.</li>
                                                <li><strong>End-to-End:</strong> Optional end-to-end encryption for sensitive data.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>


<?php //include('footer_landing.php'); ?>
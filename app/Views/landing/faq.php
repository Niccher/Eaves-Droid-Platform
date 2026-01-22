<?php //include('head_landing.php'); ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 font-weight-bold mb-4">Frequently Asked Questions</h1>
                    <p class="lead mb-4">Find answers to common questions about Prj Images</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5">
        <div class="container">
            <!-- FAQ Navigation -->
            <div class="row mb-4">
                <div class="col-12">
                    <ul class="nav nav-pills justify-content-center mb-4">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="pill" href="#general">
                                <i class="fas fa-question-circle mr-2"></i>General
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#technical">
                                <i class="fas fa-cogs mr-2"></i>Technical
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#privacy">
                                <i class="fas fa-shield-alt mr-2"></i>Privacy
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#billing">
                                <i class="fas fa-credit-card mr-2"></i>Billing
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- FAQ Content -->
            <div class="tab-content">
                <!-- General FAQ -->
                <div class="tab-pane fade show active" id="general">
                    <h2 class="section-title mb-4">
                        <i class="fas fa-question-circle text-primary mr-2"></i>General Questions
                    </h2>

                    <div class="accordion" id="generalAccordion">
                        <div class="card mb-3">
                            <div class="card-header" id="headingOne">
                                <h5 class="mb-0">
                                    <button class="btn btn-link text-primary" type="button" data-toggle="collapse" data-target="#collapseOne">
                                        <i class="fas fa-database mr-2"></i>Is my data stored forever?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseOne" class="collapse show" data-parent="#generalAccordion">
                                <div class="card-body">
                                    <p>You maintain complete control over your information:</p>
                                    <ul>
                                        <li>Delete individual data points from your dashboard</li>
                                        <li>Schedule automatic data purging</li>
                                        <li>Completely delete your account and all associated data</li>
                                    </ul>
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Account deletion is permanent and irreversible
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header" id="headingTwo">
                                <h5 class="mb-0">
                                    <button class="btn btn-link text-primary collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo">
                                        <i class="fas fa-level-up-alt mr-2"></i>Can I upgrade my plan later?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseTwo" class="collapse" data-parent="#generalAccordion">
                                <div class="card-body">
                                    <p>We've designed our pricing to be flexible and user-friendly:</p>
                                    <ul>
                                        <li><strong>Upgrade Anytime:</strong> Move to a higher tier whenever you need more features</li>
                                        <li><strong>Pro-rated Billing:</strong> Only pay for the remaining time in your billing cycle</li>
                                        <li><strong>No Downtime:</strong> Upgrades happen instantly with no service interruption</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Technical FAQ -->
                <div class="tab-pane fade" id="technical">
                    <h2 class="section-title mb-4">
                        <i class="fas fa-cogs text-success mr-2"></i>Technical Questions
                    </h2>

                    <div class="accordion" id="technicalAccordion">
                        <div class="card mb-3">
                            <div class="card-header" id="headingAndroid">
                                <h5 class="mb-0">
                                    <button class="btn btn-link text-success" type="button" data-toggle="collapse" data-target="#collapseAndroid">
                                        <i class="fab fa-android mr-2"></i>What Android versions are supported?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseAndroid" class="collapse show" data-parent="#technicalAccordion">
                                <div class="card-body">
                                    <p>Our Android app supports Android 8.0 (Oreo) and above. This covers over 95% of active Android devices.</p>
                                    <div class="mt-3">
                                        <h6>Version Compatibility:</h6>
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-success" style="width: 95%">95% Compatible</div>
                                        </div>
                                        <small class="text-muted">Based on active Android device distribution</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Privacy FAQ -->
                <div class="tab-pane fade" id="privacy">
                    <h2 class="section-title mb-4">
                        <i class="fas fa-shield-alt text-info mr-2"></i>Privacy Questions
                    </h2>

                    <div class="accordion" id="privacyAccordion">
                        <div class="card mb-3">
                            <div class="card-header" id="headingEncryption">
                                <h5 class="mb-0">
                                    <button class="btn btn-link text-info" type="button" data-toggle="collapse" data-target="#collapseEncryption">
                                        <i class="fas fa-lock mr-2"></i>How is my data encrypted?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseEncryption" class="collapse show" data-parent="#privacyAccordion">
                                <div class="card-body">
                                    <p>We use industry-standard encryption protocols to protect your data:</p>
                                    <ul>
                                        <li><strong>Transport:</strong> TLS 1.3 encryption for all data in transit</li>
                                        <li><strong>Storage:</strong> AES-256 encryption for data at rest</li>
                                        <li><strong>End-to-End:</strong> Optional end-to-end encryption for sensitive data</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing FAQ -->
                <div class="tab-pane fade" id="billing">
                    <h2 class="section-title mb-4">
                        <i class="fas fa-credit-card text-warning mr-2"></i>Billing Questions
                    </h2>

                    <div class="accordion" id="billingAccordion">
                        <div class="card mb-3">
                            <div class="card-header" id="headingRefunds">
                                <h5 class="mb-0">
                                    <button class="btn btn-link text-warning" type="button" data-toggle="collapse" data-target="#collapseRefunds">
                                        <i class="fas fa-money-bill-wave mr-2"></i>What is your refund policy?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseRefunds" class="collapse show" data-parent="#billingAccordion">
                                <div class="card-body">
                                    <p>We offer a 14-day money-back guarantee on all paid plans. If you're not satisfied with our service, contact our support team within 14 days of purchase for a full refund.</p>
                                    <div class="alert alert-warning mt-3">
                                        <i class="fas fa-exclamation-circle mr-2"></i>
                                        Refunds are processed within 5-7 business days and will be issued to your original payment method.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Still Have Questions -->
            <div class="card border-0 shadow-sm bg-light mt-5">
                <div class="card-body p-5 text-center">
                    <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                    <h3 class="text-primary mb-3">Still Have Questions?</h3>
                    <p class="text-muted mb-4">Can't find the answer you're looking for? Our support team is here to help.</p>
                    <a href="<?= url_to('contact') ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-envelope mr-2"></i>Contact Support
                    </a>
                </div>
            </div>
        </div>
    </section>

<?php //include('footer_landing.php'); ?>
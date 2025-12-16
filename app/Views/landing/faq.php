    <!-- FAQ Section -->
    <section class="section" id="faq">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="section-title">
                        <h4 class="title mb-4">Frequently Asked Questions</h4>
                        <p class="text-muted">These are among the most raised questions on our platform. If you have any
                            other question, please use <a href="<?= url_to('contactus') ?>" class="text-primary">this
                                link</a> to send your question.</p>
                    </div>

                    <!-- FAQ Categories Navigation -->
                    <div class="faq-nav mb-4">
                        <div class="nav nav-pills justify-content-center" id="faqTab" role="tablist">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="pill" data-bs-target="#general"
                                    type="button">
                                <i class="fas fa-question-circle me-2"></i> General
                            </button>
                            <button class="nav-link" id="technical-tab" data-bs-toggle="pill" data-bs-target="#technical"
                                    type="button">
                                <i class="fas fa-cogs me-2"></i> Technical
                            </button>
                            <button class="nav-link" id="privacy-tab" data-bs-toggle="pill" data-bs-target="#privacy"
                                    type="button">
                                <i class="fas fa-shield-alt me-2"></i> Privacy
                            </button>
                            <button class="nav-link" id="billing-tab" data-bs-toggle="pill" data-bs-target="#billing"
                                    type="button">
                                <i class="fas fa-credit-card me-2"></i> Billing
                            </button>
                        </div>
                    </div>

                    <!-- FAQ Content -->
                    <div class="tab-content" id="faqTabContent">
                        <!-- General FAQ -->
                        <div class="tab-pane fade show active" id="general">
                            <div class="accordion" id="generalAccordion">
                                <div class="accordion-item rounded shadow-sm mb-3">
                                    <h2 class="accordion-header" id="headingDataStorage">
                                        <button class="accordion-button border-0 bg-light collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapseDataStorage">
                                            <i class="fas fa-database text-primary me-3"></i>
                                            Is my data stored forever?
                                        </button>
                                    </h2>
                                    <div id="collapseDataStorage" class="accordion-collapse border-0 collapse">
                                        <div class="accordion-body">
                                            <p>Once the Android client uploads data to our servers, you maintain complete
                                                control over your information. You can:</p>
                                            <ul>
                                                <li>Delete individual data points from your dashboard</li>
                                                <li>Schedule automatic data purging after a set period</li>
                                                <li>Completely delete your account and all associated data</li>
                                                <li>Request immediate data deletion through our support team</li>
                                            </ul>
                                            <p class="mb-0"><strong>Note:</strong> Account deletion is permanent and
                                                irreversible. All associated data will be permanently removed from our
                                                servers within 30 days.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item rounded shadow-sm mb-3">
                                    <h2 class="accordion-header" id="headingUpgrade">
                                        <button class="accordion-button border-0 bg-light collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapseUpgrade">
                                            <i class="fas fa-level-up-alt text-primary me-3"></i>
                                            Can I upgrade my plan later?
                                        </button>
                                    </h2>
                                    <div id="collapseUpgrade" class="accordion-collapse border-0 collapse">
                                        <div class="accordion-body">
                                            <p>Absolutely! We've designed our pricing to be flexible and user-friendly:</p>
                                            <ul>
                                                <li><strong>Upgrade Anytime:</strong> Move to a higher tier whenever you
                                                    need more features
                                                </li>
                                                <li><strong>Pro-rated Billing:</strong> Only pay for the remaining time in
                                                    your billing cycle
                                                </li>
                                                <li><strong>No Downtime:</strong> Upgrades happen instantly with no service
                                                    interruption
                                                </li>
                                                <li><strong>Feature Preview:</strong> Test premium features before
                                                    committing
                                                </li>
                                            </ul>
                                            <div class="alert alert-info mt-3">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>Tip:</strong> All paid plans include a 14-day money-back guarantee.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item rounded shadow-sm mb-3">
                                    <h2 class="accordion-header" id="headingDataExport">
                                        <button class="accordion-button border-0 bg-light collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapseDataExport">
                                            <i class="fas fa-download text-primary me-3"></i>
                                            Can I download/export my data?
                                        </button>
                                    </h2>
                                    <div id="collapseDataExport" class="accordion-collapse border-0 collapse">
                                        <div class="accordion-body">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="me-3">
                                                    <span class="badge bg-success">Available Now</span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Basic Export Features</h6>
                                                    <p class="text-muted mb-0">Export your data in CSV format directly from
                                                        your dashboard</p>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-start mb-3">
                                                <div class="me-3">
                                                    <span class="badge bg-warning">Coming Soon</span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Advanced Export Options</h6>
                                                    <p class="text-muted mb-0">JSON, XML, PDF, and Excel formats with custom
                                                        templates</p>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-start">
                                                <div class="me-3">
                                                    <span class="badge bg-info">Planned</span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">API Access</h6>
                                                    <p class="text-muted mb-0">Direct API access for automated data
                                                        retrieval</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item rounded shadow-sm mb-3">
                                    <h2 class="accordion-header" id="headingPlugins">
                                        <button class="accordion-button border-0 bg-light collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapsePlugins">
                                            <i class="fas fa-puzzle-piece text-primary me-3"></i>
                                            Are plugins or integrations available?
                                        </button>
                                    </h2>
                                    <div id="collapsePlugins" class="accordion-collapse border-0 collapse">
                                        <div class="accordion-body">
                                            <p>We're constantly expanding our integration capabilities:</p>

                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <div class="integration-card p-3 border rounded mb-3">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="fas fa-check-circle text-success me-2"></i>
                                                            <h6 class="mb-0">Current Integrations</h6>
                                                        </div>
                                                        <ul class="text-muted small mb-0">
                                                            <li>Google Drive export</li>
                                                            <li>Dropbox backup</li>
                                                            <li>Email report delivery</li>
                                                            <li>Webhook notifications</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="integration-card p-3 border rounded mb-3">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="fas fa-clock text-warning me-2"></i>
                                                            <h6 class="mb-0">Coming Soon</h6>
                                                        </div>
                                                        <ul class="text-muted small mb-0">
                                                            <li>Slack notifications</li>
                                                            <li>Zapier integration</li>
                                                            <li>Custom plugin API</li>
                                                            <li>Third-party app store</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <a href="<?= url_to('contactus') ?>" class="text-primary">
                                                    <i class="fas fa-lightbulb me-1"></i> Have a specific integration
                                                    request?
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Technical FAQ -->
                        <div class="tab-pane fade" id="technical">
                            <div class="accordion" id="technicalAccordion">
                                <!-- Add technical FAQs here -->
                                <div class="accordion-item rounded shadow-sm mb-3">
                                    <h2 class="accordion-header" id="headingAndroidVersion">
                                        <button class="accordion-button border-0 bg-light collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapseAndroidVersion">
                                            <i class="fab fa-android text-primary me-3"></i>
                                            What Android versions are supported?
                                        </button>
                                    </h2>
                                    <div id="collapseAndroidVersion" class="accordion-collapse border-0 collapse">
                                        <div class="accordion-body">
                                            <p>Our Android app supports Android 8.0 (Oreo) and above. This covers over 95%
                                                of active Android devices.</p>
                                            <div class="compatibility-chart mt-3">
                                                <h6>Version Compatibility:</h6>
                                                <div class="progress mb-2" style="height: 20px;">
                                                    <div class="progress-bar bg-success" style="width: 95%">95% Compatible
                                                    </div>
                                                </div>
                                                <small class="text-muted">Based on active Android device
                                                    distribution</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Privacy FAQ -->
                        <div class="tab-pane fade" id="privacy">
                            <div class="accordion" id="privacyAccordion">
                                <!-- Add privacy FAQs here -->
                                <div class="accordion-item rounded shadow-sm mb-3">
                                    <h2 class="accordion-header" id="headingDataEncryption">
                                        <button class="accordion-button border-0 bg-light collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapseDataEncryption">
                                            <i class="fas fa-lock text-primary me-3"></i>
                                            How is my data encrypted?
                                        </button>
                                    </h2>
                                    <div id="collapseDataEncryption" class="accordion-collapse border-0 collapse">
                                        <div class="accordion-body">
                                            <p>We use industry-standard encryption protocols to protect your data:</p>
                                            <ul>
                                                <li><strong>Transport:</strong> TLS 1.3 encryption for all data in transit
                                                </li>
                                                <li><strong>Storage:</strong> AES-256 encryption for data at rest</li>
                                                <li><strong>End-to-End:</strong> Optional end-to-end encryption for
                                                    sensitive data
                                                </li>
                                                <li><strong>Key Management:</strong> Secure key management with regular
                                                    rotation
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Billing FAQ -->
                        <div class="tab-pane fade" id="billing">
                            <div class="accordion" id="billingAccordion">
                                <!-- Add billing FAQs here -->
                                <div class="accordion-item rounded shadow-sm mb-3">
                                    <h2 class="accordion-header" id="headingRefunds">
                                        <button class="accordion-button border-0 bg-light collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapseRefunds">
                                            <i class="fas fa-money-bill-wave text-primary me-3"></i>
                                            What is your refund policy?
                                        </button>
                                    </h2>
                                    <div id="collapseRefunds" class="accordion-collapse border-0 collapse">
                                        <div class="accordion-body">
                                            <p>We offer a 14-day money-back guarantee on all paid plans. If you're not
                                                satisfied with our service, contact our support team within 14 days of
                                                purchase for a full refund.</p>
                                            <div class="alert alert-info mt-3">
                                                <i class="fas fa-exclamation-circle me-2"></i>
                                                Refunds are processed within 5-7 business days and will be issued to your
                                                original payment method.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Still Have Questions -->
                    <div class="card border-0 bg-light mt-5">
                        <div class="card-body p-5 text-center">
                            <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                            <h4 class="mb-3">Still Have Questions?</h4>
                            <p class="text-muted mb-4">Can't find the answer you're looking for? Our support team is here to
                                help.</p>
                            <div class="d-flex flex-wrap justify-content-center gap-3">
                                <a href="<?= url_to('contactus') ?>" class="btn btn-primary">
                                    <i class="fas fa-envelope me-2"></i> Contact Support
                                </a>
                                <a href="<?= base_url('documentation') ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-book me-2"></i> View Documentation
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Terms of Use Section -->
    <section class="section bg-light" id="terms">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow">
                        <div class="card-body p-5">
                            <!-- Terms Navigation -->
                            <div class="terms-nav mb-5">
                                <h4 class="title mb-4">Website Terms of Use</h4>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="#terms-overview" class="btn btn-sm btn-outline-primary">Overview</a>
                                    <a href="#terms-access" class="btn btn-sm btn-outline-primary">Access</a>
                                    <a href="#terms-content" class="btn btn-sm btn-outline-primary">User Content</a>
                                    <a href="#terms-privacy" class="btn btn-sm btn-outline-primary">Privacy</a>
                                    <a href="#terms-disclaimer" class="btn btn-sm btn-outline-primary">Disclaimer</a>
                                    <a href="#terms-liability" class="btn btn-sm btn-outline-primary">Liability</a>
                                </div>
                            </div>

                            <!-- Version Info -->
                            <div class="alert alert-info mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle fa-2x me-3"></i>
                                    <div>
                                        <h6 class="mb-1">Version 2.1 • Last Updated: <?= date('F j, Y') ?></h6>
                                        <p class="mb-0">These terms were last updated on the date shown above. Please review
                                            them regularly.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms Content -->
                            <div class="terms-content">
                                <!-- Overview -->
                                <div id="terms-overview" class="mb-5">
                                    <h5 class="mb-3">Overview</h5>
                                    <p>The Prj Images website located at <strong><?= base_url() ?></strong> is a copyrighted
                                        work belonging to Prj Images. Certain features of the Site may be subject to
                                        additional guidelines, terms, or rules, which will be posted on the Site in
                                        connection with such features.</p>
                                    <p>All such additional terms, guidelines, and rules are incorporated by reference into
                                        these Terms.</p>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Important:</strong> These Terms of Use describe the legally binding terms
                                        and conditions that oversee your use of the Site. BY LOGGING INTO THE SITE, YOU ARE
                                        BEING COMPLIANT THAT THESE TERMS and you represent that you have the authority and
                                        capacity to enter into these Terms. YOU SHOULD BE AT LEAST 18 YEARS OF AGE TO ACCESS
                                        THE SITE. IF YOU DISAGREE WITH ALL OF THE PROVISION OF THESE TERMS, DO NOT LOG INTO
                                        AND/OR USE THE SITE.
                                    </div>
                                </div>

                                <!-- Access -->
                                <div id="terms-access" class="mb-5">
                                    <h5 class="mb-3">Access to the Site</h5>
                                    <p><strong>Subject to these Terms.</strong> Company grants you a non-transferable,
                                        non-exclusive, revocable, limited license to access the Site solely for your own
                                        personal, noncommercial use.</p>

                                    <div class="card border-warning mb-3">
                                        <div class="card-header bg-warning text-dark">
                                            <i class="fas fa-ban me-2"></i> Certain Restrictions
                                        </div>
                                        <div class="card-body">
                                            <p>The rights approved to you in these Terms are subject to the following
                                                restrictions:</p>
                                            <ul>
                                                <li>You shall not sell, rent, lease, transfer, assign, distribute, host, or
                                                    otherwise commercially exploit the Site
                                                </li>
                                                <li>You shall not change, make derivative works of, disassemble, reverse
                                                    compile or reverse engineer any part of the Site
                                                </li>
                                                <li>You shall not access the Site in order to build a similar or competitive
                                                    website
                                                </li>
                                                <li>Except as expressly stated herein, no part of the Site may be copied,
                                                    reproduced, distributed, republished, downloaded, displayed, posted or
                                                    transmitted in any form or by any means
                                                </li>
                                            </ul>
                                            <p class="mb-0">All copyright and other proprietary notices on the Site must be
                                                retained on all copies thereof.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- User Content -->
                                <div id="terms-content" class="mb-5">
                                    <h5 class="mb-3">User Content & Acceptable Use</h5>
                                    <p><strong>User Content.</strong> "User Content" means any and all information and
                                        content that a user submits to the Site. You are exclusively responsible for your
                                        User Content.</p>

                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div class="card border-success h-100">
                                                <div class="card-header bg-success text-white">
                                                    <i class="fas fa-check-circle me-2"></i> Acceptable Use
                                                </div>
                                                <div class="card-body">
                                                    <ul class="mb-0">
                                                        <li>Your own original content</li>
                                                        <li>Properly licensed material</li>
                                                        <li>Respectful communication</li>
                                                        <li>Lawful purposes only</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card border-danger h-100">
                                                <div class="card-header bg-danger text-white">
                                                    <i class="fas fa-times-circle me-2"></i> Prohibited Content
                                                </div>
                                                <div class="card-body">
                                                    <ul class="mb-0">
                                                        <li>Copyright infringement</li>
                                                        <li>Harassing content</li>
                                                        <li>Malicious software</li>
                                                        <li>Spam or phishing</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Privacy -->
                                <div id="terms-privacy" class="mb-5">
                                    <h5 class="mb-3">Privacy & Data Protection</h5>
                                    <p><strong>Cookies and Web Beacons.</strong> Like any other website, Prj Images uses
                                        'cookies'. These cookies are used to store information including visitors'
                                        preferences, and the pages on the website that the visitor accessed or visited. The
                                        information is used to optimize the users' experience by customizing our web page
                                        content based on visitors' browser type and/or other information.</p>

                                    <div class="privacy-highlights mt-4">
                                        <h6 class="mb-3">Privacy Highlights:</h6>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="text-center p-3 border rounded">
                                                    <i class="fas fa-user-shield fa-2x text-primary mb-2"></i>
                                                    <h6>Data Protection</h6>
                                                    <p class="text-muted small mb-0">We protect your personal
                                                        information</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="text-center p-3 border rounded">
                                                    <i class="fas fa-cookie-bite fa-2x text-primary mb-2"></i>
                                                    <h6>Cookie Control</h6>
                                                    <p class="text-muted small mb-0">Manage cookie preferences anytime</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="text-center p-3 border rounded">
                                                    <i class="fas fa-eye-slash fa-2x text-primary mb-2"></i>
                                                    <h6>No Tracking</h6>
                                                    <p class="text-muted small mb-0">We don't sell your data to third
                                                        parties</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Disclaimer -->
                                <div id="terms-disclaimer" class="mb-5">
                                    <h5 class="mb-3">Disclaimers</h5>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                                        <div>
                                            <p class="mb-0">The site is provided on an "as-is" and "as available" basis, and
                                                company and our suppliers expressly disclaim any and all warranties and
                                                conditions of any kind, whether express, implied, or statutory, including
                                                all warranties or conditions of merchantability, fitness for a particular
                                                purpose, title, quiet enjoyment, accuracy, or non-infringement.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Liability -->
                                <div id="terms-liability" class="mb-5">
                                    <h5 class="mb-3">Limitation on Liability</h5>
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white">
                                            <i class="fas fa-balance-scale me-2"></i> Liability Limitation
                                        </div>
                                        <div class="card-body">
                                            <p>To the maximum extent permitted by law, in no event shall company or our
                                                suppliers be liable to you or any third-party for any lost profits, lost
                                                data, costs of procurement of substitute products, or any indirect,
                                                consequential, exemplary, incidental, special or punitive damages arising
                                                from or relating to these terms or your use of, or incapability to use the
                                                site even if company has been advised of the possibility of such
                                                damages.</p>
                                            <div class="alert alert-warning mt-3">
                                                <i class="fas fa-dollar-sign me-2"></i>
                                                <strong>Maximum Liability:</strong> Our liability to you for any damages
                                                arising from or related to this agreement will at all times be limited to a
                                                maximum of fifty U.S. dollars (U.S. $50).
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Acceptance -->
                                <div class="acceptance-section text-center mt-5 pt-5 border-top">
                                    <div class="acceptance-box p-4 bg-light rounded shadow-sm d-inline-block">
                                        <i class="fas fa-file-contract fa-3x text-primary mb-3"></i>
                                        <h5 class="mb-3">By using our service, you agree to these terms</h5>
                                        <p class="text-muted mb-4">Please read these terms carefully before using our
                                            platform.</p>
                                        <div class="d-flex flex-wrap justify-content-center gap-3">
                                            <a href="<?= base_url('privacy-policy') ?>" class="btn btn-outline-primary">
                                                <i class="fas fa-user-shield me-2"></i> Privacy Policy
                                            </a>
                                            <button type="button" class="btn btn-primary"
                                                    onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
                                                <i class="fas fa-arrow-up me-2"></i> Back to Top
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="row mt-4">
                        <div class="col-md-4 mb-3">
                            <a href="<?= base_url('privacy-policy') ?>"
                               class="card border-0 shadow-sm text-decoration-none h-100">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-user-shield fa-2x text-primary mb-3"></i>
                                    <h6>Privacy Policy</h6>
                                    <p class="text-muted small mb-0">How we protect your data</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="<?= base_url('cookie-policy') ?>"
                               class="card border-0 shadow-sm text-decoration-none h-100">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-cookie-bite fa-2x text-primary mb-3"></i>
                                    <h6>Cookie Policy</h6>
                                    <p class="text-muted small mb-0">Our use of cookies</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="<?= url_to('contactus') ?>" class="card border-0 shadow-sm text-decoration-none h-100">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-question-circle fa-2x text-primary mb-3"></i>
                                    <h6>Contact Legal</h6>
                                    <p class="text-muted small mb-0">Questions about terms?</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="position-relative">
        <div class="shape overflow-hidden text-footer">
            <svg viewBox="0 0 2880 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 48H1437.5H2880V0H2160C1442.5 52 720 0 720 0H0V48Z" fill="currentColor"></path>
            </svg>
        </div>
    </div>

    <style>
        /* FAQ Navigation */
        .faq-nav .nav-link {
            border-radius: 30px;
            padding: 10px 20px;
            margin: 0 5px;
            transition: all 0.3s;
        }

        .faq-nav .nav-link.active {
            background: #a8cd93;
            color: white;
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
        }

        .faq-nav .nav-link:hover:not(.active) {
            background: rgba(78, 115, 223, 0.1);
        }

        /* Accordion Improvements */
        .accordion-item {
            border: 1px solid #e9ecef;
        }

        .accordion-button {
            font-weight: 500;
            padding: 1.25rem;
        }

        .accordion-button:not(.collapsed) {
            background-color: rgba(78, 115, 223, 0.05);
            color: #4e73df;
        }

        .accordion-body {
            padding: 1.5rem;
        }

        /* Terms Navigation */
        .terms-nav {
            position: sticky;
            top: 20px;
            z-index: 100;
            background: white;
            padding: 20px 0;
            border-bottom: 2px solid #f8f9fa;
        }

        .terms-content h5 {
            color: #4e73df;
            padding-bottom: 10px;
            border-bottom: 2px solid #f8f9fa;
            margin-bottom: 20px;
        }

        /* Smooth scrolling for anchor links */
        html {
            scroll-behavior: smooth;
        }

        /* Integration Cards */
        .integration-card {
            transition: transform 0.3s;
        }

        .integration-card:hover {
            transform: translateY(-5px);
        }

        /* Progress Bar for Compatibility */
        .compatibility-chart .progress {
            border-radius: 10px;
            overflow: hidden;
        }

        /* Alert Improvements */
        .alert {
            border-radius: 10px;
            border: none;
        }

        /* Card Hover Effects */
        .card {
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
        }

        /* Privacy Highlights */
        .privacy-highlights .border {
            border: 2px solid #e9ecef !important;
            transition: all 0.3s;
        }

        .privacy-highlights .border:hover {
            border-color: #4e73df !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Smooth scroll for terms navigation
            document.querySelectorAll('.terms-nav a').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 100,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Auto-expand FAQ based on URL hash
            const hash = window.location.hash;
            if (hash) {
                const targetElement = document.querySelector(hash);
                if (targetElement) {
                    // If it's an accordion item
                    const accordionButton = targetElement.querySelector('.accordion-button');
                    if (accordionButton) {
                        setTimeout(() => {
                            accordionButton.click();
                        }, 500);
                    }

                    // Smooth scroll to element
                    setTimeout(() => {
                        window.scrollTo({
                            top: targetElement.offsetTop - 100,
                            behavior: 'smooth'
                        });
                    }, 100);
                }
            }

            // Track FAQ interactions (for analytics)
            document.querySelectorAll('.accordion-button').forEach(button => {
                button.addEventListener('click', function () {
                    const question = this.textContent.trim();
                    const isExpanding = this.classList.contains('collapsed');

                    // You can send this data to your analytics service
                    console.log(`FAQ ${isExpanding ? 'expanded' : 'collapsed'}: ${question}`);
                });
            });

            // Terms acceptance tracking
            const acceptanceButton = document.querySelector('.acceptance-section button');
            if (acceptanceButton) {
                acceptanceButton.addEventListener('click', function () {
                    // You can add terms acceptance tracking here
                    console.log('User scrolled to review terms');
                });
            }
        });
    </script>
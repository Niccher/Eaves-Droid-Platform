<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-dark font-weight-bold">
                        <i class="fas fa-question-circle text-info mr-2"></i> FAQs
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                        <li class="breadcrumb-item active">FAQs</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-12 col-lg-8 mx-auto">

                <!-- Getting Started -->
                <div class="mb-4">
                    <h5 class="text-info font-weight-bold mb-3">
                        <i class="fas fa-rocket mr-2"></i> Getting Started
                    </h5>
                    <div id="accordion-getting-started">
                        <div class="card card-info card-outline shadow-sm border-0 rounded-lg mb-3">
                            <a class="d-block w-100" data-toggle="collapse" href="#faq1">
                                <div class="card-header bg-white border-bottom-0 rounded-lg">
                                    <h6 class="card-title w-100 mb-0 d-flex align-items-center">
                                        <span class="badge badge-info mr-3">1</span>
                                        <span class="text-dark">Is Eaves Droid free to use?</span>
                                        <i class="fas fa-chevron-down ml-auto text-muted"></i>
                                    </h6>
                                </div>
                            </a>
                            <div id="faq1" class="collapse show" data-parent="#accordion-getting-started">
                                <div class="card-body pt-0 text-muted">
                                    We offer a generous <strong>Free Plan</strong> which includes 1 device and 10 days of basic data logs. If you need advanced analytics, real-time push alerts, geofencing, wellbeing metrics, or to monitor multiple devices, you can upgrade to our paid plans (Gold or Platinum) via our billing portal.
                                    <div class="mt-2 small text-info"><i class="fas fa-info-circle mr-1"></i> The basic timeline features remain fully free.</div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-info card-outline shadow-sm border-0 rounded-lg mb-3">
                            <a class="d-block w-100" data-toggle="collapse" href="#faq2">
                                <div class="card-header bg-white border-bottom-0 rounded-lg">
                                    <h6 class="card-title w-100 mb-0 d-flex align-items-center">
                                        <span class="badge badge-info mr-3">2</span>
                                        <span class="text-dark">How do I get started?</span>
                                        <i class="fas fa-chevron-down ml-auto text-muted"></i>
                                    </h6>
                                </div>
                            </a>
                            <div id="faq2" class="collapse" data-parent="#accordion-getting-started">
                                <div class="card-body pt-0 text-muted">
                                    Getting started takes less than 2 minutes:
                                    <ol class="mt-2 mb-0">
                                        <li class="mb-1">Create a free account on our website</li>
                                        <li class="mb-1">Download the Eaves Droid Android client (APK from the Download page)</li>
                                        <li class="mb-1">Log in with your account credentials on the app</li>
                                        <li class="mb-1">Grant the required permissions (Contacts, SMS, Phone, Location)</li>
                                        <li class="mb-1">Let the app collect and upload your data securely in the background</li>
                                    </ol>
                                    <div class="mt-2 small text-info">Your dashboard automatically updates with insights as data arrives.</div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-info card-outline shadow-sm border-0 rounded-lg mb-3">
                            <a class="d-block w-100" data-toggle="collapse" href="#faq3">
                                <div class="card-header bg-white border-bottom-0 rounded-lg">
                                    <h6 class="card-title w-100 mb-0 d-flex align-items-center">
                                        <span class="badge badge-info mr-3">3</span>
                                        <span class="text-dark">Why is my data not appearing on the dashboard?</span>
                                        <i class="fas fa-chevron-down ml-auto text-muted"></i>
                                    </h6>
                                </div>
                            </a>
                            <div id="faq3" class="collapse" data-parent="#accordion-getting-started">
                                <div class="card-body pt-0 text-muted">
                                    If your data isn't showing up, try these steps:
                                    <ul class="mt-2 mb-0">
                                        <li class="mb-1">Ensure the Android app is running and has internet access</li>
                                        <li class="mb-1">Check that permissions (Contacts, SMS, Call Logs, Location) are granted in Android Settings</li>
                                        <li class="mb-1">Force close and reopen the app</li>
                                        <li class="mb-1">Wait a few minutes — initial sync can take time depending on data volume</li>
                                        <li class="mb-1">Refresh your browser dashboard</li>
                                    </ul>
                                    <div class="mt-2 small text-info">Data uploads happen in the background and may take a moment to process.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Android Client -->
                <div class="mb-4">
                    <h5 class="text-success font-weight-bold mb-3">
                        <i class="fab fa-android mr-2"></i> Android Client
                    </h5>
                    <div id="accordion-android">
                        <div class="card card-success card-outline shadow-sm border-0 rounded-lg mb-3">
                            <a class="d-block w-100" data-toggle="collapse" href="#faq4">
                                <div class="card-header bg-white border-bottom-0 rounded-lg">
                                    <h6 class="card-title w-100 mb-0 d-flex align-items-center">
                                        <span class="badge badge-success mr-3">4</span>
                                        <span class="text-dark">Where can I download the Android app?</span>
                                        <i class="fas fa-chevron-down ml-auto text-muted"></i>
                                    </h6>
                                </div>
                            </a>
                            <div id="faq4" class="collapse" data-parent="#accordion-android">
                                <div class="card-body pt-0 text-muted">
                                    The Eaves Droid Android client can be downloaded directly from our
                                    <a href="<?= base_url('download') ?>">Download page</a>. The APK is signed and safe to install.
                                    <div class="mt-2 small text-success">
                                        <i class="fas fa-shield-alt mr-1"></i> Requirements: Android 7.0+ (API 24+).
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-success card-outline shadow-sm border-0 rounded-lg mb-3">
                            <a class="d-block w-100" data-toggle="collapse" href="#faq5">
                                <div class="card-header bg-white border-bottom-0 rounded-lg">
                                    <h6 class="card-title w-100 mb-0 d-flex align-items-center">
                                        <span class="badge badge-success mr-3">5</span>
                                        <span class="text-dark">What permissions does the app need and why?</span>
                                        <i class="fas fa-chevron-down ml-auto text-muted"></i>
                                    </h6>
                                </div>
                            </a>
                            <div id="faq5" class="collapse" data-parent="#accordion-android">
                                <div class="card-body pt-0 text-muted">
                                    The app requires specific permissions to collect and analyze device data:
                                    <ul class="mt-2 mb-0">
                                        <li class="mb-1"><strong>Contacts</strong> — to read and analyze your contact list</li>
                                        <li class="mb-1"><strong>SMS</strong> — to read SMS messages for analysis and correlation</li>
                                        <li class="mb-1"><strong>Phone</strong> — to access call logs for timeline and frequency analysis</li>
                                        <li class="mb-1"><strong>Location</strong> — to map your device's location history</li>
                                        <li class="mb-1"><strong>Storage</strong> — to enumerate files and media on the device</li>
                                    </ul>
                                    <div class="mt-2 small text-success">
                                        <i class="fas fa-lock mr-1"></i> Data is encrypted in transit and never shared with third parties.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-success card-outline shadow-sm border-0 rounded-lg mb-3">
                            <a class="d-block w-100" data-toggle="collapse" href="#faq6">
                                <div class="card-header bg-white border-bottom-0 rounded-lg">
                                    <h6 class="card-title w-100 mb-0 d-flex align-items-center">
                                        <span class="badge badge-success mr-3">6</span>
                                        <span class="text-dark">Does the app drain my battery?</span>
                                        <i class="fas fa-chevron-down ml-auto text-muted"></i>
                                    </h6>
                                </div>
                            </a>
                            <div id="faq6" class="collapse" data-parent="#accordion-android">
                                <div class="card-body pt-0 text-muted">
                                    No. The Eaves Droid Android client is designed to be lightweight:
                                    <ul class="mt-2 mb-0">
                                        <li class="mb-1">Runs as a background service with minimal CPU usage</li>
                                        <li class="mb-1">Uploads data in batches only when connected to Wi-Fi (optional)</li>
                                        <li class="mb-1">Uses Android's JobScheduler to optimize battery consumption</li>
                                        <li class="mb-1">No constant location polling — location updates are triggered by significant movement</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Docker & Deployment -->
                <div class="mb-4">
                    <h5 class="text-primary font-weight-bold mb-3">
                        <i class="fab fa-docker mr-2"></i> Docker & Deployment
                    </h5>
                    <div id="accordion-docker">
                        <div class="card card-primary card-outline shadow-sm border-0 rounded-lg mb-3">
                            <a class="d-block w-100" data-toggle="collapse" href="#faq7">
                                <div class="card-header bg-white border-bottom-0 rounded-lg">
                                    <h6 class="card-title w-100 mb-0 d-flex align-items-center">
                                        <span class="badge badge-primary mr-3">7</span>
                                        <span class="text-dark">Is Eaves Droid available as a Docker image?</span>
                                        <i class="fas fa-chevron-down ml-auto text-muted"></i>
                                    </h6>
                                </div>
                            </a>
                            <div id="faq7" class="collapse" data-parent="#accordion-docker">
                                <div class="card-body pt-0 text-muted">
                                    Yes! Eaves Droid is fully containerized and available as a Docker image. The entire stack runs in containers:
                                    <ul class="mt-2 mb-0">
                                        <li class="mb-1"><strong>Web server</strong> — Apache with PHP 8.x (CodeIgniter 4)</li>
                                        <li class="mb-1"><strong>Database</strong> — MariaDB/MySQL for persistent storage</li>
                                        <li class="mb-1"><strong>Background workers</strong> — for data processing and ML analysis</li>
                                    </ul>
                                    <div class="mt-2 small text-primary">
                                        <i class="fas fa-terminal mr-1"></i> Deploy with a single <code>docker-compose up -d</code> command.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-primary card-outline shadow-sm border-0 rounded-lg mb-3">
                            <a class="d-block w-100" data-toggle="collapse" href="#faq8">
                                <div class="card-header bg-white border-bottom-0 rounded-lg">
                                    <h6 class="card-title w-100 mb-0 d-flex align-items-center">
                                        <span class="badge badge-primary mr-3">8</span>
                                        <span class="text-dark">How do I deploy with Docker Compose?</span>
                                        <i class="fas fa-chevron-down ml-auto text-muted"></i>
                                    </h6>
                                </div>
                            </a>
                            <div id="faq8" class="collapse" data-parent="#accordion-docker">
                                <div class="card-body pt-0 text-muted">
                                    <p>Clone the repository and run:</p>
                                    <pre class="bg-light p-3 rounded"><code>docker-compose up -d</code></pre>
                                    <p class="mb-0">This starts all services — web server, database, and background workers. The web UI will be available at <code>http://localhost:9007</code>.</p>
                                    <div class="mt-2 small text-primary">
                                        <i class="fas fa-info-circle mr-1"></i> Environment variables for database credentials, app key, and email settings are configured in a <code>.env</code> file.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-primary card-outline shadow-sm border-0 rounded-lg mb-3">
                            <a class="d-block w-100" data-toggle="collapse" href="#faq9">
                                <div class="card-header bg-white border-bottom-0 rounded-lg">
                                    <h6 class="card-title w-100 mb-0 d-flex align-items-center">
                                        <span class="badge badge-primary mr-3">9</span>
                                        <span class="text-dark">Can I run it without Docker?</span>
                                        <i class="fas fa-chevron-down ml-auto text-muted"></i>
                                    </h6>
                                </div>
                            </a>
                            <div id="faq9" class="collapse" data-parent="#accordion-docker">
                                <div class="card-body pt-0 text-muted">
                                    Yes. You can run Eaves Droid natively if you have PHP 8.x, MariaDB/MySQL, and Composer installed. However, Docker is the recommended and fully supported deployment method — it handles all dependencies, PHP extensions, and database setup automatically.
                                    <div class="mt-2 small text-primary">
                                        <i class="fas fa-check-circle mr-1"></i> Docker ensures consistent behavior across all environments.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data & Privacy -->
                <div class="mb-4">
                    <h5 class="text-secondary font-weight-bold mb-3">
                        <i class="fas fa-shield-alt mr-2"></i> Data & Privacy
                    </h5>
                    <div id="accordion-privacy">
                        <div class="card card-secondary card-outline shadow-sm border-0 rounded-lg mb-3">
                            <a class="d-block w-100" data-toggle="collapse" href="#faq10">
                                <div class="card-header bg-white border-bottom-0 rounded-lg">
                                    <h6 class="card-title w-100 mb-0 d-flex align-items-center">
                                        <span class="badge badge-secondary mr-3">10</span>
                                        <span class="text-dark">Can I delete my personal data?</span>
                                        <i class="fas fa-chevron-down ml-auto text-muted"></i>
                                    </h6>
                                </div>
                            </a>
                            <div id="faq10" class="collapse" data-parent="#accordion-privacy">
                                <div class="card-body pt-0 text-muted">
                                    Absolutely — you have full control over your data:
                                    <ul class="mt-2 mb-0">
                                        <li class="mb-1">Delete individual categories (SMS, Calls, Contacts, Apps) from your profile</li>
                                        <li class="mb-1">Remove all data at once with one click</li>
                                        <li class="mb-1">Delete your entire account and all associated data permanently</li>
                                    </ul>
                                    <div class="mt-2 small text-secondary">
                                        <i class="fas fa-user-cog mr-1"></i> Visit your <a href="<?= base_url('account/profile') ?>"><strong>Profile → Data Management</strong></a> section.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-secondary card-outline shadow-sm border-0 rounded-lg mb-3">
                            <a class="d-block w-100" data-toggle="collapse" href="#faq11">
                                <div class="card-header bg-white border-bottom-0 rounded-lg">
                                    <h6 class="card-title w-100 mb-0 d-flex align-items-center">
                                        <span class="badge badge-secondary mr-3">11</span>
                                        <span class="text-dark">How secure is my data?</span>
                                        <i class="fas fa-chevron-down ml-auto text-muted"></i>
                                    </h6>
                                </div>
                            </a>
                            <div id="faq11" class="collapse" data-parent="#accordion-privacy">
                                <div class="card-body pt-0 text-muted">
                                    Security is our top priority:
                                    <ul class="mt-2 mb-0">
                                        <li class="mb-1">End-to-end encryption for all data in transit (HTTPS/TLS) and at rest (AES-256)</li>
                                        <li class="mb-1">Data is only accessible to you — not shared with third parties</li>
                                        <li class="mb-1">No logs of your personal information are kept beyond what's needed for service operation</li>
                                        <li class="mb-1">Regular security audits and dependency updates</li>
                                        <li class="mb-1">CSRF and XSS protection built into every request</li>
                                    </ul>
                                    <div class="mt-2 small text-secondary">We never sell or share your personal data.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms of Service -->
                <div class="mb-4">
                    <h5 class="text-dark font-weight-bold mb-3">
                        <i class="fas fa-file-contract mr-2"></i> Terms of Service
                    </h5>
                    <div id="accordion-terms">
                        <div class="card card-dark card-outline shadow-sm border-0 rounded-lg mb-3">
                            <a class="d-block w-100" data-toggle="collapse" href="#terms">
                                <div class="card-header bg-white border-bottom-0 rounded-lg">
                                    <h6 class="card-title w-100 mb-0 d-flex align-items-center">
                                        <i class="fas fa-gavel mr-3 text-muted"></i>
                                        <span class="text-dark">Terms & Conditions</span>
                                        <i class="fas fa-chevron-down ml-auto text-muted"></i>
                                    </h6>
                                </div>
                            </a>
                            <div id="terms" class="collapse" data-parent="#accordion-terms">
                                <div class="card-body pt-0">
                                    <p class="text-muted mb-3">By using Eaves Droid, you agree to the following terms:</p>
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><strong class="text-dark">1. Free Service:</strong> <span class="text-muted">Eaves Droid is provided free of charge for personal, non-commercial use.</span></li>
                                        <li class="mb-2"><strong class="text-dark">2. Data Ownership:</strong> <span class="text-muted">You retain full ownership of all data you upload. We do not claim any rights to your personal information.</span></li>
                                        <li class="mb-2"><strong class="text-dark">3. Privacy:</strong> <span class="text-muted">Your data is private and will never be shared, sold, or used for advertising.</span></li>
                                        <li class="mb-2"><strong class="text-dark">4. Responsible Use:</strong> <span class="text-muted">You may only use the Android client on devices you own or have explicit permission to monitor.</span></li>
                                        <li class="mb-2"><strong class="text-dark">5. No Warranty:</strong> <span class="text-muted">The service is provided "as is" without warranties of any kind.</span></li>
                                        <li class="mb-2"><strong class="text-dark">6. Termination:</strong> <span class="text-muted">We reserve the right to terminate accounts that violate these terms.</span></li>
                                        <li class="mb-2"><strong class="text-dark">7. Changes:</strong> <span class="text-muted">These terms may be updated periodically. Continued use constitutes acceptance of changes.</span></li>
                                    </ul>
                                    <div class="text-muted small mt-3">
                                        <i class="fas fa-calendar-alt mr-1"></i> Last updated: December 12, 2025
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing & Payments -->
                <div class="mb-4">
                    <h5 class="text-warning font-weight-bold mb-3">
                        <i class="fas fa-credit-card mr-2"></i> Billing & Payments
                    </h5>
                    <div id="accordion-billing">
                        <div class="card card-warning card-outline shadow-sm border-0 rounded-lg mb-3">
                            <a class="d-block w-100" data-toggle="collapse" href="#billing-faq1">
                                <div class="card-header bg-white border-bottom-0 rounded-lg">
                                    <h6 class="card-title w-100 mb-0 d-flex align-items-center">
                                        <span class="badge badge-warning mr-3">8</span>
                                        <span class="text-dark">What payment methods are supported?</span>
                                        <i class="fas fa-chevron-down ml-auto text-muted"></i>
                                    </h6>
                                </div>
                            </a>
                            <div id="billing-faq1" class="collapse" data-parent="#accordion-billing">
                                <div class="card-body pt-0 text-muted">
                                    Through our partnership with <strong>Pesapal</strong>, we support all major payment networks in Kenya and internationally:
                                    <ul class="mt-2 mb-0">
                                        <li class="mb-1"><strong>Safaricom M-Pesa</strong> (via instant STK Push prompt on your phone or manual Paybill/Till instruction)</li>
                                        <li class="mb-1"><strong>Airtel Money</strong> (mobile money wallet transfers)</li>
                                        <li class="mb-1"><strong>Debit & Credit Cards</strong> (Visa, Mastercard, American Express issued by local banks like KCB, Equity, NCBA, Coop, etc. or international institutions)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="card card-warning card-outline shadow-sm border-0 rounded-lg mb-3">
                            <a class="d-block w-100" data-toggle="collapse" href="#billing-faq2">
                                <div class="card-header bg-white border-bottom-0 rounded-lg">
                                    <h6 class="card-title w-100 mb-0 d-flex align-items-center">
                                        <span class="badge badge-warning mr-3">9</span>
                                        <span class="text-dark">Are my payment details secure?</span>
                                        <i class="fas fa-chevron-down ml-auto text-muted"></i>
                                    </h6>
                                </div>
                            </a>
                            <div id="billing-faq2" class="collapse" data-parent="#accordion-billing">
                                <div class="card-body pt-0 text-muted">
                                    Yes. All transactions are securely routed, processed, and backed by <strong>Pesapal</strong> (which is fully PCI-DSS certified). Eaves Droid does not store or process your credit card numbers, CVVs, or mobile money PINs on our servers. All sensitive financial authentication occurs directly on Pesapal's secure checkout page.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Support -->
                <div class="text-center my-5 py-4 bg-light rounded-lg shadow-sm">
                    <i class="fas fa-headset fa-3x text-info mb-3"></i>
                    <h5 class="font-weight-bold text-dark">Still Have Questions?</h5>
                    <p class="text-muted mb-3">Our team is here to help with any issues or concerns.</p>
                    <a href="<?= base_url('contactus') ?>" class="btn btn-info px-5 py-2 rounded-pill shadow-sm">
                        <i class="fas fa-envelope mr-2"></i> Contact Support
                    </a>
                </div>

            </div>
        </div>
    </section>
</div>

<style>
    .card-header {
        cursor: pointer;
    }
    .card-header .fa-chevron-down {
        font-size: 0.8rem;
        transition: transform 0.25s ease;
    }
    .card-header.collapsed .fa-chevron-down {
        transform: rotate(0deg);
    }
    .card-header:not(.collapsed) .fa-chevron-down {
        transform: rotate(180deg);
    }
    .badge {
        min-width: 24px;
    }
    pre {
        border: 1px solid #dee2e6;
        margin-bottom: 0;
    }
    pre code {
        font-size: 0.9rem;
    }
</style>

<script>
$(function() {
    // Add collapsed class dynamically if collapsed on load
    $('.card-header[data-toggle="collapse"]').each(function() {
        var target = $(this).attr('href');
        if (!$(target).hasClass('show')) {
            $(this).addClass('collapsed');
        }
    });
});
</script>
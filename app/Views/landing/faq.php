<!-- Hero Section -->
<div class="bg-primary py-5 shadow-sm" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
    <div class="container text-center text-white">
        <h1 class="display-4 font-weight-bold">Frequently Asked Questions</h1>
        <p class="lead mb-0">Everything you need to know about Eaves Droid</p>
    </div>
</div>

<!-- FAQ Section -->
<section class="content py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <!-- General -->
                <h3 class="font-weight-bold text-primary mb-4">
                    <i class="fas fa-question-circle mr-2"></i>General
                </h3>
                <hr class="border-primary mb-4">

                <div class="accordion" id="faqGeneral">
                    <div class="card card-primary card-outline shadow-sm mb-3">
                        <div class="card-header p-0" id="genOne">
                            <h5 class="mb-0">
                                <button class="btn btn-link btn-block text-left py-3 text-bold text-dark" type="button" data-toggle="collapse" data-target="#collapseGenOne">
                                    <i class="fas fa-mobile-alt mr-2 text-primary"></i> What is Eaves Droid?
                                </button>
                            </h5>
                        </div>
                        <div id="collapseGenOne" class="collapse show" data-parent="#faqGeneral">
                            <div class="card-body">
                                <p class="mb-1">Eaves Droid is a comprehensive mobile data intelligence platform that collects, analyzes, and visualizes Android device data. It combines a native Android APK, a web dashboard, a machine learning engine, and Docker-based deployment to provide deep insights into device activity.</p>
                                <div class="row mt-3 text-center">
                                    <div class="col-3">
                                        <i class="fab fa-android fa-2x text-primary mb-1"></i>
                                        <small class="d-block text-muted">Android APK</small>
                                    </div>
                                    <div class="col-3">
                                        <i class="fas fa-tachometer-alt fa-2x text-primary mb-1"></i>
                                        <small class="d-block text-muted">Web Dashboard</small>
                                    </div>
                                    <div class="col-3">
                                        <i class="fas fa-brain fa-2x text-primary mb-1"></i>
                                        <small class="d-block text-muted">ML Engine</small>
                                    </div>
                                    <div class="col-3">
                                        <i class="fab fa-docker fa-2x text-primary mb-1"></i>
                                        <small class="d-block text-muted">Docker</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-primary card-outline shadow-sm mb-3">
                        <div class="card-header p-0" id="genTwo">
                            <h5 class="mb-0">
                                <button class="btn btn-link btn-block text-left py-3 text-bold text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapseGenTwo">
                                    <i class="fas fa-dollar-sign mr-2 text-primary"></i> Is Eaves Droid free?
                                </button>
                            </h5>
                        </div>
                        <div id="collapseGenTwo" class="collapse" data-parent="#faqGeneral">
                            <div class="card-body">
                                <p class="mb-0">Eaves Droid is open-source and self-hostable at no cost. The <strong>Free plan</strong> covers one device with core analysis. Paid tiers (<strong>Gold</strong> and <strong>Platinum</strong>) unlock more devices, longer history, and the full set of ML-engine detectors — see the <a href="<?= base_url('pricing') ?>" class="text-primary">pricing page</a> for details.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card card-primary card-outline shadow-sm mb-3">
                        <div class="card-header p-0" id="genThree">
                            <h5 class="mb-0">
                                <button class="btn btn-link btn-block text-left py-3 text-bold text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapseGenThree">
                                    <i class="fas fa-chart-bar mr-2 text-primary"></i> What kind of data can I analyze?
                                </button>
                            </h5>
                        </div>
                        <div id="collapseGenThree" class="collapse" data-parent="#faqGeneral">
                            <div class="card-body">
                                <p>Eaves Droid collects and analyzes a wide range of Android device data:</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="mb-0">
                                            <li><i class="fas fa-phone text-primary mr-1"></i> Call logs</li>
                                            <li><i class="fas fa-envelope text-primary mr-1"></i> SMS messages</li>
                                            <li><i class="fas fa-address-book text-primary mr-1"></i> Contacts</li>
                                            <li><i class="fas fa-map-marker-alt text-primary mr-1"></i> Locations</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="mb-0">
                                            <li><i class="fas fa-th-large text-primary mr-1"></i> Installed apps</li>
                                            <li><i class="fas fa-file text-primary mr-1"></i> FilesController &amp; media</li>
                                            <li><i class="fas fa-clock text-primary mr-1"></i> Activities</li>
                                            <li><i class="fas fa-microchip text-primary mr-1"></i> Device metrics</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Android ClientController -->
                <h3 class="font-weight-bold text-success mt-5 mb-4">
                    <i class="fab fa-android mr-2"></i>Android ClientController
                </h3>
                <hr class="border-success mb-4">

                <div class="accordion" id="faqAndroid">
                    <div class="card card-success card-outline shadow-sm mb-3">
                        <div class="card-header p-0" id="andOne">
                            <h5 class="mb-0">
                                <button class="btn btn-link btn-block text-left py-3 text-bold text-dark" type="button" data-toggle="collapse" data-target="#collapseAndOne">
                                    <i class="fas fa-play-circle mr-2 text-success"></i> How does the Android app work?
                                </button>
                            </h5>
                        </div>
                        <div id="collapseAndOne" class="collapse show" data-parent="#faqAndroid">
                            <div class="card-body">
                                <p>Getting started is simple:</p>
                                <ol class="mb-0">
                                    <li>Install the Eaves Droid APK on your Android device</li>
                                    <li>Authenticate using your unique token from the web dashboard</li>
                                    <li>Grant the required permissions when prompted</li>
                                    <li>The app runs as a background service, collecting data silently and automatically</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="card card-success card-outline shadow-sm mb-3">
                        <div class="card-header p-0" id="andTwo">
                            <h5 class="mb-0">
                                <button class="btn btn-link btn-block text-left py-3 text-bold text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapseAndTwo">
                                    <i class="fas fa-shield-alt mr-2 text-success"></i> What permissions are needed?
                                </button>
                            </h5>
                        </div>
                        <div id="collapseAndTwo" class="collapse" data-parent="#faqAndroid">
                            <div class="card-body">
                                <p>Each permission serves a specific purpose:</p>
                                <ul class="mb-0">
                                    <li><strong>Contacts</strong> — read your contact list for analysis and correlation</li>
                                    <li><strong>SMS</strong> — read and log SMS messages for text analysis</li>
                                    <li><strong>Phone</strong> — access call logs and device state</li>
                                    <li><strong>Location</strong> — collect GPS coordinates for location-based analysis</li>
                                    <li><strong>Storage</strong> — scan files and media on the device</li>
                                </ul>
                                <div class="callout callout-info mt-3 small">
                                    <i class="fas fa-info-circle mr-2 text-info"></i> All data is encrypted before leaving your device.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-success card-outline shadow-sm mb-3">
                        <div class="card-header p-0" id="andThree">
                            <h5 class="mb-0">
                                <button class="btn btn-link btn-block text-left py-3 text-bold text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapseAndThree">
                                    <i class="fas fa-battery-full mr-2 text-success"></i> Will it drain my battery?
                                </button>
                            </h5>
                        </div>
                        <div id="collapseAndThree" class="collapse" data-parent="#faqAndroid">
                            <div class="card-body">
                                <p class="mb-0">No. Eaves Droid is optimized for minimal power consumption. It uses Android's <code>WorkManager</code> to schedule periodic data collection, batches uploads to avoid constant network wake-ups, and keeps CPU usage low in the background. Most users report no noticeable impact on battery life.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card card-success card-outline shadow-sm mb-3">
                        <div class="card-header p-0" id="andFour">
                            <h5 class="mb-0">
                                <button class="btn btn-link btn-block text-left py-3 text-bold text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapseAndFour">
                                    <i class="fas fa-lock mr-2 text-success"></i> Is my data encrypted on the device?
                                </button>
                            </h5>
                        </div>
                        <div id="collapseAndFour" class="collapse" data-parent="#faqAndroid">
                            <div class="card-body">
                                <p class="mb-0">Yes. All data collected by the Android client is encrypted with AES using a user-supplied key before it leaves the device. This ensures that even if the device is compromised, your collected data remains unreadable without the encryption key. You self-host the backend, so no third party stores your data.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ML Analysis -->
                <h3 class="font-weight-bold text-info mt-5 mb-4">
                    <i class="fas fa-brain mr-2"></i>ML Analysis
                </h3>
                <hr class="border-info mb-4">

                <div class="accordion" id="faqMl">
                    <div class="card card-info card-outline shadow-sm mb-3">
                        <div class="card-header p-0" id="mlOne">
                            <h5 class="mb-0">
                                <button class="btn btn-link btn-block text-left py-3 text-bold text-dark" type="button" data-toggle="collapse" data-target="#collapseMlOne">
                                    <i class="fas fa-cogs mr-2 text-info"></i> What machine learning algorithms are used?
                                </button>
                            </h5>
                        </div>
                        <div id="collapseMlOne" class="collapse show" data-parent="#faqMl">
                            <div class="card-body">
                                <p>Eaves Droid runs a two-engine analysis stack. The webapp uses <strong>PHP-ML</strong> (KMeans clustering, DBSCAN, TF-IDF, Z-Score), and the dedicated <strong>Python ML engine</strong> adds seven detectors:</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="mb-0">
                                            <li><strong>Isolation Forest</strong> — call-log anomalies</li>
                                            <li><strong>One-Class SVM</strong> — system-state profiler</li>
                                            <li><strong>Contact Graph Outlier</strong> — relationship network</li>
                                            <li><strong>PCA Anomaly Scanner</strong> — app manifest checks</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="mb-0">
                                            <li><strong>SMS Phishing Heuristic</strong> — social-engineering language</li>
                                            <li><strong>Activity Sequence Predictor</strong> — usage-rhythm deviations</li>
                                            <li><strong>Suspicious File Scanner</strong> — hidden/encrypted payloads</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-info card-outline shadow-sm mb-3">
                        <div class="card-header p-0" id="mlTwo">
                            <h5 class="mb-0">
                                <button class="btn btn-link btn-block text-left py-3 text-bold text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapseMlTwo">
                                    <i class="fas fa-file-pdf mr-2 text-info"></i> Can I export my analysis reports?
                                </button>
                            </h5>
                        </div>
                        <div id="collapseMlTwo" class="collapse" data-parent="#faqMl">
                            <div class="card-body">
                                <p class="mb-0">Absolutely. Eaves Droid generates comprehensive PDF reports using <strong>Dompdf</strong>. Each report includes 15 detailed sections covering call logs, SMS analysis, location history, contact correlation, app usage, and more. Reports can be downloaded directly from the dashboard with a single click.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card card-info card-outline shadow-sm mb-3">
                        <div class="card-header p-0" id="mlThree">
                            <h5 class="mb-0">
                                <button class="btn btn-link btn-block text-left py-3 text-bold text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapseMlThree">
                                    <i class="fas fa-project-diagram mr-2 text-info"></i> What is correlation mapping?
                                </button>
                            </h5>
                        </div>
                        <div id="collapseMlThree" class="collapse" data-parent="#faqMl">
                            <div class="card-body">
                                <p class="mb-0">Correlation mapping links related calls and SMS messages by contact, creating a visual graph of communication patterns over time. This helps identify relationships, frequency trends, and behavioral changes through an interactive network visualization in the dashboard.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Docker & Deployment -->
                <h3 class="font-weight-bold text-warning mt-5 mb-4">
                    <i class="fab fa-docker mr-2"></i>Docker &amp; Deployment
                </h3>
                <hr class="border-warning mb-4">

                <div class="accordion" id="faqDocker">
                    <div class="card card-warning card-outline shadow-sm mb-3">
                        <div class="card-header p-0" id="docOne">
                            <h5 class="mb-0">
                                <button class="btn btn-link btn-block text-left py-3 text-bold text-dark" type="button" data-toggle="collapse" data-target="#collapseDocOne">
                                    <i class="fas fa-server mr-2 text-warning"></i> Can I self-host Eaves Droid?
                                </button>
                            </h5>
                        </div>
                        <div id="collapseDocOne" class="collapse show" data-parent="#faqDocker">
                            <div class="card-body">
                                <p class="mb-0">Yes. Eaves Droid provides a production-ready Docker image. Deployment is as simple as running <code>docker compose up --build -d</code> in the parent directory of the three repos. The stack includes all necessary dependencies and services, giving you full control over your instance.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card card-warning card-outline shadow-sm mb-3">
                        <div class="card-header p-0" id="docTwo">
                            <h5 class="mb-0">
                                <button class="btn btn-link btn-block text-left py-3 text-bold text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapseDocTwo">
                                    <i class="fas fa-layer-group mr-2 text-warning"></i> What's in the Docker stack?
                                </button>
                            </h5>
                        </div>
                        <div id="collapseDocTwo" class="collapse" data-parent="#faqDocker">
                            <div class="card-body">
                                <p>The Docker stack includes everything needed to run Eaves Droid:</p>
                                <ul class="mb-0">
                                    <li><strong>Apache + PHP 8.3</strong> — serves the web dashboard and API</li>
                                    <li><strong>MySQL 8.4</strong> — relational database for all collected data</li>
                                    <li><strong>Python ML engine</strong> — FastAPI service running the 7 anomaly detectors</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="card card-warning card-outline shadow-sm mb-3">
                        <div class="card-header p-0" id="docThree">
                            <h5 class="mb-0">
                                <button class="btn btn-link btn-block text-left py-3 text-bold text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapseDocThree">
                                    <i class="fas fa-terminal mr-2 text-warning"></i> Is there a native deployment option?
                                </button>
                            </h5>
                        </div>
                        <div id="collapseDocThree" class="collapse" data-parent="#faqDocker">
                            <div class="card-body">
                                <p class="mb-0">Yes. If you prefer not to use Docker, Eaves Droid can be deployed natively on any server running <strong>PHP 8.3</strong> with <strong>MySQL 8.4</strong> and <strong>Composer</strong>. Clone the repository, run <code>composer install</code>, configure your <code>.env</code> file, and set up the database migrations. The Python ML engine can be run separately with <code>uvicorn app.main:app</code>.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Privacy & Security -->
                <h3 class="font-weight-bold text-danger mt-5 mb-4">
                    <i class="fas fa-shield-alt mr-2"></i>Privacy &amp; Security
                </h3>
                <hr class="border-danger mb-4">

                <div class="accordion" id="faqPrivacy">
                    <div class="card card-danger card-outline shadow-sm mb-3">
                        <div class="card-header p-0" id="privOne">
                            <h5 class="mb-0">
                                <button class="btn btn-link btn-block text-left py-3 text-bold text-dark" type="button" data-toggle="collapse" data-target="#collapsePrivOne">
                                    <i class="fas fa-lock mr-2 text-danger"></i> How is my data protected?
                                </button>
                            </h5>
                        </div>
                        <div id="collapsePrivOne" class="collapse show" data-parent="#faqPrivacy">
                            <div class="card-body">
                                <p>Eaves Droid uses multiple layers of protection:</p>
                                <ul class="mb-0">
                                    <li><strong>In transit:</strong> uploads are encrypted with AES (user-supplied key) before leaving the device; use HTTPS/TLS on your server for transport encryption</li>
                                    <li><strong>On device:</strong> data is encrypted before leaving the Android client</li>
                                    <li><strong>Self-hosted:</strong> data is stored on infrastructure you control — never sent to third parties</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="card card-danger card-outline shadow-sm mb-3">
                        <div class="card-header p-0" id="privTwo">
                            <h5 class="mb-0">
                                <button class="btn btn-link btn-block text-left py-3 text-bold text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapsePrivTwo">
                                    <i class="fas fa-user-secret mr-2 text-danger"></i> Who has access to my data?
                                </button>
                            </h5>
                        </div>
                        <div id="collapsePrivTwo" class="collapse" data-parent="#faqPrivacy">
                            <div class="card-body">
                                <p class="mb-0">Only you. Your data is never shared with third parties, sold, or used for advertising. Eaves Droid is designed with privacy as a core principle — you retain full ownership and exclusive access to all collected information.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card card-danger card-outline shadow-sm mb-3">
                        <div class="card-header p-0" id="privThree">
                            <h5 class="mb-0">
                                <button class="btn btn-link btn-block text-left py-3 text-bold text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapsePrivThree">
                                    <i class="fas fa-trash-alt mr-2 text-danger"></i> Can I delete my data?
                                </button>
                            </h5>
                        </div>
                        <div id="collapsePrivThree" class="collapse" data-parent="#faqPrivacy">
                            <div class="card-body">
                                <p class="mb-0">Yes, you have full control. You can delete individual data categories (call logs, SMS, locations, etc.) or remove your entire account and all associated data at any time from the dashboard settings. Data deletion is immediate and permanent.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing & Payments -->
                <h3 class="font-weight-bold text-warning mb-4 mt-5">
                    <i class="fas fa-credit-card mr-2"></i>Billing & Payments
                </h3>
                <hr class="border-warning mb-4">

                <div class="accordion mb-5" id="faqBilling">
                    <div class="card card-warning card-outline shadow-sm mb-3">
                        <div class="card-header p-0" id="billOne">
                            <h5 class="mb-0">
                                <button class="btn btn-link btn-block text-left py-3 text-bold text-dark" type="button" data-toggle="collapse" data-target="#collapseBillOne">
                                    <i class="fas fa-money-check-alt mr-2 text-warning"></i> What payment methods are supported?
                                </button>
                            </h5>
                        </div>
                        <div id="collapseBillOne" class="collapse show" data-parent="#faqBilling">
                            <div class="card-body">
                                <p class="mb-2">Through our partnership with <strong>Pesapal</strong>, we support all major payment networks in Kenya and internationally:</p>
                                <ul class="mb-0">
                                    <li><strong>Safaricom M-Pesa</strong> (via instant STK Push prompt on your phone or manual Paybill/Till instruction)</li>
                                    <li><strong>Airtel Money</strong> (mobile money wallet transfers)</li>
                                    <li><strong>Debit & Credit Cards</strong> (Visa, Mastercard, American Express issued by local banks like KCB, Equity, NCBA, Coop, etc. or international institutions)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="card card-warning card-outline shadow-sm mb-3">
                        <div class="card-header p-0" id="billTwo">
                            <h5 class="mb-0">
                                <button class="btn btn-link btn-block text-left py-3 text-bold text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapseBillTwo">
                                    <i class="fas fa-shield-alt mr-2 text-warning"></i> Are my payment details secure?
                                </button>
                            </h5>
                        </div>
                        <div id="collapseBillTwo" class="collapse" data-parent="#faqBilling">
                            <div class="card-body">
                                <p class="mb-0">Yes. All transactions are securely routed, processed, and backed by <strong>Pesapal</strong> (which is fully PCI-DSS certified). Eaves Droid does not store or process your credit card numbers, CVVs, or mobile money PINs on our servers. All sensitive financial authentication occurs directly on Pesapal's secure checkout page.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA -->
                <div class="text-center mt-5 pt-4 border-top">
                    <h4 class="font-weight-bold mb-2">Still have questions?</h4>
                    <p class="text-muted mb-4">We're here to help. Get in touch with our team.</p>
                    <a href="<?= url_to('contact') ?>" class="btn btn-primary btn-lg px-5 shadow-sm">
                        <i class="fas fa-headset mr-2"></i> Contact Us
                    </a>
                    <a href="<?= base_url('faqs_terms') ?>" class="btn btn-outline-secondary btn-lg px-4 ml-2 shadow-sm">
                        <i class="fas fa-file-alt mr-2"></i> Terms &amp; Policies
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>

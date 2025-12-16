    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>FAQs & Terms of Service</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                            <li class="breadcrumb-item active">FAQs & Terms</li>
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
                    <!-- FAQ 1 -->
                    <div class="card card-info card-outline">
                        <a class="d-block w-100" data-toggle="collapse" href="#collapseOne">
                            <div class="card-header">
                                <h4 class="card-title w-100">
                                    1. Is Prj Images free to use?
                                </h4>
                            </div>
                        </a>
                        <div id="collapseOne" class="collapse show" data-parent="#accordion">
                            <div class="card-body">
                                Yes! Prj Images is completely free for personal use. There are no subscription fees, hidden
                                charges, or paid plans.
                                <div class="text-muted mt-2">We believe everyone should have access to powerful data
                                    insights without cost barriers.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="card card-primary card-outline">
                        <a class="d-block w-100" data-toggle="collapse" href="#collapseTwo">
                            <div class="card-header">
                                <h4 class="card-title w-100">
                                    2. How do I get started?
                                </h4>
                            </div>
                        </a>
                        <div id="collapseTwo" class="collapse" data-parent="#accordion">
                            <div class="card-body">
                                Getting started is simple and takes less than 2 minutes:
                                <ol>
                                    <li>Create a free account on our website</li>
                                    <li>Download the Android client app</li>
                                    <li>Log in with your account credentials</li>
                                    <li>Grant the required permissions</li>
                                    <li>Let the app collect and upload your data securely</li>
                                </ol>
                                <div class="text-muted">Your dashboard will automatically update with insights as data
                                    arrives.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="card card-warning card-outline">
                        <a class="d-block w-100" data-toggle="collapse" href="#collapseThree">
                            <div class="card-header">
                                <h4 class="card-title w-100">
                                    3. Why is my data not appearing on the dashboard?
                                </h4>
                            </div>
                        </a>
                        <div id="collapseThree" class="collapse" data-parent="#accordion">
                            <div class="card-body">
                                If your data isn't showing up, try these steps:
                                <ul>
                                    <li>Ensure the Android app is running and has internet access</li>
                                    <li>Check that permissions (Contacts, SMS, Call Logs) are granted</li>
                                    <li>Force close and reopen the app</li>
                                    <li>Wait a few minutes — initial sync can take time</li>
                                    <li>Refresh your browser dashboard</li>
                                </ul>
                                <div class="text-muted">Data uploads happen in the background and may take a moment to
                                    process.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="card card-success card-outline">
                        <adiss class="d-block w-100" data-toggle="collapse" href="#collapseFour">
                            <div class="card-header">
                                <h4 class="card-title w-100">
                                    4. Can I delete my personal data?
                                </h4>
                            </div>
                            </a>
                            <div id="collapseFour" class="collapse" data-parent="#accordion">
                                <div class="card-body">
                                    Absolutely — you have full control over your data:
                                    <ul>
                                        <li>Delete individual categories (SMS, Calls, Contacts, Apps) from your profile</li>
                                        <li>Remove all data at once with one click</li>
                                        <li>Delete your entire account and all associated data permanently</li>
                                    </ul>
                                    <div class="text-muted">Visit your <a href="<?= base_url('account/profile') ?>"><strong>Profile
                                                → Data Management</strong></a> section to manage or delete your data
                                        anytime.
                                    </div>
                                </div>
                            </div>
                    </div>

                    <!-- FAQ 5 -->
                    <div class="card card-danger card-outline">
                        <a class="d-block w-100" data-toggle="collapse" href="#collapseFive">
                            <div class="card-header">
                                <h4 class="card-title w-100">
                                    5. How secure is my data?
                                </h4>
                            </div>
                        </a>
                        <div id="collapseFive" class="collapse" data-parent="#accordion">
                            <div class="card-body">
                                Security is our top priority:
                                <ul>
                                    <li>End-to-end encryption for all data in transit and at rest</li>
                                    <li>Data is only accessible to you — not shared with third parties</li>
                                    <li>No logs of your personal information are kept beyond what's needed for service</li>
                                    <li>Regular security audits and updates</li>
                                </ul>
                                <div class="text-muted">We never sell or share your personal data.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms of Service Section -->
                    <div class="card card-dark card-outline mt-4">
                        <a class="d-block w-100" data-toggle="collapse" href="#collapseTerms">
                            <div class="card-header bg-grey text-white">
                                <h4 class="card-title w-100">
                                    <i class="fas fa-file-contract me-2"></i> Terms of Service
                                </h4>
                            </div>
                        </a>
                        <div id="collapseTerms" class="collapse" data-parent="#accordion">
                            <div class="card-body">
                                <p class="mb-3">By using Prj Images, you agree to the following terms:</p>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><strong>1. Free Service:</strong> Prj Images is provided free of charge
                                        for personal, non-commercial use.
                                    </li>
                                    <li class="mb-2"><strong>2. Data Ownership:</strong> You retain full ownership of all
                                        data you upload. We do not claim any rights to your personal information.
                                    </li>
                                    <li class="mb-2"><strong>3. Privacy:</strong> Your data is private and will never be
                                        shared, sold, or used for advertising.
                                    </li>
                                    <li class="mb-2"><strong>4. Responsible Use:</strong> You may only use the Android
                                        client on devices you own or have explicit permission to monitor.
                                    </li>
                                    <li class="mb-2"><strong>5. No Warranty:</strong> The service is provided "as is"
                                        without warranties of any kind.
                                    </li>
                                    <li class="mb-2"><strong>6. Termination:</strong> We reserve the right to terminate
                                        accounts that violate these terms.
                                    </li>
                                    <li class="mb-2"><strong>7. Changes:</strong> These terms may be updated periodically.
                                        Continued use constitutes acceptance of changes.
                                    </li>
                                </ul>
                                <div class="text-muted mt-3 small">
                                    Last updated: December 12, 2025
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support Section -->
            <div class="row mt-5">
                <div class="col-12 text-center">
                    <h5 class="text-muted">Still Have Questions?</h5>
                    <p class="text-muted">Our team is here to help you with any issues or concerns.</p>
                    <a href="<?= base_url('contactus') ?>" class="btn btn-primary btn-lg">Contact Support</a>
                </div>
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Footer Info -->
    <div class="mt-5 text-center text-muted small">
        <a href="<?= base_url('privacy') ?>">Privacy Policy</a> •
        <a href="<?= base_url('faqs') ?>">FAQs & Terms</a> •
        <a href="<?= base_url('contactus') ?>">Contact Us</a>
    </div>

    <style>
        .card-header {
            background: #f8f9fa;
            border: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .card-header:hover {
            background: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .card {
            margin-bottom: 1rem;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .card-body {
            background: #fff;
            line-height: 1.7;
        }

        .card-title {
            font-weight: 600;
            color: #94989c;
        }

        .btn-primary {
            background: #4e73df;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 500;
        }

        .btn-primary:hover {
            background: #375bb8;
        }
    </style>


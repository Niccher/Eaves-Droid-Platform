    <!-- Hero Section -->
    <div class="bg-primary py-5 shadow-sm" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
        <div class="container text-center text-white">
            <h1 class="display-4 font-weight-bold">Contact Us</h1>
            <p class="lead">We're here to help — reach out to the Eaves Droid team</p>
        </div>
    </div>

    <!-- Contact Section -->
    <section class="content py-5">
        <div class="container">
            <!-- Contact Methods -->
            <div class="row mb-5">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="info-box shadow-sm h-100">
                        <span class="info-box-icon bg-primary"><i class="fas fa-envelope"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-bold">Email Support</span>
                            <span class="info-box-number small text-muted font-weight-light">support@eavesdroid.com</span>
                            <span class="progress-description small">Response: 24 hours</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="info-box shadow-sm h-100">
                        <span class="info-box-icon bg-success"><i class="fas fa-headset"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-bold">Technical Support</span>
                            <span class="info-box-number small text-muted font-weight-light">tech@eavesdroid.com</span>
                            <span class="progress-description small">Response: 12 hours</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="info-box shadow-sm h-100">
                        <span class="info-box-icon bg-warning"><i class="fas fa-briefcase"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-bold text-white">Business Inquiries</span>
                            <span class="info-box-number small text-muted font-weight-light">business@eavesdroid.com</span>
                            <span class="progress-description small">Response: 48 hours</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="info-box shadow-sm h-100">
                        <span class="info-box-icon bg-danger"><i class="fab fa-github"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-bold">GitHub Issues</span>
                            <span class="info-box-number small text-muted font-weight-light">Report bugs & feature requests</span>
                            <span class="progress-description small"><a href="https://github.com/Niccher/Eaves-Droid-WebApp/issues" target="_blank" class="text-danger">Open an Issue →</a></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title text-bold"><i class="fas fa-paper-plane mr-2 text-primary"></i>Send us a Message</h3>
                        </div>
                        <div class="card-body">
                            <?php if (function_exists('auth') && auth()->loggedIn()): ?>
                                <div class="alert alert-info shadow-sm">
                                    <h5><i class="icon fas fa-info-circle"></i> Need instant help?</h5>
                                    You are logged in! Head over to our <a href="<?= base_url('users/support') ?>" class="text-bold text-white text-decoration-underline" style="text-decoration: underline;">Live Support Chat</a> for a faster response.
                                </div>
                            <?php endif; ?>

                            <?php if (session()->has('success')): ?>
                                <div class="alert alert-success alert-dismissible shadow-sm">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <h5><i class="icon fas fa-check"></i> Success!</h5>
                                    <?= session('success') ?>
                                </div>
                            <?php endif; ?>

                            <?php if (session()->has('error')): ?>
                                <div class="alert alert-danger alert-dismissible shadow-sm">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <h5><i class="icon fas fa-ban"></i> Error!</h5>
                                    <?= session('error') ?>
                                </div>
                            <?php endif; ?>

                            <form class="needs-validation" method="post" action="<?= url_to('contact') ?>" enctype="multipart/form-data" novalidate>
                                <?= csrf_field() ?>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="contact_name">Full Name *</label>
                                            <input type="text" name="contact_name" id="contact_name" 
                                                   class="form-control <?= session('errors.contact_name') ? 'is-invalid' : '' ?>" 
                                                   placeholder="Enter your name" value="<?= old('contact_name') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="contact_email">Email Address *</label>
                                            <input type="email" name="contact_email" id="contact_email" 
                                                   class="form-control <?= session('errors.contact_email') ? 'is-invalid' : '' ?>" 
                                                   placeholder="Enter your email" value="<?= old('contact_email') ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="contact_subject">Inquiry Type</label>
                                    <select name="contact_subject" id="contact_subject" 
                                            class="form-control <?= session('errors.contact_subject') ? 'is-invalid' : '' ?>">
                                        <option value="">Select a category</option>
                                        <option value="General Inquiry" <?= old('contact_subject') == 'General Inquiry' ? 'selected' : '' ?>>General Inquiry</option>
                                        <option value="Technical Support" <?= old('contact_subject') == 'Technical Support' ? 'selected' : '' ?>>Technical Support</option>
                                        <option value="Feature Request" <?= old('contact_subject') == 'Feature Request' ? 'selected' : '' ?>>Feature Request</option>
                                        <option value="Bug Report" <?= old('contact_subject') == 'Bug Report' ? 'selected' : '' ?>>Bug Report</option>
                                        <option value="Business Inquiry" <?= old('contact_subject') == 'Business Inquiry' ? 'selected' : '' ?>>Business Inquiry</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="contact_message">Your Message *</label>
                                    <textarea name="contact_message" id="contact_message" rows="5" 
                                              class="form-control <?= session('errors.contact_message') ? 'is-invalid' : '' ?>" 
                                              placeholder="How can we help you?" required><?= old('contact_message') ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="contact_attachment">Attachment (Optional)</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input <?= session('errors.contact_attachment') ? 'is-invalid' : '' ?>" id="contact_attachment" name="contact_attachment" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx">
                                        <label class="custom-file-label" for="contact_attachment">Choose file</label>
                                    </div>
                                    <?php if(session('errors.contact_attachment')): ?>
                                        <div class="invalid-feedback d-block"><?= session('errors.contact_attachment') ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group mb-4">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="privacy_consent" 
                                               name="privacy_consent" value="1" required <?= old('privacy_consent') ? 'checked' : '' ?>>
                                        <label class="custom-control-label font-weight-normal" for="privacy_consent">
                                            I agree to the <a href="<?= base_url('privacy-policy') ?>" target="_blank">Privacy Policy</a>
                                        </label>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                        <i class="fas fa-paper-plane mr-2"></i>Send Message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="container mt-5">
            <h3 class="text-center mb-4">Frequently Asked Questions</h3>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="accordion" id="faqAccordion">
                        <div class="card shadow-sm">
                            <div class="card-header" id="headingOne">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left font-weight-bold" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        How long does it take to get a response?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#faqAccordion">
                                <div class="card-body">
                                    We aim to respond to all general inquiries within 24 hours. Technical support and bug reports from active users may receive priority responses within 12 hours.
                                </div>
                            </div>
                        </div>
                        <div class="card shadow-sm">
                            <div class="card-header" id="headingTwo">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left collapsed font-weight-bold" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        Where can I find my application logs?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#faqAccordion">
                                <div class="card-body">
                                    If you are experiencing a technical issue with the Android app, please attach a screenshot or PDF of the issue using the attachment field above.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


<?php //include('footer_landing.php'); ?>

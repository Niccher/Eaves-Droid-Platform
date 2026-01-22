<?php //include('head_landing.php'); ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 font-weight-bold mb-4">Contact Us</h1>
                    <p class="lead mb-4">Get in touch with our support team</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-5">
        <div class="container">
            <!-- Contact Methods -->
            <div class="row mb-5">
                <div class="col-md-4 mb-4">
                    <div class="contact-method">
                        <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                        <h4 class="text-primary mb-2">Email Support</h4>
                        <p class="text-muted mb-3">For general inquiries & questions</p>
                        <a href="mailto:support@prjimages.com" class="text-primary d-block">support@prjimages.com</a>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-clock mr-1"></i> Response time: 24 hours
                        </small>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="contact-method">
                        <i class="fas fa-headset fa-3x text-success mb-3"></i>
                        <h4 class="text-success mb-2">Technical Support</h4>
                        <p class="text-muted mb-3">For app-related issues & bugs</p>
                        <a href="mailto:tech@prjimages.com" class="text-success d-block">tech@prjimages.com</a>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-clock mr-1"></i> Response time: 12 hours
                        </small>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="contact-method">
                        <i class="fas fa-briefcase fa-3x text-warning mb-3"></i>
                        <h4 class="text-warning mb-2">Business Inquiries</h4>
                        <p class="text-muted mb-3">For partnerships & enterprise</p>
                        <a href="mailto:business@prjimages.com" class="text-warning d-block">business@prjimages.com</a>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-clock mr-1"></i> Response time: 48 hours
                        </small>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-lg-5">
                            <h2 class="section-title mb-4">Send us a Message</h2>

                            <?php if (session()->has('success')): ?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <?= session('success') ?>
                                    <button type="button" class="close" data-dismiss="alert">
                                        <span>&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>

                            <?php if (session()->has('error')): ?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    <?= session('error') ?>
                                    <button type="button" class="close" data-dismiss="alert">
                                        <span>&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>

                            <form class="needs-validation" method="post" action="<?= url_to('contact') ?>" novalidate>
                                <?= csrf_field() ?>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_name" class="form-label">
                                            <i class="fas fa-user mr-1 text-primary"></i>Your Name *
                                        </label>
                                        <input type="text"
                                               name="contact_name"
                                               id="contact_name"
                                               class="form-control <?= session('errors.contact_name') ? 'is-invalid' : '' ?>"
                                               placeholder="John Doe"
                                               value="<?= old('contact_name') ?>"
                                               required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="contact_email" class="form-label">
                                            <i class="fas fa-envelope mr-1 text-primary"></i>Your Email *
                                        </label>
                                        <input type="email"
                                               name="contact_email"
                                               id="contact_email"
                                               class="form-control <?= session('errors.contact_email') ? 'is-invalid' : '' ?>"
                                               placeholder="john@example.com"
                                               value="<?= old('contact_email') ?>"
                                               required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="contact_subject" class="form-label">
                                        <i class="fas fa-tag mr-1 text-primary"></i>Inquiry Type
                                    </label>
                                    <select name="contact_subject"
                                            id="contact_subject"
                                            class="form-control <?= session('errors.contact_subject') ? 'is-invalid' : '' ?>">
                                        <option value="">Select a category</option>
                                        <option value="General Inquiry" <?= old('contact_subject') == 'General Inquiry' ? 'selected' : '' ?>>General Inquiry</option>
                                        <option value="Technical Support" <?= old('contact_subject') == 'Technical Support' ? 'selected' : '' ?>>Technical Support</option>
                                        <option value="Feature Request" <?= old('contact_subject') == 'Feature Request' ? 'selected' : '' ?>>Feature Request</option>
                                        <option value="Bug Report" <?= old('contact_subject') == 'Bug Report' ? 'selected' : '' ?>>Bug Report</option>
                                        <option value="Business Inquiry" <?= old('contact_subject') == 'Business Inquiry' ? 'selected' : '' ?>>Business Inquiry</option>
                                        <option value="Other" <?= old('contact_subject') == 'Other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="contact_message" class="form-label">
                                        <i class="fas fa-comment mr-1 text-primary"></i>Your Message *
                                    </label>
                                    <textarea name="contact_message"
                                              id="contact_message"
                                              rows="5"
                                              class="form-control <?= session('errors.contact_message') ? 'is-invalid' : '' ?>"
                                              placeholder="Tell us how we can help you..."
                                              required><?= old('contact_message') ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input type="checkbox"
                                               class="form-check-input"
                                               id="privacy_consent"
                                               name="privacy_consent"
                                               value="1"
                                            <?= old('privacy_consent') ? 'checked' : '' ?>
                                               required>
                                        <label class="form-check-label" for="privacy_consent">
                                            I agree to the <a href="<?= base_url('privacy-policy') ?>" target="_blank" class="text-primary">Privacy Policy</a>
                                        </label>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane mr-2"></i>Send Message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php //include('footer_landing.php'); ?>
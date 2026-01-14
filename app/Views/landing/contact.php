    <!-- Start  -->
    <!-- Contact Methods -->
    <section class="section bg-primary">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mt-4">
                    <div class="contact-method text-center p-4 rounded bg-white shadow hover-lift">
                        <div class="icon-wrapper bg-primary rounded-circle p-3 d-inline-flex mb-3">
                            <i class="fas fa-envelope fa-2x text-white"></i>
                        </div>
                        <h5 class="mb-2">Email Support</h5>
                        <p class="text-muted mb-2">For general inquiries & questions</p>
                        <a href="mailto:support@prjimages.com" class="text-primary d-block mb-2">support@prjimages.com</a>
                        <small class="text-muted d-block">
                            <i class="fas fa-clock me-1"></i> Response time: 24 hours
                        </small>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mt-4">
                    <div class="contact-method text-center p-4 rounded bg-white shadow hover-lift">
                        <div class="icon-wrapper bg-primary rounded-circle p-3 d-inline-flex mb-3">
                            <i class="fas fa-headset fa-2x text-white"></i>
                        </div>
                        <h5 class="mb-2">Technical Support</h5>
                        <p class="text-muted mb-2">For app-related issues & bugs</p>
                        <a href="mailto:tech@prjimages.com" class="text-primary d-block mb-2">tech@prjimages.com</a>
                        <small class="text-muted d-block">
                            <i class="fas fa-clock me-1"></i> Response time: 12 hours
                        </small>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mt-4">
                    <div class="contact-method text-center p-4 rounded bg-white shadow hover-lift">
                        <div class="icon-wrapper bg-primary rounded-circle p-3 d-inline-flex mb-3">
                            <i class="fas fa-building fa-2x text-white"></i>
                        </div>
                        <h5 class="mb-2">Business Inquiries</h5>
                        <p class="text-muted mb-2">For partnerships & enterprise</p>
                        <a href="mailto:business@prjimages.com" class="text-primary d-block mb-2">business@prjimages.com</a>
                        <small class="text-muted d-block">
                            <i class="fas fa-clock me-1"></i> Response time: 48 hours
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="section">
        <div class="container mt-50 mt-60">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="section-title mb-4 pb-2">
                        <span class="badge bg-primary mb-2">Contact Us</span>
                        <h4 class="title mb-4">Get In Touch!</h4>
                        <p class="text-muted para-desc mx-auto mb-0">Have questions about Prj Images? We're here to help!
                            Send us your message and we'll get back to you promptly.</p>
                    </div>
                </div>
                <!--end col-->
            </div>
            <!--end row-->

            <!-- Support Status -->
            <div class="row justify-content-center mb-4">
                <div class="col-lg-8">
                    <div class="support-status alert alert-info d-flex align-items-center">
                        <div class="status-indicator me-3">
                            <div class="status-dot online"></div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Support Status: <span class="badge bg-success">Online</span></h6>
                            <p class="mb-0 small">Current response time: Less than 2 hours • Available: Mon-Fri, 9AM-6PM
                                EST</p>
                        </div>
                        <div class="status-time text-end">
                            <small class="text-muted">Updated: Just now</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="contact-form-wrapper bg-white rounded shadow p-4 p-lg-5">
                        <!-- Success/Error Messages -->
                        <?php if (session()->has('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= session('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->has('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?= session('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->has('errors')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <h6 class="alert-heading mb-2">Please fix the following errors:</h6>
                                <ul class="mb-0">
                                    <?php foreach (session('errors') as $error): ?>
                                        <li><?= esc($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form class="needs-validation" method="post" action="<?= url_to('contact') ?>" novalidate
                              id="contactForm">
                            <?= csrf_field() ?>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_name" class="form-label">
                                            <i class="fas fa-user me-1 text-primary"></i>
                                            Your Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text"
                                               name="contact_name"
                                               id="contact_name"
                                               class="form-control <?= session('errors.contact_name') ? 'is-invalid' : '' ?>"
                                               placeholder="John Doe"
                                               value="<?= old('contact_name') ?>"
                                               required>
                                        <div class="invalid-feedback">
                                            Please enter your full name.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_email" class="form-label">
                                            <i class="fas fa-envelope me-1 text-primary"></i>
                                            Your Email <span class="text-danger">*</span>
                                        </label>
                                        <input type="email"
                                               name="contact_email"
                                               id="contact_email"
                                               class="form-control <?= session('errors.contact_email') ? 'is-invalid' : '' ?>"
                                               placeholder="john@example.com"
                                               value="<?= old('contact_email') ?>"
                                               required>
                                        <div class="invalid-feedback">
                                            Please enter a valid email address.
                                        </div>
                                    </div>
                                </div>
                                <!--end col-->

                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="contact_subject" class="form-label">
                                            <i class="fas fa-tag me-1 text-primary"></i>
                                            Inquiry Type
                                        </label>
                                        <select name="contact_subject"
                                                id="contact_subject"
                                                class="form-select <?= session('errors.contact_subject') ? 'is-invalid' : '' ?>">
                                            <option value="">Select a category</option>
                                            <option value="General Inquiry" <?= old('contact_subject') == 'General Inquiry' ? 'selected' : '' ?>>
                                                General Inquiry
                                            </option>
                                            <option value="Technical Support" <?= old('contact_subject') == 'Technical Support' ? 'selected' : '' ?>>
                                                Technical Support
                                            </option>
                                            <option value="Feature Request" <?= old('contact_subject') == 'Feature Request' ? 'selected' : '' ?>>
                                                Feature Request
                                            </option>
                                            <option value="Bug Report" <?= old('contact_subject') == 'Bug Report' ? 'selected' : '' ?>>
                                                Bug Report
                                            </option>
                                            <option value="Business Inquiry" <?= old('contact_subject') == 'Business Inquiry' ? 'selected' : '' ?>>
                                                Business Inquiry
                                            </option>
                                            <option value="Other" <?= old('contact_subject') == 'Other' ? 'selected' : '' ?>>
                                                Other
                                            </option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select an inquiry type.
                                        </div>
                                    </div>
                                </div>
                                <!--end col-->

                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="contact_message" class="form-label">
                                            <i class="fas fa-comment-dots me-1 text-primary"></i>
                                            Your Message <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="contact_message"
                                                  id="contact_message"
                                                  rows="5"
                                                  class="form-control <?= session('errors.contact_message') ? 'is-invalid' : '' ?>"
                                                  placeholder="Tell us how we can help you..."
                                                  required><?= old('contact_message') ?></textarea>
                                        <div class="form-text d-flex justify-content-between align-items-center mt-1">
                                            <span id="charCount">0</span> / 2000 characters
                                            <span class="text-muted">Required field *</span>
                                        </div>
                                        <div class="invalid-feedback">
                                            Please enter your message (minimum 10 characters).
                                        </div>
                                    </div>
                                </div>
                                <!--end col-->

                                <!-- File Attachment -->
                                <div class="col-12">
                                    <div class="mb-4">
                                        <label for="contact_attachment" class="form-label">
                                            <i class="fas fa-paperclip me-1 text-primary"></i>
                                            Attach File (Optional)
                                        </label>
                                        <input type="file"
                                               name="contact_attachment"
                                               id="contact_attachment"
                                               class="form-control"
                                               accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                        <small class="form-text text-muted">
                                            Maximum file size: 5MB • Allowed formats: JPG, PNG, PDF, DOC, DOCX
                                        </small>
                                        <div class="file-preview mt-2 d-none">
                                            <div class="alert alert-light d-flex justify-content-between align-items-center">
                                                <span class="file-name"></span>
                                                <button type="button" class="btn-close remove-file"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end col-->

                                <!-- Privacy Consent -->
                                <div class="col-12">
                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input <?= session('errors.privacy_consent') ? 'is-invalid' : '' ?>"
                                                   type="checkbox"
                                                   name="privacy_consent"
                                                   id="privacy_consent"
                                                   value="1"
                                                <?= old('privacy_consent') ? 'checked' : '' ?>
                                                   required>
                                            <label class="form-check-label" for="privacy_consent">
                                                I agree to the <a href="<?= base_url('privacy-policy') ?>" target="_blank"
                                                                  class="text-primary">Privacy Policy</a> and consent to the
                                                processing of my personal data.
                                            </label>
                                            <div class="invalid-feedback">
                                                You must agree to the privacy policy before submitting.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end col-->

                                <!-- CAPTCHA (Optional - you can add Google reCAPTCHA here) -->
                                <div class="col-12 mb-3">
                                    <div class="captcha-container">
                                        <!-- Add your CAPTCHA implementation here -->
                                        <div class="form-text text-muted">
                                            <i class="fas fa-shield-alt me-1"></i>
                                            This form is protected by reCAPTCHA and the Google
                                            <a href="https://policies.google.com/privacy" target="_blank"
                                               class="text-primary">Privacy Policy</a> and
                                            <a href="https://policies.google.com/terms" target="_blank"
                                               class="text-primary">Terms of Service</a> apply.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="d-grid">
                                        <button type="submit"
                                                name="send"
                                                class="btn btn-primary btn-lg"
                                                id="submitButton">
                                            <i class="fas fa-paper-plane me-2"></i>
                                            Send Message
                                        </button>
                                    </div>
                                </div>
                                <!--end col-->
                            </div>
                            <!--end row-->

                            <!-- Form Submission Info -->
                            <div class="text-center mt-4">
                                <small class="text-muted">
                                    <i class="fas fa-lock me-1"></i>
                                    Your information is secure and encrypted
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-clock me-1"></i>
                                    Typical response time: 2-24 hours
                                </small>
                            </div>
                        </form>
                    </div>
                    <!--end custom-form-->
                </div>
                <!--end col-->
            </div>
            <!--end row-->

            <!-- Alternative Contact Methods -->
            <div class="row justify-content-center mt-5 pt-4">
                <div class="col-lg-10">
                    <div class="card border-0 bg-light">
                        <div class="card-body p-4">
                            <h5 class="text-center mb-4">Other Ways to Reach Us</h5>
                            <div class="row">
                                <div class="col-md-4 text-center mb-3">
                                    <div class="p-3">
                                        <i class="fab fa-twitter fa-2x text-primary mb-3"></i>
                                        <h6>Twitter</h6>
                                        <p class="text-muted small mb-2">Follow us for updates</p>
                                        <a href="https://twitter.com/prjimages" target="_blank" class="text-primary">@prjimages</a>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center mb-3">
                                    <div class="p-3">
                                        <i class="fab fa-github fa-2x text-primary mb-3"></i>
                                        <h6>GitHub</h6>
                                        <p class="text-muted small mb-2">Report issues & contribute</p>
                                        <a href="https://github.com/prjimages" target="_blank" class="text-primary">github.com/prjimages</a>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center mb-3">
                                    <div class="p-3">
                                        <i class="fas fa-book fa-2x text-primary mb-3"></i>
                                        <h6>Documentation</h6>
                                        <p class="text-muted small mb-2">Browse our guides</p>
                                        <a href="<?= base_url('documentation') ?>" class="text-primary">View Docs</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end container-->
    </section>
    <!--end section-->

    <!-- FAQ Quick Links -->
    <section class="section bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h4 class="title mb-4">Quick Answers to Common Questions</h4>
                    <p class="text-muted mb-4">Before contacting support, check if your question is already answered in our
                        FAQ.</p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="<?= base_url('faqs_terms') ?>#data-storage" class="btn btn-outline-primary">
                            <i class="fas fa-database me-2"></i> Data Storage
                        </a>
                        <a href="<?= base_url('faqs_terms') ?>#billing" class="btn btn-outline-primary">
                            <i class="fas fa-credit-card me-2"></i> Billing Questions
                        </a>
                        <a href="<?= base_url('faqs_terms') ?>#technical" class="btn btn-outline-primary">
                            <i class="fas fa-cogs me-2"></i> Technical Issues
                        </a>
                        <a href="<?= base_url('faqs_terms') ?>#privacy" class="btn btn-outline-primary">
                            <i class="fas fa-shield-alt me-2"></i> Privacy Concerns
                        </a>
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
    <!-- End  -->

    <style>
        /* Contact Method Cards */
        .contact-method {
            transition: all 0.3s ease;
            height: 100%;
        }

        .contact-method:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
        }

        .icon-wrapper {
            transition: transform 0.3s ease;
        }

        .contact-method:hover .icon-wrapper {
            transform: scale(1.1);
        }

        /* Support Status */
        .support-status {
            border-left: 4px solid #4e73df;
        }

        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            position: relative;
        }

        .status-dot.online {
            background-color: #28a745;
            animation: pulse 2s infinite;
        }

        .status-dot.offline {
            background-color: #dc3545;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
            100% {
                opacity: 1;
            }
        }

        /* Contact Form */
        .contact-form-wrapper {
            border-top: 4px solid #4e73df;
        }

        .form-label i {
            width: 20px;
            text-align: center;
        }

        /* Character Counter */
        #charCount {
            font-weight: bold;
        }

        #charCount.warning {
            color: #ffc107;
        }

        #charCount.danger {
            color: #dc3545;
        }

        /* File Upload */
        .file-preview .alert {
            padding: 0.5rem 1rem;
        }

        .file-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Form Validation */
        .was-validated .form-control:valid,
        .form-control.is-valid {
            border-color: #28a745;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
        }

        .was-validated .form-control:invalid,
        .form-control.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        }

        /* Button Loading State */
        .btn-loading {
            position: relative;
            color: transparent !important;
        }

        .btn-loading:after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: button-loading-spinner 1s ease infinite;
        }

        @keyframes button-loading-spinner {
            from {
                transform: rotate(0turn);
            }
            to {
                transform: rotate(1turn);
            }
        }

        /* Quick Links */
        .btn-outline-primary {
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.2);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Form validation
            const contactForm = document.getElementById('contactForm');
            const submitButton = document.getElementById('submitButton');
            const messageField = document.getElementById('contact_message');
            const charCount = document.getElementById('charCount');
            const fileInput = document.getElementById('contact_attachment');
            const filePreview = document.querySelector('.file-preview');
            const fileName = document.querySelector('.file-name');
            const removeFileButton = document.querySelector('.remove-file');

            // Character counter
            if (messageField && charCount) {
                messageField.addEventListener('input', function () {
                    const length = this.value.length;
                    charCount.textContent = length;

                    // Update color based on length
                    if (length > 1800) {
                        charCount.className = 'danger';
                    } else if (length > 1500) {
                        charCount.className = 'warning';
                    } else {
                        charCount.className = '';
                    }

                    // Validate minimum length
                    if (length < 10) {
                        this.setCustomValidity('Please enter at least 10 characters.');
                    } else {
                        this.setCustomValidity('');
                    }
                });

                // Initial count
                charCount.textContent = messageField.value.length;
            }

            // File upload preview
            if (fileInput && filePreview && fileName && removeFileButton) {
                fileInput.addEventListener('change', function (e) {
                    const file = e.target.files[0];
                    if (file) {
                        // Validate file size (5MB)
                        if (file.size > 5 * 1024 * 1024) {
                            alert('File size must be less than 5MB');
                            this.value = '';
                            return;
                        }

                        // Validate file type
                        const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                        if (!allowedTypes.includes(file.type)) {
                            alert('Only JPG, PNG, PDF, DOC, and DOCX files are allowed');
                            this.value = '';
                            return;
                        }

                        // Show preview
                        fileName.textContent = file.name;
                        filePreview.classList.remove('d-none');
                    }
                });

                // Remove file
                removeFileButton.addEventListener('click', function () {
                    fileInput.value = '';
                    filePreview.classList.add('d-none');
                });
            }

            // Form submission
            if (contactForm && submitButton) {
                contactForm.addEventListener('submit', function (e) {
                    // Check form validity
                    if (!this.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                        this.classList.add('was-validated');
                        return;
                    }

                    // Show loading state
                    submitButton.disabled = true;
                    submitButton.classList.add('btn-loading');
                    submitButton.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Sending...';

                    // You can add AJAX submission here if needed
                    // For now, let the form submit normally
                });
            }

            // Real-time validation for email
            const emailField = document.getElementById('contact_email');
            if (emailField) {
                emailField.addEventListener('blur', function () {
                    const email = this.value;
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                    if (email && !emailRegex.test(email)) {
                        this.setCustomValidity('Please enter a valid email address.');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }

            // Support status update (simulated)
            function updateSupportStatus() {
                const statusDot = document.querySelector('.status-dot');
                const statusText = document.querySelector('.support-status .badge');
                const statusTime = document.querySelector('.status-time small');

                if (statusDot && statusText && statusTime) {
                    const now = new Date();
                    const hours = now.getHours();
                    const isOnline = hours >= 9 && hours < 18 && now.getDay() >= 1 && now.getDay() <= 5;

                    if (isOnline) {
                        statusDot.className = 'status-dot online';
                        statusText.textContent = 'Online';
                        statusText.className = 'badge bg-success';
                        statusTime.textContent = 'Updated: Just now';
                    } else {
                        statusDot.className = 'status-dot offline';
                        statusText.textContent = 'Offline';
                        statusText.className = 'badge bg-secondary';
                        statusTime.textContent = 'Back online: Tomorrow 9AM';
                    }
                }
            }

            // Update status every minute
            updateSupportStatus();
            setInterval(updateSupportStatus, 60000);

            // Auto-fill subject based on category
            const subjectSelect = document.getElementById('contact_subject');
            if (subjectSelect) {
                subjectSelect.addEventListener('change', function () {
                    // You could add additional logic here to auto-fill subject
                    console.log('Selected category:', this.value);
                });
            }

            // Save form data to localStorage in case of page refresh
            if (contactForm) {
                const formFields = contactForm.querySelectorAll('input, textarea, select');

                // Load saved data
                formFields.forEach(field => {
                    const savedValue = localStorage.getItem(`contact_${field.name}`);
                    if (savedValue && !field.value) {
                        field.value = savedValue;
                    }
                });

                // Save on input
                contactForm.addEventListener('input', function (e) {
                    if (e.target.name) {
                        localStorage.setItem(`contact_${e.target.name}`, e.target.value);
                    }
                });

                // Clear on successful submission
                contactForm.addEventListener('submit', function () {
                    formFields.forEach(field => {
                        localStorage.removeItem(`contact_${field.name}`);
                    });
                });
            }

            // Smooth scroll to form errors
            const firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({behavior: 'smooth', block: 'center'});
                firstError.focus();
            }
        });
    </script>
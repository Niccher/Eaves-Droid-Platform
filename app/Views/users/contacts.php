<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex flex-column flex-md-row align-items-md-center">
                        <h1 class="h2 mb-2 mb-md-0">
                            <i class="fas fa-address-book text-primary mr-2"></i>
                            <?php echo empty($pag) ? 'Saved Contacts' : ucfirst($pag) ?>
                        </h1>
                        <div class="ml-0 ml-md-3 d-flex flex-wrap">
                            <span class="badge badge-light border p-2 mr-2 mb-2 mb-md-0">
                                <i class="fas fa-users text-primary mr-1"></i>
                                Total: <b><?php echo $totalContacts ?? 0 ?></b>
                            </span>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">Manage your saved contacts and their communication history</p>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="float-right mt-2 mb-2">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb bg-transparent p-0 mb-0">
                                <li class="breadcrumb-item">
                                    <a href="<?= base_url("home") ?>">
                                        <i class="fas fa-home"></i> Home
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">Contacts</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <!-- Main Card -->
                    <div class="card card-secondary shadow-sm">
                        <div class="card-header d-flex align-items-center">
                             <h3 class="card-title">
                                 <i class="fas fa-users mr-2"></i>
                                 Contact List
                                 <small class="text-white ml-2">Showing <?php echo count($contacts_dump) ?> of <?php echo $totalContacts ?? 0 ?> contacts</small>
                             </h3>
                             <div class="card-tools ml-auto my-2">
                                 <button type="button" class="btn btn-success btn-sm" id="pdfExport" title="Export PDF">
                                     <i class="fas fa-file-pdf mr-1"></i> Export
                                 </button>
                                 <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                     <i class="fas fa-minus"></i>
                                 </button>
                             </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="border-bottom px-3 py-2">
                            <div class="input-group input-group-sm" style="max-width:350px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" class="form-control table-search" placeholder="Search by name or phone..." data-table="table-sortable">
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sticky-header table-sticky-header table-hover table-striped table-bordered mb-0 table-sortable">
                                    <thead class="thead-light">
                                    <tr>
                                        <th width="30%">Contact</th>
                                        <th width="20%">Phone Number</th>
                                        <th width="15%">Contact ID</th>
                                        <th width="15%">Phone Count</th>
                                        <th width="20%">Last Contacted</th>
                                        <th width="20%">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($contacts_dump)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <?= view("components/empty_state", ["icon" => "fa-user-slash", "title" => "No contacts found", "message" => "Your saved contacts will appear here"]) ?>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php
                                        $encrypter = model('CryptModel');
                                        foreach ($contacts_dump as $contact):
                                            // Use counter as ID for encryption
                                            $enc_id = $encrypter->encrypt_id($contact['ID'] ?? $contact['counter'] ?? '');

                                            // Determine contact avatar background color based on name
                                            $initial = strtoupper(substr(($contact['Name'] ?? '?'), 0, 1));
                                            $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
                                            $colorIndex = ord($initial) % count($colors);
                                            $avatarColor = $colors[$colorIndex];

                                            // Format last contacted time if available
                                            $lastContacted = 'Never';
                                            if (!empty($contact['last_contacted'])) {
                                                $date = new DateTime('@' . ($contact['last_contacted'] / 1000));
                                                $lastContacted = $date->format('M d, Y H:i');
                                            }
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3">
                                                            <div class="avatar-circle-sm bg-<?php echo $avatarColor; ?> text-white">
                                                                <?php echo $initial; ?>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="text-dark font-weight-bold"><?php echo htmlspecialchars($contact['Name'] ?? 'Unknown'); ?></div>
                                                            <small class="text-muted">ID: <?php echo $contact['ID'] ?? $contact['counter'] ?? 'N/A'; ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-dark font-weight-bold">
                                                        <?php echo htmlspecialchars($contact['Number'] ?? ($contact['phone_numbers'] ?? 'N/A')); ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <i class="fas fa-mobile-alt mr-1"></i> Phone
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light border text-dark p-2">
                                                        <i class="fas fa-hashtag mr-1"></i>
                                                        <?php echo $contact['contact_id'] ?? ($contact['ID'] ?? $contact['counter'] ?? 'N/A'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info p-2">
                                                        <i class="fas fa-phone-alt mr-1"></i>
                                                        <?php echo $contact['phone_count'] ?? 0; ?> numbers
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-light border p-2">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        <?php echo $lastContacted; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="<?php echo base_url('contacts/analyze/sms/' . $enc_id); ?>"
                                                           class="btn btn-info"
                                                           title="View SMS History">
                                                            <i class="fas fa-comments mr-1"></i> SMS
                                                        </a>
                                                        <a href="<?php echo base_url('contacts/analyze/calls/' . $enc_id); ?>"
                                                           class="btn btn-success"
                                                           title="View Call History">
                                                            <i class="fas fa-phone mr-1"></i> Calls
                                                        </a>
                                                         <button type="button"
                                                                 class="btn btn-outline-info view-contact-details"
                                                                 data-contact='<?= htmlspecialchars(json_encode([
                                                                     'ID' => $contact['ID'] ?? $contact['counter'] ?? '',
                                                                     'Name' => $contact['Name'] ?? 'Unknown',
                                                                     'contact_id' => $contact['contact_id'] ?? ($contact['ID'] ?? $contact['counter'] ?? ''),
                                                                     'phone_numbers' => $contact['phone_numbers_array'] ?? [],
                                                                     'phone_count' => $contact['phone_count'] ?? 0,
                                                                     'last_contacted' => $contact['last_contacted'] ?? null,
                                                                     'is_favorite' => $contact['is_favorite'] ?? 0,
                                                                     'contact_frequency' => $contact['contact_frequency'] ?? 0,
                                                                     'device_id' => $contact['device_id'] ?? null,
                                                                     'emails' => json_decode($contact['emails'] ?? '[]', true) ?: ($contact['emails'] ? [$contact['emails']] : []),
                                                                     'email_count' => $contact['email_count'] ?? 0,
                                                                     'companies' => json_decode($contact['companies'] ?? '[]', true) ?: ($contact['companies'] ? [$contact['companies']] : []),
                                                                     'addresses' => json_decode($contact['addresses'] ?? '[]', true) ?: ($contact['addresses'] ? [$contact['addresses']] : []),
                                                                     'notes' => $contact['notes'] ?? '',
                                                                     'nickname' => $contact['nickname'] ?? '',
                                                                     'website' => json_decode($contact['website'] ?? '[]', true) ?: ($contact['website'] ? [$contact['website']] : []),
                                                                     'social_profiles' => json_decode($contact['social_profiles'] ?? '[]', true) ?: ($contact['social_profiles'] ? [$contact['social_profiles']] : []),
                                                                     'raw_contact_account_type' => $contact['raw_contact_account_type'] ?? '',
                                                                     'raw_contact_account_name' => $contact['raw_contact_account_name'] ?? '',
                                                                     'photo_thumbnail_base64' => $contact['photo_thumbnail_base64'] ?? '',
                                                                     'relation' => json_decode($contact['relation'] ?? '[]', true) ?: ($contact['relation'] ? [$contact['relation']] : [])
                                                                 ]), ENT_QUOTES, 'UTF-8') ?>'>
                                                             <i class="fas fa-info-circle"></i> Details
                                                         </button>
                                                          <button type="button"
                                                                  class="btn btn-outline-danger delete-row"
                                                                  data-id="<?php echo $contact['ID'] ?? $contact['counter'] ?? ''; ?>"
                                                                  data-url="<?= base_url('contacts/delete') ?>"
                                                                  data-name="<?php echo htmlspecialchars($contact['Name'] ?? 'Unknown'); ?>"
                                                                  title="Delete contact">
                                                             <i class="fas fa-trash"></i>
                                                         </button>
                                                     </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="entry-info">
                                        Showing <?php echo (($currentPage - 1) * $perPage) + 1 ?>
                                        to <?php echo min($currentPage * $perPage, $totalContacts ?? 0) ?>
                                        of <?php echo $totalContacts ?? 0 ?> entries
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="float-right">
                                        <?php if (isset($pager) && $totalContacts > $perPage): ?>
                                            <?php echo $pager->links('default', 'bootstrap5_full'); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<!-- Contact Details Modal -->
<div class="modal fade" id="contactDetailsModal" tabindex="-1" role="dialog" aria-labelledby="contactDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="contactDetailsModalLabel">
                    <i class="fas fa-user-circle mr-2"></i>
                    Contact Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Left Sidebar (Avatar, Name, Quick details) -->
                    <div class="col-md-4 text-center border-right">
                        <div id="contactAvatar" class="mb-3">
                            <div id="contactPhotoWrapper" class="d-inline-block position-relative">
                                <div id="contactInitialsCircle" class="avatar-circle-lg bg-info text-white d-flex align-items-center justify-content-center" style="width:110px; height:110px; border-radius:50%; margin:0 auto; font-size:48px;">
                                    <span id="contactInitial"></span>
                                </div>
                                <img id="contactPhotoImg" class="img-thumbnail rounded-circle" style="width:110px; height:110px; object-fit: cover; display:none; margin:0 auto;" alt="Contact Photo">
                            </div>
                        </div>
                        <h4 id="contactName" class="font-weight-bold mb-1"></h4>
                        <p id="contactNickname" class="text-muted small mb-2"></p>
                        <div id="contactBadges" class="mb-3"></div>
                        
                        <div class="card card-outline card-secondary text-left mt-3">
                            <div class="card-header p-2">
                                <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-link mr-1"></i> Account Source</h3>
                            </div>
                            <div class="card-body p-2">
                                <table class="table table-sticky-header table-sticky-header table-sm table-borderless mb-0 small">
                                    <tr><td class="text-muted" style="width:70px;">Type:</td><td id="contactAccountType" class="font-weight-bold"></td></tr>
                                    <tr><td class="text-muted">Name:</td><td id="contactAccountName" class="text-break"></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Content (Tabs for organized sections) -->
                    <div class="col-md-8">
                        <ul class="nav nav-tabs mb-3" id="modalTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="info-tab" data-toggle="tab" href="#tab-info" role="tab"><i class="fas fa-id-card mr-1"></i> Contact Info</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="social-tab" data-toggle="tab" href="#tab-share" role="tab"><i class="fas fa-share-alt mr-1"></i> Details &amp; Notes</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="meta-tab" data-toggle="tab" href="#tab-meta" role="tab"><i class="fas fa-cog mr-1"></i> System Meta</a>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="modalTabsContent">
                            <!-- TAB 1: Contact Info -->
                            <div class="tab-pane fade show active" id="tab-info" role="tabpanel">
                                <!-- Phone Numbers -->
                                <h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-phone mr-1"></i> Phone Numbers (<span id="phoneCount">0</span>)</h6>
                                <div id="contactPhoneNumbers" class="mb-3 p-2 bg-light rounded border small" style="max-height:150px; overflow-y:auto;"></div>

                                <!-- Emails -->
                                <h6 class="font-weight-bold text-success mb-2"><i class="fas fa-envelope mr-1"></i> Email Addresses (<span id="emailCount">0</span>)</h6>
                                <div id="contactEmails" class="mb-3 p-2 bg-light rounded border small" style="max-height:120px; overflow-y:auto;"></div>

                                <!-- Companies / Organizations -->
                                <h6 class="font-weight-bold text-info mb-2"><i class="fas fa-building mr-1"></i> Organization / Company</h6>
                                <div id="contactCompanies" class="mb-3 p-2 bg-light rounded border small"></div>
                            </div>
                            
                            <!-- TAB 2: Details & Notes -->
                            <div class="tab-pane fade" id="tab-share" role="tabpanel">
                                <!-- Addresses -->
                                <h6 class="font-weight-bold text-secondary mb-2"><i class="fas fa-map-marker-alt mr-1"></i> Addresses</h6>
                                <div id="contactAddresses" class="mb-3 p-2 bg-light rounded border small" style="max-height:100px; overflow-y:auto;"></div>

                                <!-- Websites & Socials -->
                                <h6 class="font-weight-bold text-indigo mb-2"><i class="fas fa-globe mr-1"></i> Websites &amp; Socials</h6>
                                <div id="contactWebsites" class="mb-3 p-2 bg-light rounded border small"></div>

                                <!-- Notes -->
                                <h6 class="font-weight-bold text-dark mb-2"><i class="fas fa-sticky-note mr-1"></i> Notes / Comments</h6>
                                <div id="contactNotes" class="mb-3 p-2 bg-light rounded border small" style="max-height:120px; overflow-y:auto; white-space: pre-wrap;"></div>
                            </div>
                            
                            <!-- TAB 3: System Meta -->
                            <div class="tab-pane fade" id="tab-meta" role="tabpanel">
                                <table class="table table-sticky-header table-sticky-header table-sm table-striped border rounded small">
                                    <tr><td class="font-weight-bold" style="width:150px;">Local Database ID</td><td id="contactIdVal"></td></tr>
                                    <tr><td class="font-weight-bold">Android Contact ID</td><td id="contactAndroidIdVal"></td></tr>
                                    <tr><td class="font-weight-bold">Device ID</td><td id="contactDeviceIdVal"></td></tr>
                                    <tr><td class="font-weight-bold">Last Contacted</td><td id="contactLastContactedVal"></td></tr>
                                    <tr><td class="font-weight-bold">Contact Frequency</td><td id="contactFrequencyVal"></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle-sm {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    .avatar-circle-lg {
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .phone-numbers-list {
        max-height: 150px;
        overflow-y: auto;
        background: #f8f9fa;
        border-radius: 5px;
        padding: 10px;
    }
    .phone-numbers-list .phone-item {
        padding: 5px 0;
        border-bottom: 1px solid #e9ecef;
    }
    .phone-numbers-list .phone-item:last-child {
        border-bottom: none;
    }
    .table-sortable thead th { cursor: pointer; user-select: none; }
    .table-sortable thead th.sort-asc::after { content: ' \25B2'; font-size: 0.7em; }
    .table-sortable thead th.sort-desc::after { content: ' \25BC'; font-size: 0.7em; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const viewButtons = document.querySelectorAll('.view-contact-details');
        const modal = new bootstrap.Modal(document.getElementById('contactDetailsModal'));

        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const contactData = JSON.parse(this.getAttribute('data-contact'));
                populateContactModal(contactData);
                modal.show();
            });
        });

        function populateContactModal(contact) {
            // Basic Info
            document.getElementById('contactName').textContent = contact.Name;
            document.getElementById('contactNickname').textContent = contact.nickname ? '"' + contact.nickname + '"' : '';
            
            // Local Database ID / Android Contact ID / Device ID
            document.getElementById('contactIdVal').textContent = contact.ID || 'N/A';
            document.getElementById('contactAndroidIdVal').textContent = contact.contact_id || 'N/A';
            document.getElementById('contactDeviceIdVal').textContent = contact.device_id || 'N/A';
            document.getElementById('contactFrequencyVal').textContent = (contact.contact_frequency || 0) + ' times';

            // Parse Account Source
            let accountType = 'Local / Unknown';
            let accountName = 'Device Account';
            
            try {
                if (contact.raw_contact_account_type) {
                    let parsedType = typeof contact.raw_contact_account_type === 'string' && contact.raw_contact_account_type.startsWith('[') || contact.raw_contact_account_type.startsWith('{')
                        ? JSON.parse(contact.raw_contact_account_type) 
                        : contact.raw_contact_account_type;
                    if (Array.isArray(parsedType) && parsedType.length > 0) {
                        accountType = parsedType[0].account_type || accountType;
                        accountName = parsedType[0].account_name || accountName;
                    } else if (typeof parsedType === 'object' && parsedType !== null) {
                        accountType = parsedType.account_type || accountType;
                        accountName = parsedType.account_name || accountName;
                    } else if (typeof parsedType === 'string' && parsedType.trim() !== '') {
                        accountType = parsedType;
                    }
                }
            } catch (e) {
                accountType = contact.raw_contact_account_type || accountType;
            }

            try {
                if (contact.raw_contact_account_name && (accountName === 'Device Account' || accountName === '')) {
                    let parsedName = typeof contact.raw_contact_account_name === 'string' && contact.raw_contact_account_name.startsWith('[') || contact.raw_contact_account_name.startsWith('{')
                        ? JSON.parse(contact.raw_contact_account_name) 
                        : contact.raw_contact_account_name;
                    if (Array.isArray(parsedName) && parsedName.length > 0) {
                        accountName = parsedName[0] || accountName;
                    } else if (typeof parsedName === 'string' && parsedName.trim() !== '') {
                        accountName = parsedName;
                    }
                }
            } catch (e) {
                if (accountName === 'Device Account') {
                    accountName = contact.raw_contact_account_name || accountName;
                }
            }

            // Prettify common account types
            let prettyType = accountType;
            if (accountType === 'com.google') {
                prettyType = 'Google Account';
            } else if (accountType === 'com.whatsapp') {
                prettyType = 'WhatsApp';
            } else if (accountType === 'com.android.huawei.phone') {
                prettyType = 'Huawei Device';
            } else if (accountType.toLowerCase().includes('telegram')) {
                prettyType = 'Telegram';
            } else if (accountType.toLowerCase().includes('facebook')) {
                prettyType = 'Facebook';
            }

            document.getElementById('contactAccountType').textContent = prettyType;
            document.getElementById('contactAccountName').textContent = accountName;

            // Set Photo or Initials
            const initialCircle = document.getElementById('contactInitialsCircle');
            const photoImg = document.getElementById('contactPhotoImg');
            
            if (contact.photo_thumbnail_base64 && contact.photo_thumbnail_base64.trim() !== '') {
                // Ensure data URI prefix is present
                let photoSrc = contact.photo_thumbnail_base64;
                if (!photoSrc.startsWith('data:')) {
                    photoSrc = 'data:image/jpeg;base64,' + photoSrc;
                }
                photoImg.src = photoSrc;
                photoImg.style.display = 'block';
                initialCircle.style.display = 'none';
            } else {
                const initial = contact.Name ? contact.Name.charAt(0).toUpperCase() : '?';
                document.getElementById('contactInitial').textContent = initial;
                photoImg.style.display = 'none';
                initialCircle.style.display = 'flex';
            }

            // Badges
            const badgesContainer = document.getElementById('contactBadges');
            badgesContainer.innerHTML = '';
            if (parseInt(contact.is_favorite)) {
                badgesContainer.innerHTML += '<span class="badge badge-warning mr-1"><i class="fas fa-star mr-1"></i>Favorite</span>';
            }
            if (contact.phone_count > 0) {
                badgesContainer.innerHTML += '<span class="badge badge-success mr-1"><i class="fas fa-phone mr-1"></i>' + contact.phone_count + ' Phones</span>';
            }
            if (parseInt(contact.email_count) > 0) {
                badgesContainer.innerHTML += '<span class="badge badge-info mr-1"><i class="fas fa-envelope mr-1"></i>' + contact.email_count + ' Emails</span>';
            }

            // Format last contacted time if available
            const lastContactedVal = document.getElementById('contactLastContactedVal');
            if (contact.last_contacted) {
                const date = new Date(parseInt(contact.last_contacted));
                const now = new Date();
                const diffMs = now - date;
                const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

                let timeText = '';
                if (diffDays === 0) {
                    timeText = 'Today at ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                } else if (diffDays === 1) {
                    timeText = 'Yesterday at ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                } else if (diffDays < 7) {
                    timeText = diffDays + ' days ago';
                } else {
                    timeText = date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                }
                lastContactedVal.textContent = timeText;
            } else {
                lastContactedVal.textContent = 'Never';
            }

            // Phone Numbers
            const phoneNumbersContainer = document.getElementById('contactPhoneNumbers');
            const phoneCountSpan = document.getElementById('phoneCount');
            if (contact.phone_numbers && Array.isArray(contact.phone_numbers) && contact.phone_numbers.length > 0) {
                phoneCountSpan.textContent = contact.phone_numbers.length;
                let phoneHtml = '';
                contact.phone_numbers.forEach((phone, index) => {
                    let num = phone;
                    let label = 'Mobile';
                    if (typeof phone === 'object' && phone !== null) {
                        num = phone.number || '';
                        label = phone.type || phone.label || 'Phone';
                    }
                    phoneHtml += `
                        <div class="py-1 border-bottom d-flex align-items-center justify-content-between">
                            <div>
                                <i class="fas fa-phone text-success mr-2"></i>
                                <span class="font-weight-bold">${num}</span>
                            </div>
                            <span class="badge badge-light border small text-muted">${label}</span>
                        </div>
                    `;
                });
                phoneNumbersContainer.innerHTML = phoneHtml;
            } else {
                phoneCountSpan.textContent = '0';
                phoneNumbersContainer.innerHTML = '<div class="text-center text-muted py-2">No phone numbers</div>';
            }

            // Emails
            const emailsContainer = document.getElementById('contactEmails');
            const emailCountSpan = document.getElementById('emailCount');
            if (contact.emails && Array.isArray(contact.emails) && contact.emails.length > 0) {
                emailCountSpan.textContent = contact.emails.length;
                let emailHtml = '';
                contact.emails.forEach((email) => {
                    let addr = email;
                    let label = 'Home';
                    if (typeof email === 'object' && email !== null) {
                        addr = email.address || '';
                        label = email.type || email.label || 'Email';
                    }
                    emailHtml += `
                        <div class="py-1 border-bottom d-flex align-items-center justify-content-between">
                            <div>
                                <i class="fas fa-envelope text-success mr-2"></i>
                                <span class="font-weight-bold">${addr}</span>
                            </div>
                            <span class="badge badge-light border small text-muted">${label}</span>
                        </div>
                    `;
                });
                emailsContainer.innerHTML = emailHtml;
            } else {
                emailCountSpan.textContent = '0';
                emailsContainer.innerHTML = '<div class="text-center text-muted py-2">No emails registered</div>';
            }

            // Companies
            const companiesContainer = document.getElementById('contactCompanies');
            if (contact.companies && Array.isArray(contact.companies) && contact.companies.length > 0) {
                let compHtml = '';
                contact.companies.forEach((comp) => {
                    let name = comp.company || comp.name || '';
                    let title = comp.title || comp.jobTitle || '';
                    compHtml += `
                        <div class="py-1 border-bottom">
                            <i class="fas fa-building text-info mr-2"></i><strong>${name}</strong>
                            ${title ? ` — <span class="text-muted">${title}</span>` : ''}
                        </div>
                    `;
                });
                companiesContainer.innerHTML = compHtml;
            } else {
                companiesContainer.innerHTML = '<div class="text-center text-muted py-2">No company details</div>';
            }

            // Addresses
            const addressesContainer = document.getElementById('contactAddresses');
            if (contact.addresses && Array.isArray(contact.addresses) && contact.addresses.length > 0) {
                let addrHtml = '';
                contact.addresses.forEach((addr) => {
                    let formatted = addr.formattedAddress || addr.address || '';
                    let label = addr.type || addr.label || 'Work';
                    addrHtml += `
                        <div class="py-1 border-bottom d-flex justify-content-between">
                            <div><i class="fas fa-map-marker-alt text-secondary mr-2"></i>${formatted}</div>
                            <span class="badge badge-light border small text-muted align-self-start">${label}</span>
                        </div>
                    `;
                });
                addressesContainer.innerHTML = addrHtml;
            } else {
                addressesContainer.innerHTML = '<div class="text-center text-muted py-2">No address data</div>';
            }

            // Websites
            const websitesContainer = document.getElementById('contactWebsites');
            if (contact.website && Array.isArray(contact.website) && contact.website.length > 0) {
                let webHtml = '';
                contact.website.forEach((web) => {
                    let url = typeof web === 'string' ? web : (web.url || '');
                    webHtml += `
                        <div class="py-1 border-bottom">
                            <i class="fas fa-globe text-primary mr-2"></i><a href="${url}" target="_blank">${url}</a>
                        </div>
                    `;
                });
                websitesContainer.innerHTML = webHtml;
            } else {
                websitesContainer.innerHTML = '<div class="text-center text-muted py-2">No websites listed</div>';
            }

            // Notes
            const notesContainer = document.getElementById('contactNotes');
            notesContainer.textContent = contact.notes || 'No notes available.';
        }

        // Real-time table search
        document.querySelector('.table-search')?.addEventListener('keyup', function() {
            var keyword = this.value.toLowerCase();
            var target = this.getAttribute('data-table');
            document.querySelectorAll('.' + target + ' tbody tr').forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().indexOf(keyword) > -1 ? '' : 'none';
            });
        });

        // Column sorting
        document.querySelectorAll('.table-sortable thead th').forEach(function(th) {
            th.addEventListener('click', function() {
                var table = this.closest('table');
                var tbody = table.querySelector('tbody');
                var index = Array.prototype.indexOf.call(this.parentNode.children, this);
                var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
                var asc = !this.classList.contains('sort-asc');
                table.querySelectorAll('thead th').forEach(function(h) { h.classList.remove('sort-asc', 'sort-desc'); });
                this.classList.toggle('sort-asc', asc);
                this.classList.toggle('sort-desc', !asc);
                rows.sort(function(a, b) {
                    var aVal = (a.querySelectorAll('td')[index]?.textContent || '').trim();
                    var bVal = (b.querySelectorAll('td')[index]?.textContent || '').trim();
                    var aNum = parseFloat(aVal), bNum = parseFloat(bVal);
                    if (!isNaN(aNum) && !isNaN(bNum)) return asc ? aNum - bNum : bNum - aNum;
                    return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
                });
                rows.forEach(function(row) { tbody.appendChild(row); });
});
    });
    // PDF Export
    $('#pdfExport').on('click', function () {
        var element = document.querySelector('.table-sortable');
        if (!element) return;
        Swal.fire({
            title: 'Generating PDF...',
            text: 'Please wait while we prepare your document',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        html2pdf().set({
            margin:       10,
            filename:     'contacts_export_' + Date.now() + '.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, letterRendering: true },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
        }).from(element).save().then(function () {
            Swal.close();
            Swal.fire({ icon: 'success', title: 'Export Complete', text: 'PDF has been downloaded', timer: 2000, showConfirmButton: false });
        }).catch(function () {
            Swal.close();
            Swal.fire({ icon: 'error', title: 'Export Failed', text: 'Could not generate PDF', timer: 3000, showConfirmButton: false });
        });
    });
});
</script>
<?php include __DIR__ . '/partials/_delete_confirm.php'; ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center">
                        <h1 class="h2 mb-0">
                            <i class="fas fa-address-book text-primary mr-2"></i>
                            <?php echo empty($pag) ? 'Saved Contacts' : ucfirst($pag) ?>
                        </h1>
                        <div class="ml-3">
                            <span class="badge badge-light border p-2">
                                <i class="fas fa-users text-primary mr-1"></i>
                                Total: <b><?php echo $totalContacts ?? 0 ?></b>
                            </span>
                        </div>
                    </div>
                    <p class="text-white mt-2 mb-0">Manage your saved contacts and their communication history</p>
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
                                <table class="table table-hover table-striped table-bordered mb-0 table-sortable">
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
                                                <div class="empty-state">
                                                    <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                                                    <h4>No contacts found</h4>
                                                    <p class="text-muted">Your saved contacts will appear here</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php
                                        $encrypter = model('Mod_Crypt');
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
                                                                     'phone_numbers' => [$contact['Number'] ?? 'N/A'],
                                                                     'phone_count' => $contact['phone_count'] ?? 0,
                                                                     'last_contacted' => $contact['last_contacted'] ?? null,
                                                                     'is_favorite' => $contact['is_favorite'] ?? 0,
                                                                     'contact_frequency' => $contact['contact_frequency'] ?? 0,
                                                                     'device_id' => $contact['device_id'] ?? null
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
                    <div class="col-md-3 text-center">
                        <div id="contactAvatar" class="mb-3">
                            <div class="avatar-circle-lg bg-info text-white d-inline-flex align-items-center justify-content-center" style="width:100px;height:100px;">
                                <span id="contactInitial" style="font-size:48px;"></span>
                            </div>
                        </div>
                        <h5 id="contactName" class="font-weight-bold"></h5>
                        <p id="contactId" class="text-muted small"></p>
                        <div id="contactBadges" class="mt-2"></div>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <!-- Contact Info -->
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-2">
                                    <i class="fas fa-info-circle text-primary mr-1"></i>
                                    Contact Information
                                </h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td width="40%"><i class="fas fa-hashtag text-muted"></i> Contact ID:</td>
                                        <td><code id="contactAndroidId"></code></td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-mobile-alt text-muted"></i> Device ID:</td>
                                        <td><small id="contactDeviceId" class="text-muted"></small></td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-handshake text-muted"></i> Last Contacted:</td>
                                        <td><span id="contactLastContacted" class="badge badge-info"></span></td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-chart-line text-muted"></i> Contact Frequency:</td>
                                        <td><span id="contactFrequency" class="badge badge-success"></span></td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Phone Numbers -->
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-2">
                                    <i class="fas fa-phone text-primary mr-1"></i>
                                    Phone Numbers (<span id="phoneCount">0</span>)
                                </h6>
                                <div id="contactPhoneNumbers" class="phone-numbers-list"></div>
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
            // Set basic info
            document.getElementById('contactName').textContent = contact.Name;
            document.getElementById('contactId').textContent = 'ID: ' + (contact.ID || 'N/A');
            document.getElementById('contactAndroidId').textContent = contact.contact_id || 'N/A';
            document.getElementById('contactDeviceId').textContent = contact.device_id || 'N/A';
            document.getElementById('contactFrequency').textContent = (contact.contact_frequency || 0) + ' times';

            // Set avatar initial
            const initial = contact.Name.charAt(0).toUpperCase();
            document.getElementById('contactInitial').textContent = initial;

            // Set badges
            const badgesContainer = document.getElementById('contactBadges');
            badgesContainer.innerHTML = '';

            if (contact.is_favorite) {
                badgesContainer.innerHTML += '<span class="badge badge-warning mr-1"><i class="fas fa-star"></i> Favorite</span>';
            }
            if (contact.phone_count > 1) {
                badgesContainer.innerHTML += '<span class="badge badge-success mr-1">' + contact.phone_count + ' phones</span>';
            }

            // Set last contacted time
            const lastContactedSpan = document.getElementById('contactLastContacted');
            if (contact.last_contacted) {
                const date = new Date(parseInt(contact.last_contacted));
                const now = new Date();
                const diffMs = now - date;
                const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

                let timeText = '';
                if (diffDays === 0) {
                    timeText = 'Today at ' + date.toLocaleTimeString();
                } else if (diffDays === 1) {
                    timeText = 'Yesterday at ' + date.toLocaleTimeString();
                } else if (diffDays < 7) {
                    timeText = diffDays + ' days ago';
                } else {
                    timeText = date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
                }
                lastContactedSpan.textContent = timeText;
            } else {
                lastContactedSpan.textContent = 'Never';
            }

            // Set phone numbers
            const phoneNumbersContainer = document.getElementById('contactPhoneNumbers');
            const phoneCountSpan = document.getElementById('phoneCount');

            if (contact.phone_numbers && Array.isArray(contact.phone_numbers)) {
                phoneCountSpan.textContent = contact.phone_numbers.length;
                let phoneHtml = '';

                contact.phone_numbers.forEach((phone, index) => {
                    phoneHtml += `
                    <div class="phone-item">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-phone text-success mr-2"></i>
                            <span class="font-weight-bold">${phone}</span>
                            ${index === 0 ? '<span class="badge badge-primary badge-sm ml-2">Primary</span>' : ''}
                        </div>
                    </div>
                `;
                });

                phoneNumbersContainer.innerHTML = phoneHtml;
            } else {
                phoneCountSpan.textContent = '0';
                phoneNumbersContainer.innerHTML = '<p class="text-muted text-center">No phone numbers available</p>';
            }
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
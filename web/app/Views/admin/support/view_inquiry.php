<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title">Inquiry Details</h3>
                <div class="card-tools">
                    <a href="<?= base_url('admin/support/inquiries') ?>" class="btn btn-sm btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 25%;">ID</th>
                        <td>#<?= esc($inquiry['id']) ?></td>
                    </tr>
                    <tr>
                        <th>Date Submitted</th>
                        <td><?= esc(date('Y-m-d H:i:s', strtotime($inquiry['created_at']))) ?></td>
                    </tr>
                    <tr>
                        <th>Sender Name</th>
                        <td><?= esc($inquiry['name']) ?></td>
                    </tr>
                    <tr>
                        <th>Sender Email</th>
                        <td><a href="mailto:<?= esc($inquiry['email']) ?>"><?= esc($inquiry['email']) ?></a></td>
                    </tr>
                    <tr>
                        <th>Subject</th>
                        <td class="font-weight-bold"><?= esc($inquiry['subject']) ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <form id="statusForm" class="form-inline">
                                <select name="status" id="inquiryStatus" class="form-control form-control-sm mr-2">
                                    <?php $currentStatus = $inquiry['status'] ?? 'pending'; ?>
                                    <option value="pending" <?= $currentStatus == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="in_progress" <?= $currentStatus == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="resolved" <?= $currentStatus == 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                </select>
                                <button type="button" class="btn btn-sm btn-primary shadow-sm" onclick="updateStatus(<?= esc($inquiry['id']) ?>)">
                                    <i class="fas fa-save"></i> Update
                                </button>
                                <span id="statusMessage" class="ml-2 text-success" style="display:none;"><i class="fas fa-check"></i> Saved</span>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <th>IP Address</th>
                        <td><?= esc($inquiry['ip_address']) ?></td>
                    </tr>
                    <tr>
                        <th>User Agent</th>
                        <td class="small text-muted"><?= esc($inquiry['user_agent']) ?></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="bg-light">
                            <strong>Message Content:</strong>
                            <div class="mt-3 p-3 bg-white border rounded">
                                <?= nl2br(esc($inquiry['message'])) ?>
                            </div>
                        </td>
                    </tr>
                    <?php if (!empty($inquiry['attachment'])): ?>
                        <?php $filename = basename($inquiry['attachment']); ?>
                        <tr>
                            <th>Attachment</th>
                            <td>
                                <a href="<?= url_to('admin_inquiries_attachment', $filename) ?>" class="btn btn-sm btn-info shadow-sm" target="_blank">
                                    <i class="fas fa-paperclip"></i> View/Download Attachment
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
            <div class="card-footer text-right">
                <form action="<?= base_url('admin/support/inquiries/delete/' . $inquiry['id']) ?>" method="post" onsubmit="return confirm('Are you sure you want to delete this inquiry?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger shadow-sm">
                        <i class="fas fa-trash"></i> Delete Inquiry
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updateStatus(id) {
    var status = document.getElementById('inquiryStatus').value;
    var csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
    
    $.ajax({
        url: '<?= base_url('admin/support/inquiries/status/') ?>' + id,
        type: 'POST',
        data: {
            status: status,
            [csrfName]: csrfHash
        },
        success: function(response) {
            if(response.status === 'success') {
                $('#statusMessage').fadeIn().delay(2000).fadeOut();
            } else {
                alert('Error updating status: ' + response.message);
            }
        },
        error: function() {
            alert('Failed to connect to the server.');
        }
    });
}
</script>

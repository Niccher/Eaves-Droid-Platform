<div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Inquiries List</h3>
    </div>
    <div class="card-body">
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

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover dataTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($inquiries) && is_array($inquiries)): ?>
                        <?php foreach ($inquiries as $inq): ?>
                            <tr>
                                <td><?= esc($inq['id']) ?></td>
                                <td><?= esc($inq['name']) ?></td>
                                <td><a href="mailto:<?= esc($inq['email']) ?>"><?= esc($inq['email']) ?></a></td>
                                <td><?= esc($inq['subject']) ?></td>
                                <td>
                                    <?php if (isset($inq['status'])): ?>
                                        <?php if ($inq['status'] == 'resolved'): ?>
                                            <span class="badge badge-success">Resolved</span>
                                        <?php elseif ($inq['status'] == 'in_progress'): ?>
                                            <span class="badge badge-warning">In Progress</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Pending</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc(date('Y-m-d H:i', strtotime($inq['created_at']))) ?></td>
                                <td>
                                    <a href="<?= base_url('admin/support/inquiries/view/' . $inq['id']) ?>" class="btn btn-sm btn-info shadow-sm" title="View Details">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No inquiries found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- DataTables setup (assuming DataTables is loaded in BaseAdminController layout) -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if ($.fn.DataTable) {
            $('.dataTable').DataTable({
                "order": [[5, "desc"]],
                "responsive": true
            });
        }
    });
</script>

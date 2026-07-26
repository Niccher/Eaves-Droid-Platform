<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>User Activity Reports</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/reports') ?>">Reports</a></li>
                        <li class="breadcrumb-item active">User Activity</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1">User Activity Report</h5>
                        <p class="mb-0 small text-muted">Detailed log of user actions — logins, page views, feature usage, and administrative operations with timestamps and IP addresses.</p>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <div class="input-group input-group-lg" style="max-width: 400px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" id="userSearch" placeholder="Search users...">
                    </div>
                </div>
            </div>

            <div class="row" id="userCards">
                <?php foreach ($users as $u): ?>
                <div class="col-lg-4 col-md-6 col-sm-12 mb-3 user-card-wrapper">
                    <a href="<?= base_url('admin/reports/user-activity/' . urlencode($u['username'])) ?>" class="text-decoration-none">
                        <div class="card card-hover shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                            <i class="fas fa-user fa-2x text-white"></i>
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-grow-1">
                                        <h5 class="mb-1 font-weight-bold text-dark"><?= htmlspecialchars($u['username']) ?></h5>
                                        <small class="text-muted">
                                            <i class="fas fa-envelope mr-1"></i><?= htmlspecialchars($u['email'] ?? 'No email') ?>
                                        </small>
                                    </div>
                                    <div class="ml-2">
                                        <i class="fas fa-chevron-right text-muted"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($users)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">No users found.</div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#userSearch').on('keyup', function() {
        var value = this.value.toLowerCase();
        $('#userCards .user-card-wrapper').each(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});
</script>

<style>
.card-hover {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card-hover:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important;
    border-color: #007bff !important;
}
.text-decoration-none:hover {
    text-decoration: none;
}
</style>
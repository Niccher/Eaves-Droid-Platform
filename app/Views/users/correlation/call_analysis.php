<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="fas fa-phone-alt mr-2"></i> Call Intelligence Analysis
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo base_url('home'); ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo base_url('analysis'); ?>">Intelligence Dashboard</a></li>
                        <li class="breadcrumb-item active">Call Analysis</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Category Tabs -->
            <div class="card card-success card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="call-tabs" role="tablist">
                        <?php 
                        $categories = [
                            'family'    => ['icon' => 'fa-users', 'label' => 'Family/Friends'],
                            'new'       => ['icon' => 'fa-user-plus', 'label' => 'New Callers'],
                            'business'  => ['icon' => 'fa-briefcase', 'label' => 'Business'],
                            'intl'      => ['icon' => 'fa-globe-africa', 'label' => 'International'],
                            'urgent'    => ['icon' => 'fa-exclamation-triangle', 'label' => 'Urgent'],
                            'spam'      => ['icon' => 'fa-ban', 'label' => 'Spam']
                        ];
                        
                        foreach ($categories as $cat => $info): 
                        ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_category == $cat ? 'active' : ''; ?>" 
                               href="<?php echo base_url('analysis/calls?category=' . $cat); ?>">
                                <i class="fas <?php echo $info['icon']; ?> mr-1"></i>
                                <?php echo $info['label']; ?>
                                <span class="badge badge-light ml-1"><?php echo $call_counts[$cat] ?? 0; ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped dataTable" id="callAnalysisTable">
                            <thead>
                                <tr>
                                    <th>Contact Name</th>
                                    <th>Phone Number</th>
                                    <th>Call Type</th>
                                    <th>Duration</th>
                                    <th>Date/Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($categorized_calls)): ?>
                                    <?php foreach ($categorized_calls as $call): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($call['contact_name'] ?? 'Unknown'); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary p-2">
                                                    <?php echo htmlspecialchars($call['phone_number']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                $type_color = 'info';
                                                if ($call['call_type'] == 'missed') $type_color = 'warning';
                                                if ($call['call_type'] == 'rejected' || $call['call_type'] == 'blocked') $type_color = 'danger';
                                                if ($call['call_type'] == 'outgoing') $type_color = 'success';
                                                ?>
                                                <span class="badge badge-<?php echo $type_color; ?>">
                                                    <?php echo ucfirst($call['call_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                $dur = $call['duration_seconds'];
                                                if ($dur >= 60) {
                                                    echo floor($dur / 60) . 'm ' . ($dur % 60) . 's';
                                                } else {
                                                    echo $dur . 's';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $date_ms = $call['call_date'];
                                                echo date('Y-m-d H:i:s', $date_ms / 1000);
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No calls found in this category.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(function () {
    $('#callAnalysisTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "order": [[4, "desc"]]
    });
});
</script>

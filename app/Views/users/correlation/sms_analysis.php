<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="fas fa-sms mr-2"></i> SMS Intelligence Analysis
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo base_url('home'); ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo base_url('analysis'); ?>">Intelligence Dashboard</a></li>
                        <li class="breadcrumb-item active">SMS Analysis</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Category Tabs -->
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="sms-tabs" role="tablist">
                        <?php 
                        $categories = [
                            'financial' => ['icon' => 'fa-money-bill-wave', 'label' => 'Financial'],
                            'otp'       => ['icon' => 'fa-key', 'label' => 'OTP/Auth'],
                            'promo'     => ['icon' => 'fa-ad', 'label' => 'Promotional'],
                            'utility'   => ['icon' => 'fa-file-invoice-dollar', 'label' => 'Utility/Bills'],
                            'service'   => ['icon' => 'fa-truck', 'label' => 'Service/Delivery'],
                            'malicious' => ['icon' => 'fa-shield-alt', 'label' => 'Malicious'],
                            'personal'  => ['icon' => 'fa-user', 'label' => 'Personal']
                        ];
                        
                        foreach ($categories as $cat => $info): 
                        ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_category == $cat ? 'active' : ''; ?>" 
                               href="<?php echo base_url('analysis/sms?category=' . $cat); ?>">
                                <i class="fas <?php echo $info['icon']; ?> mr-1"></i>
                                <?php echo $info['label']; ?>
                                <span class="badge badge-light ml-1"><?php echo $sms_counts[$cat] ?? 0; ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped dataTable" id="smsAnalysisTable">
                            <thead>
                                <tr>
                                    <th>Address</th>
                                    <th>Message Body</th>
                                    <th>Date/Time</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($categorized_sms)): ?>
                                    <?php foreach ($categorized_sms as $sms): ?>
                                        <tr>
                                            <td>
                                                <span class="badge badge-secondary p-2">
                                                    <?php echo htmlspecialchars($sms['address']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($sms['body']); ?></td>
                                            <td>
                                                <?php 
                                                $date_ms = $sms['sms_time'];
                                                echo date('Y-m-d H:i:s', $date_ms / 1000);
                                                ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-info"><?php echo ucfirst($sms['sms_type']); ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No messages found in this category.</td>
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
    $('#smsAnalysisTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "order": [[2, "desc"]]
    });
});
</script>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-8 col-md-6">
                        <div class="d-flex align-items-center">
                            <h1 class="h2 mb-0">
                                <i class="fas fa-phone-alt text-primary mr-2"></i>
                                <?php echo $call_head ?? 'Call Logs' ?>
                            </h1>
                            <div class="ml-3 d-flex align-items-center flex-wrap">
                                <span class="badge badge-light border p-2 mr-2">
                                    <i class="fas fa-chart-bar text-primary mr-1"></i>
                                    Total: <b><?php echo $totalCalls ?? 0 ?></b>
                                </span>
                                <span class="badge badge-light border p-2 mr-2">
                                    <i class="fas fa-arrow-circle-down text-info mr-1"></i>
                                    Incoming: <b><?php echo $incomingCallsCount ?? 0 ?></b>
                                </span>
                                <span class="badge badge-light border p-2 mr-2">
                                    <i class="fas fa-arrow-circle-up text-success mr-1"></i>
                                    Outgoing: <b><?php echo $outgoingCallsCount ?? 0 ?></b>
                                </span>
                                <span class="badge badge-light border p-2 mr-2">
                                    <i class="fas fa-times-circle text-danger mr-1"></i>
                                    Rejected: <b><?php echo $rejectedCallsCount ?? 0 ?></b>
                                </span>
                                <span class="badge badge-light border p-2">
                                    <i class="fas fa-shield-alt text-warning mr-1"></i>
                                    Blocked: <b><?php echo $blockedCallsCount ?? 0 ?></b>
                                </span>
                            </div>
                        </div>
                        <p class="text-muted mt-2 mb-0">View and manage your call history</p>
                    </div>
                    <div class="col-lg-4 col-md-6">
                                <div class="float-right mt-2 mb-2">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <?php echo $call_urls; ?>
                            </div>
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
                            <h3 class="card-title text-white">
                                <i class="fas fa-history mr-2"></i>
                                Call History
                                <small class="text-white ml-2">Showing <?php echo count($call_logs_dump) ?> of <?php echo $totalCalls ?? 0 ?> calls</small>
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
                                    <input type="text" class="form-control table-search" placeholder="Search by contact, type or duration..." data-table="table-sortable">
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped table-bordered mb-0 table-sortable">
                                        <thead class="thead-light">
                                        <tr>
                                            <th width="30%">Contact</th>
                                            <th width="15%">Type</th>
                                            <th width="25%">Time</th>
                                            <th width="30%">Duration</th>
                                            <th width="10%">Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (empty($call_logs_dump)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-5">
                                                    <div class="empty-state">
                                                        <i class="fas fa-phone-slash fa-3x text-muted mb-3"></i>
                                                        <h4>No call logs found</h4>
                                                        <p class="text-muted">Your call history will appear here</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($call_logs_dump as $call_log): ?>
                                                <?php
                                                // Format timestamp
                                                if (is_numeric($call_log['Timestamp'])) {
                                                    $dt = date('Y-m-d H:i:s', $call_log['Timestamp'] / 1000);
                                                    $dateOnly = date('M d, Y', $call_log['Timestamp'] / 1000);
                                                    $timeOnly = date('H:i:s', $call_log['Timestamp'] / 1000);
                                                    $dayName = date('D', $call_log['Timestamp'] / 1000);
                                                } else {
                                                    $dt = $call_log['Timestamp'];
                                                    $dateOnly = date('M d, Y', strtotime($call_log['Timestamp']));
                                                    $timeOnly = date('H:i:s', strtotime($call_log['Timestamp']));
                                                    $dayName = date('D', strtotime($call_log['Timestamp']));
                                                }

                                                // Generate avatar from contact name or phone number
                                                if (!empty($call_log['Saved']) && $call_log['Saved'] !== 'Unsaved Contact') {
                                                    $contactName = $call_log['Saved'];
                                                    // Get first character or first letter after space
                                                    $words = explode(' ', $contactName);
                                                    $avatarText = strtoupper(substr($words[0], 0, 1));
                                                    if (count($words) > 1) {
                                                        $avatarText .= strtoupper(substr($words[1], 0, 1));
                                                    }
                                                } else {
                                                    $contactName = $call_log['Caller'];
                                                    // For phone numbers, use first non-digit character or #
                                                    $avatarText = strtoupper(substr(preg_replace('/[0-9]/', '', $contactName), 0, 1));
                                                    if (empty($avatarText) || is_numeric($avatarText)) {
                                                        $avatarText = '#';
                                                    } else {
                                                        $avatarText = strtoupper($avatarText);
                                                    }
                                                }

                                                // Generate unique color based on contact name
                                                $colors = [
                                                    'primary' => ['bg' => 'bg-primary', 'text' => 'text-white'],
                                                    'success' => ['bg' => 'bg-success', 'text' => 'text-white'],
                                                    'info' => ['bg' => 'bg-info', 'text' => 'text-white'],
                                                    'warning' => ['bg' => 'bg-warning', 'text' => 'text-dark'],
                                                    'danger' => ['bg' => 'bg-danger', 'text' => 'text-white'],
                                                    'secondary' => ['bg' => 'bg-secondary', 'text' => 'text-white'],
                                                    'purple' => ['bg' => 'bg-purple', 'text' => 'text-white'],
                                                    'pink' => ['bg' => 'bg-pink', 'text' => 'text-white'],
                                                    'teal' => ['bg' => 'bg-teal', 'text' => 'text-white'],
                                                    'orange' => ['bg' => 'bg-orange', 'text' => 'text-white']
                                                ];

                                                $colorKeys = array_keys($colors);
                                                $colorIndex = crc32($contactName) % count($colorKeys);
                                                $selectedColor = $colorKeys[$colorIndex];
                                                $avatarBg = $colors[$selectedColor]['bg'];
                                                $avatarTextColor = $colors[$selectedColor]['text'];

                                                // Format duration with enhanced styling
                                                $durationSeconds = (int) $call_log['Durations'];

                                                // Duration configuration with icons, colors, and labels
                                                $durationConfigs = [
                                                    // Very short calls (0-15 seconds)
                                                    [
                                                        'max' => 15,
                                                        'icon' => 'bolt',
                                                        'color' => 'secondary',
                                                        'bg' => 'bg-secondary',
                                                        'text' => 'text-secondary',
                                                        'label' => 'Quick',
                                                        'icon_color' => 'text-secondary'
                                                    ],
                                                    // Short calls (16-30 seconds)
                                                    [
                                                        'max' => 30,
                                                        'icon' => 'stopwatch',
                                                        'color' => 'info',
                                                        'bg' => 'bg-info',
                                                        'text' => 'text-info',
                                                        'label' => 'Brief',
                                                        'icon_color' => 'text-info'
                                                    ],
                                                    // Normal calls (31-60 seconds)
                                                    [
                                                        'max' => 60,
                                                        'icon' => 'clock',
                                                        'color' => 'primary',
                                                        'bg' => 'bg-primary',
                                                        'text' => 'text-primary',
                                                        'label' => 'Short',
                                                        'icon_color' => 'text-primary'
                                                    ],
                                                    // Medium calls (61-180 seconds)
                                                    [
                                                        'max' => 180,
                                                        'icon' => 'hourglass-half',
                                                        'color' => 'success',
                                                        'bg' => 'bg-success',
                                                        'text' => 'text-success',
                                                        'label' => 'Medium',
                                                        'icon_color' => 'text-success'
                                                    ],
                                                    // Long calls (181-300 seconds)
                                                    [
                                                        'max' => 300,
                                                        'icon' => 'hourglass',
                                                        'color' => 'warning',
                                                        'bg' => 'bg-warning',
                                                        'text' => 'text-warning',
                                                        'label' => 'Long',
                                                        'icon_color' => 'text-warning'
                                                    ],
                                                    // Very long calls (301-600 seconds)
                                                    [
                                                        'max' => 600,
                                                        'icon' => 'history',
                                                        'color' => 'orange',
                                                        'bg' => 'bg-orange',
                                                        'text' => 'text-orange',
                                                        'label' => 'Extended',
                                                        'icon_color' => 'text-orange'
                                                    ],
                                                    // Extremely long calls (601+ seconds)
                                                    [
                                                        'max' => PHP_INT_MAX,
                                                        'icon' => 'infinity',
                                                        'color' => 'danger',
                                                        'bg' => 'bg-danger',
                                                        'text' => 'text-danger',
                                                        'label' => 'Marathon',
                                                        'icon_color' => 'text-danger'
                                                    ]
                                                ];

                                                // Find the appropriate duration config
                                                $durationConfig = null;
                                                foreach ($durationConfigs as $config) {
                                                    if ($durationSeconds <= $config['max']) {
                                                        $durationConfig = $config;
                                                        break;
                                                    }
                                                }

                                                // Format duration display
                                                if ($durationSeconds < 60) {
                                                    $durationDisplay = $durationSeconds . ' sec';
                                                } else {
                                                    $minutes = floor($durationSeconds / 60);
                                                    $seconds = $durationSeconds % 60;
                                                    $durationDisplay = sprintf('%d:%02d', $minutes, $seconds);
                                                }

                                                // Contact name
                                                $callerNum = $call_log['Caller'];
                                                $cleanCaller = preg_replace('/[^0-9+]/', '', $callerNum);
                                                $contactInfo = null;
                                                if (isset($contactMap[$cleanCaller])) {
                                                    $contactInfo = $contactMap[$cleanCaller];
                                                } elseif (isset($contactMap[$callerNum])) {
                                                    $contactInfo = $contactMap[$callerNum];
                                                }

                                                $displayName = !empty($call_log['Saved']) ? $call_log['Saved'] : ($contactInfo ? $contactInfo['name'] : '');

                                                if (empty($displayName)) {
                                                    $name = '<span class="text-danger"><i>Unsaved Contact</i></span>';
                                                } else {
                                                    if ($contactInfo) {
                                                        $name = '<a href="' . base_url('contacts/analyze/calls/' . $contactInfo['enc_id']) . '" class="font-weight-bold">' . htmlspecialchars($displayName) . '</a>';
                                                    } else {
                                                        $name = '<span class="text-dark font-weight-bold">' . htmlspecialchars($displayName) . '</span>';
                                                    }
                                                }

                                                // Call type with icons and colors
                                                $typeConfig = [
                                                    'Incoming' => [
                                                        'icon' => 'arrow-circle-down',
                                                        'color' => 'info',
                                                        'bg' => 'bg-info',
                                                        'pulse' => 'incoming-pulse'
                                                    ],
                                                    'Outgoing' => [
                                                        'icon' => 'arrow-circle-up',
                                                        'color' => 'success',
                                                        'bg' => 'bg-success',
                                                        'pulse' => 'outgoing-pulse'
                                                    ],
                                                    'Rejected' => [
                                                        'icon' => 'times-circle',
                                                        'color' => 'danger',
                                                        'bg' => 'bg-danger',
                                                        'pulse' => 'rejected-pulse'
                                                    ],
                                                    'Missed' => [
                                                        'icon' => 'phone-slash',
                                                        'color' => 'danger',
                                                        'bg' => 'bg-danger',
                                                        'pulse' => 'missed-pulse'
                                                    ],
                                                    'Blocked' => [
                                                        'icon' => 'shield-alt',
                                                        'color' => 'warning',
                                                        'bg' => 'bg-warning',
                                                        'pulse' => 'blocked-pulse'
                                                    ]
                                                ];

                                                $callType = $call_log['Type'];
                                                $typeInfo = $typeConfig[$callType] ?? [
                                                        'icon' => 'question-circle',
                                                        'color' => 'secondary',
                                                        'bg' => 'bg-secondary',
                                                        'pulse' => ''
                                                    ];
                                                ?>

                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="mr-3">
                                                                <div class="avatar-circle-sm <?php echo $avatarBg; ?> <?php echo $avatarTextColor; ?> shadow-sm">
                                                                    <?php echo $avatarText; ?>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <div class="text-dark"><?php echo $name; ?></div>
                                                                <small class="text-muted"><?php echo htmlspecialchars($call_log['Caller']); ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?php echo $typeInfo['bg']; ?> text-white p-2 <?php echo $typeInfo['pulse']; ?>">
                                                            <i class="fas fa-<?php echo $typeInfo['icon']; ?> mr-1"></i>
                                                            <?php echo $callType; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="text-dark">
                                                            <i class="fas fa-calendar-day text-primary mr-1"></i>
                                                            <?php echo $dateOnly; ?>
                                                        </div>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock text-secondary mr-1"></i>
                                                            <?php echo $timeOnly; ?>
                                                            <span class="badge badge-light ml-2"><?php echo $dayName; ?></span>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div class="duration-display">
                                                            <div class="d-flex align-items-center">
                                                                <div class="mr-2">
                                                                    <span class="badge <?php echo $durationConfig['bg']; ?> text-white p-2">
                                                                        <i class="fas fa-<?php echo $durationConfig['icon']; ?>"></i>
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <div class="<?php echo $durationConfig['text']; ?> font-weight-bold">
                                                                        <?php echo $durationDisplay; ?>
                                                                    </div>
                                                                    <small class="text-muted">
                                                                        <i class="fas fa-<?php echo $durationConfig['icon']; ?> mr-1 <?php echo $durationConfig['icon_color']; ?>"></i>
                                                                        <?php echo $durationConfig['label']; ?>
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-row"
                                                            data-id="<?php echo $call_log['ID'] ?? $call_log['counter'] ?? ''; ?>"
                                                            data-url="<?= base_url('call_logs/delete') ?>"
                                                            data-name="<?php echo htmlspecialchars(strip_tags($name)); ?>"
                                                            title="Delete call log entry">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
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
                                        to <?php echo min($currentPage * $perPage, $totalCalls ?? 0) ?>
                                        of <?php echo $totalCalls ?? 0 ?> entries
                                    </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="float-right">
                                            <?php if (isset($pager) && $totalCalls > $perPage): ?>
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
<script>
$(document).ready(function() {
    // Real-time table search
    $('.table-search').on('keyup', function() {
        var keyword = $(this).val().toLowerCase();
        var table = $(this).data('table');
        $('.' + table + ' tbody tr').each(function() {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(keyword) > -1);
        });
    });

    // Column sorting
    $('.table-sortable thead th').on('click', function() {
        var table = $(this).closest('table');
        var tbody = table.find('tbody');
        var index = $(this).index();
        var rows = tbody.find('tr').toArray();
        var asc = !$(this).hasClass('sort-asc');
        table.find('thead th').removeClass('sort-asc sort-desc');
        $(this).toggleClass('sort-asc', asc).toggleClass('sort-desc', !asc);
        rows.sort(function(a, b) {
            var aVal = $(a).find('td').eq(index).text().trim();
            var bVal = $(b).find('td').eq(index).text().trim();
            var aNum = parseFloat(aVal), bNum = parseFloat(bVal);
            if (!isNaN(aNum) && !isNaN(bNum)) return asc ? aNum - bNum : bNum - aNum;
            return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
        });
        tbody.append(rows);
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
                filename:     'call_logs_export_' + Date.now() + '.pdf',
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
});
</script>
<?php include __DIR__ . '/../partials/_delete_confirm.php'; ?>
    </div>
    <!-- /.content-wrapper -->

    <style>
        .avatar-circle-sm {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .empty-state {
            padding: 3rem 1rem;
            text-align: center;
        }

        .empty-state i {
            opacity: 0.5;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .duration-display .badge {
            min-width: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Color classes for extended Bootstrap colors */
        .bg-purple { background-color: #6f42c1 !important; }
        .bg-pink { background-color: #e83e8c !important; }
        .bg-teal { background-color: #20c997 !important; }
        .bg-orange { background-color: #fd7e14 !important; }

        .text-purple { color: #6f42c1 !important; }
        .text-pink { color: #e83e8c !important; }
        .text-teal { color: #20c997 !important; }
        .text-orange { color: #fd7e14 !important; }

        /* Pulse animations for call types */
        .incoming-pulse {
            animation: incomingPulse 2s infinite;
        }

        .outgoing-pulse {
            animation: outgoingPulse 2s infinite;
        }

        .rejected-pulse {
            animation: rejectedPulse 2s infinite;
        }

        .missed-pulse {
            animation: missedPulse 2s infinite;
        }

        .blocked-pulse {
            animation: blockedPulse 2s infinite;
        }

        @keyframes incomingPulse {
            0% { box-shadow: 0 0 0 0 rgba(23, 162, 184, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(23, 162, 184, 0); }
            100% { box-shadow: 0 0 0 0 rgba(23, 162, 184, 0); }
        }

        @keyframes outgoingPulse {
            0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(40, 167, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
        }

        @keyframes rejectedPulse {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }

        @keyframes missedPulse {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }

        @keyframes blockedPulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(255, 193, 7, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
        }

        /* Hover effects */
        .duration-display:hover .badge {
            transform: scale(1.1);
            transition: transform 0.2s ease;
        }

        .avatar-circle-sm:hover {
            transform: scale(1.1);
            transition: transform 0.2s ease;
        }

    @media (max-width: 768px) {
        .entry-info {
            text-align: center;
            margin-bottom: 1rem;
        }

        .float-right {
                float: none !important;
                text-align: center;
            }

            .duration-display .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .duration-display .mr-2 {
                margin-right: 0 !important;
                margin-bottom: 0.5rem;
            }

            .avatar-circle-sm {
                width: 32px;
                height: 32px;
                font-size: 14px;
            }
        }
        .table-sortable thead th { cursor: pointer; user-select: none; }
        .table-sortable thead th.sort-asc::after { content: ' \25B2'; font-size: 0.7em; }
        .table-sortable thead th.sort-desc::after { content: ' \25BC'; font-size: 0.7em; }
    </style>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-8 col-md-6">
                        <div class="d-flex align-items-center">
                            <h1 class="h2 mb-0">
                                <i class="fas fa-sms text-primary mr-2"></i>
                                <?php echo $sms_head ?? 'All SMS' ?>
                            </h1>
                            <div class="ml-3">
                                    <span class="badge badge-light border p-2">
                                        <i class="fas fa-chart-bar text-primary mr-1"></i>
                                        Total: <b><?php echo $totalSmsInbox ?? 0 ?></b>
                                    </span>
                            </div>
                        </div>
                        <p class="text-white mt-2 mb-0">View and manage all your SMS messages</p>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="float-right mt-2">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <?php echo $sms_urls; ?>
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
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-comments mr-2"></i>
                                    SMS Messages
                                    <small class="text-white ml-2">Showing <?php echo count($sms_dump) ?>
                                        of <?php echo $totalSMS ?? 0 ?> messages</small>
                                </h3>
                                <div class="card-tools ml-auto">
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
                                    <input type="text" class="form-control table-search" placeholder="Search by sender or message..." data-table="table-sortable">
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped table-bordered mb-0 table-sortable">
                                        <thead class="thead-light">
                                        <tr>
                                            <th width="20%">Contact</th>
                                            <th width="15%">Type</th>
                                            <th width="22%">Time</th>
                                            <th width="43%">Message</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (empty($sms_dump)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-5">
                                                    <div class="empty-state">
                                                        <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                                                        <h4>No SMS messages found</h4>
                                                        <p class="text-muted">Your SMS messages will appear here</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($sms_dump as $index => $smsinfo): ?>
                                                <?php
                                                // Format timestamp with day name
                                                if (is_numeric($smsinfo['sms_time'])) {
                                                    $dt = date('Y-m-d H:i:s', $smsinfo['sms_time'] / 1000);
                                                    $dateOnly = date('M d, Y', $smsinfo['sms_time'] / 1000);
                                                    $timeOnly = date('H:i:s', $smsinfo['sms_time'] / 1000);
                                                    $dayName = date('D', $smsinfo['sms_time'] / 1000);
                                                } else {
                                                    $dt = $smsinfo['sms_time'];
                                                    $dateOnly = date('M d, Y', strtotime($smsinfo['sms_time']));
                                                    $timeOnly = date('H:i:s', strtotime($smsinfo['sms_time']));
                                                    $dayName = date('D', strtotime($smsinfo['sms_time']));
                                                }

                                                // Decode message
                                                $msg = base64_decode($smsinfo['sms_body']);
                                                $shortMsg = character_limiter($msg, 80);

                                                // SMS type with icons and colors - SENT = BLACK, RECEIVED = GREEN
                                                $smsType = strtolower($smsinfo['sms_type']);

                                                // Determine row background color based on SMS type
                                                $rowBgClass = '';
                                                $messagePreviewBg = '';
                                                $messagePreviewBorder = '';
                                                $messagePreviewText = '';

                                                if ($smsType === 'sent') {
                                                    $rowBgClass = 'bg-light'; // Light gray background for sent
                                                    $messagePreviewBg = 'bg-dark'; // Black background for sent messages
                                                    $messagePreviewBg = 'bg-grey'; // Black background for sent messages
                                                    $messagePreviewBorder = 'border-dark';
                                                    $messagePreviewText = 'text-black'; // White text on black
                                                } else if ($smsType === 'inbox') {
                                                    $rowBgClass = 'bg-light-green'; // Light green background for received
                                                    $messagePreviewBg = 'bg-dark'; // Green background for received messages
                                                    $messagePreviewBg = 'bg-grey'; // Green background for received messages
                                                    $messagePreviewBorder = 'border-success';
                                                    $messagePreviewText = 'text-black'; // White text on green
                                                }

                                                $typeConfig = [
                                                    'inbox' => [
                                                        'icon' => 'inbox',
                                                        'color' => 'success',
                                                        'bg' => 'bg-success',
                                                        'label' => 'Received',
                                                        'pulse' => 'incoming-pulse',
                                                        'row_color' => 'success-row'
                                                    ],
                                                    'sent' => [
                                                        'icon' => 'paper-plane',
                                                        'color' => 'dark',
                                                        'bg' => 'bg-dark',
                                                        'label' => 'Sent',
                                                        'pulse' => 'outgoing-pulse',
                                                        'row_color' => 'sent-row'
                                                    ]
                                                ];

                                                $typeInfo = $typeConfig[$smsType] ?? [
                                                        'icon' => 'question-circle',
                                                        'color' => 'secondary',
                                                        'bg' => 'bg-secondary',
                                                        'label' => $smsinfo['sms_type'],
                                                        'pulse' => '',
                                                        'row_color' => ''
                                                    ];

                                                // Generate avatar from contact name/phone number
                                                $contactName = $smsinfo['sms_number'];

                                                // Try to get contact name from database if available
                                                $avatarText = '?';
                                                if (!empty($smsinfo['contact_name'])) {
                                                    $contactDisplayName = $smsinfo['contact_name'];
                                                    $words = explode(' ', $contactDisplayName);
                                                    $avatarText = strtoupper(substr($words[0], 0, 1));
                                                    if (count($words) > 1) {
                                                        $avatarText .= strtoupper(substr($words[1], 0, 1));
                                                    }
                                                } else {
                                                    $contactDisplayName = $contactName;
                                                    // For phone numbers, try to extract meaningful characters
                                                    $cleanNumber = preg_replace('/[^A-Za-z]/', '', $contactName);
                                                    if (!empty($cleanNumber)) {
                                                        $avatarText = strtoupper(substr($cleanNumber, 0, 2));
                                                        if (strlen($avatarText) < 2) {
                                                            $avatarText = strtoupper($contactName[0] . $contactName[1] ?? $contactName[0]);
                                                        }
                                                    } else {
                                                        // Use first two digits or characters
                                                        $avatarText = strtoupper(substr($contactName, 0, 2));
                                                        if (is_numeric($avatarText)) {
                                                            $avatarText = '#' . substr($contactName, 1, 1);
                                                        }
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

                                                // Message length categorization
                                                $msgLength = strlen($msg);
                                                $lengthConfigs = [
                                                    [
                                                        'max' => 20,
                                                        'icon' => 'comment-alt',
                                                        'color' => 'secondary',
                                                        'bg' => 'bg-secondary',
                                                        'text' => 'text-secondary',
                                                        'label' => 'Short',
                                                        'icon_color' => 'text-secondary'
                                                    ],
                                                    [
                                                        'max' => 100,
                                                        'icon' => 'comment',
                                                        'color' => 'info',
                                                        'bg' => 'bg-info',
                                                        'text' => 'text-info',
                                                        'label' => 'Medium',
                                                        'icon_color' => 'text-info'
                                                    ],
                                                    [
                                                        'max' => 300,
                                                        'icon' => 'comments',
                                                        'color' => 'primary',
                                                        'bg' => 'bg-primary',
                                                        'text' => 'text-primary',
                                                        'label' => 'Long',
                                                        'icon_color' => 'text-primary'
                                                    ],
                                                    [
                                                        'max' => PHP_INT_MAX,
                                                        'icon' => 'file-alt',
                                                        'color' => 'warning',
                                                        'bg' => 'bg-warning',
                                                        'text' => 'text-warning',
                                                        'label' => 'Very Long',
                                                        'icon_color' => 'text-warning'
                                                    ]
                                                ];

                                                // Find appropriate length config
                                                $lengthConfig = null;
                                                foreach ($lengthConfigs as $config) {
                                                    if ($msgLength <= $config['max']) {
                                                        $lengthConfig = $config;
                                                        break;
                                                    }
                                                }
                                                ?>

                                                <!-- Main Row -->
                                                <tr class="accordion-toggle expandable-row <?php echo $rowBgClass; ?> <?php echo $typeInfo['row_color']; ?>"
                                                    data-target="#sms-details-<?php echo $index; ?>">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="mr-3">
                                                                <div class="avatar-circle-sm <?php echo $avatarBg; ?> <?php echo $avatarTextColor; ?> shadow-sm">
                                                                    <?php echo $avatarText; ?>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <div class="text-dark font-weight-bold">
                                                                    <?php
                                                                    if (!empty($smsinfo['contact_name'])) {
                                                                        echo htmlspecialchars($smsinfo['contact_name']);
                                                                    } else {
                                                                        echo htmlspecialchars($contactName);
                                                                    }
                                                                    ?>
                                                                </div>
                                                                <small class="text-muted"><?php echo $smsinfo['sms_number']; ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                            <span class="badge <?php echo $typeInfo['bg']; ?> text-white p-2 <?php echo $typeInfo['pulse']; ?>">
                                                                <i class="fas fa-<?php echo $typeInfo['icon']; ?> mr-1"></i>
                                                                <?php echo $typeInfo['label']; ?>
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
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div class="message-preview">
                                                                <div class="d-flex align-items-center">
                                                                    <div>
                                                                        <div class="message-bubble <?php echo $messagePreviewBg; ?> <?php echo $messagePreviewBorder; ?> <?php echo $messagePreviewText; ?> rounded p-2">
                                                                            <?php echo htmlspecialchars($shortMsg); ?>
                                                                        </div>
                                                                        <small class="text-muted mt-1 d-block">
                                                                            <i class="fas fa-<?php echo $lengthConfig['icon']; ?> mr-1 <?php echo $lengthConfig['icon_color']; ?>"></i>
                                                                            <?php echo $lengthConfig['label']; ?> (<?php echo $msgLength; ?> chars)
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <i class="fas fa-chevron-down text-muted chevron-icon"></i>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-- Expandable Details Row -->
                                                <tr class="expandable-content" style="display: none;">
                                                    <td colspan="4" class="p-0 border-0">
                                                        <div id="sms-details-<?php echo $index; ?>" style="display: none;">
                                                            <div class="card card-body bg-light border-0 m-0 p-3">
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <h6 class="text-muted mb-2">Message Details:</h6>
                                                                        <div class="small">
                                                                            <div class="mb-1">
                                                                                <i class="fas fa-hashtag mr-2"></i>
                                                                                <strong>ID:</strong> <?php echo $smsinfo['id'] ?? 'N/A'; ?>
                                                                            </div>
                                                                            <div class="mb-1">
                                                                                <i class="fas fa-thread mr-2"></i>
                                                                                <strong>Thread
                                                                                    ID:</strong> <?php echo $smsinfo['sms_thread_id'] ?? 'N/A'; ?>
                                                                            </div>
                                                                            <div class="mb-1">
                                                                                <i class="fas fa-phone mr-2"></i>
                                                                                <strong>Number:</strong> <?php echo $smsinfo['sms_number']; ?>
                                                                            </div>
                                                                            <div class="mb-1">
                                                                                <i class="fas fa-ruler mr-2"></i>
                                                                                <strong>Length:</strong> <?php echo $msgLength; ?> characters
                                                                            </div>
                                                                            <div class="mb-1">
                                                                                <i class="fas fa-envelope mr-2"></i>
                                                                                <strong>Type:</strong>
                                                                                <span class="badge <?php echo $typeInfo['bg']; ?> text-white">
                                                                                        <?php echo $typeInfo['label']; ?>
                                                                                    </span>
                                                                            </div>
                                                                            <div class="mb-1 mt-3">
                                                                                <button class="btn btn-sm btn-outline-danger delete-sms"
                                                                                        data-id="<?php echo $smsinfo['id'] ?? ''; ?>"
                                                                                        title="Delete this message">
                                                                                    <i class="fas fa-trash mr-1"></i> Delete
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-9">
                                                                        <h6 class="text-muted mb-2">Full Message:</h6>
                                                                        <div class="message-content-full <?php echo $messagePreviewBg; ?> <?php echo $messagePreviewText; ?> border rounded p-3">
                                                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($msg)); ?></p>
                                                                        </div>
                                                                        <div class="mt-3 text-right">
                                                                            <button class="btn btn-sm btn-outline-primary"
                                                                                    onclick="copyToClipboard('<?php echo addslashes(htmlspecialchars($msg)); ?>', this)">
                                                                                <i class="fas fa-copy mr-1"></i> Copy
                                                                                Message
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
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
                                            to <?php echo min($currentPage * $perPage, $totalSmsInbox ?? 0) ?>
                                            of <?php echo $totalSmsInbox ?? 0 ?> entries
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="float-right">
                                            <?php if (isset($pager) && $totalSmsInbox > $perPage): ?>
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

        .message-preview .badge {
            min-width: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .message-preview {
            max-width: 85%;
        }

        .accordion-toggle {
            cursor: pointer;
        }

        /* Row background colors */
        .bg-light-green {
            background-color: #f0fff4 !important; /* Very light green for received rows */
        }

        .bg-light {
            background-color: #f8f9fa !important; /* Light gray for sent rows */
        }

        .success-row:hover {
            background-color: #e8f5e9 !important; /* Slightly darker green on hover */
        }

        .sent-row:hover {
            background-color: #e9ecef !important; /* Slightly darker gray on hover */
        }

        /* Message bubble styling */
        .message-bubble {
            max-width: 300px;
            word-wrap: break-word;
            position: relative;
        }

        .message-bubble.bg-success {
            border-left: 4px solid #28a745;
        }

        .message-bubble.bg-dark {
            border-left: 4px solid #343a40;
        }

        .message-content-full {
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: monospace;
            font-size: 14px;
        }

        /* Expandable row styling */
        .expandable-row {
            border-bottom: 2px solid #dee2e6;
        }

        .expandable-content {
            background-color: #f8f9fa;
        }

        .expandable-content .card-body {
            padding: 1rem;
        }

        .chevron-icon {
            transition: transform 0.3s ease;
        }

        .chevron-icon.rotated {
            transform: rotate(180deg);
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

        /* Pulse animations for message types */
        .incoming-pulse {
            animation: incomingPulse 2s infinite;
        }

        .outgoing-pulse {
            animation: outgoingPulse 2s infinite;
        }

        @keyframes incomingPulse {
            0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(40, 167, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
        }

        @keyframes outgoingPulse {
            0% { box-shadow: 0 0 0 0 rgba(52, 58, 64, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(52, 58, 64, 0); }
            100% { box-shadow: 0 0 0 0 rgba(52, 58, 64, 0); }
        }

        /* Hover effects */
        .message-preview:hover .badge {
            transform: scale(1.1);
            transition: transform 0.2s ease;
        }

        .avatar-circle-sm:hover {
            transform: scale(1.1);
            transition: transform 0.2s ease;
        }

        .message-bubble:hover {
            transform: translateY(-2px);
            transition: transform 0.2s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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

            .message-preview .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .message-preview .mr-2 {
                margin-right: 0 !important;
                margin-bottom: 0.5rem;
            }

            .avatar-circle-sm {
                width: 32px;
                height: 32px;
                font-size: 14px;
            }

            .message-preview {
                max-width: 75%;
            }

            .message-bubble {
                max-width: 200px;
            }
        }
        .table-sortable thead th { cursor: pointer; user-select: none; }
        .table-sortable thead th.sort-asc::after { content: ' \25B2'; font-size: 0.7em; }
        .table-sortable thead th.sort-desc::after { content: ' \25BC'; font-size: 0.7em; }
    </style>
    <script>
        // Copy to clipboard function
        function copyToClipboard(text, buttonElement) {
            // Create a temporary textarea element
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.left = '-999999px';
            textarea.style.top = '-999999px';
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();

            try {
                document.execCommand('copy');
                // Show success feedback
                const originalText = buttonElement.innerHTML;
                buttonElement.innerHTML = '<i class="fas fa-check mr-1"></i> Copied!';
                buttonElement.classList.remove('btn-outline-primary');
                buttonElement.classList.add('btn-success');

                setTimeout(() => {
                    buttonElement.innerHTML = originalText;
                    buttonElement.classList.remove('btn-success');
                    buttonElement.classList.add('btn-outline-primary');
                }, 2000);
            } catch (err) {
                console.error('Could not copy text: ', err);
                alert('Failed to copy message. Please try again.');
            }

            document.body.removeChild(textarea);
        }

        // Simple accordion functionality without Bootstrap collapse
        document.addEventListener('DOMContentLoaded', function () {
            const accordionItems = document.querySelectorAll('.accordion-toggle');

            accordionItems.forEach(item => {
                item.addEventListener('click', function (e) {
                    // Prevent default if clicking on a button inside
                    if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
                        return;
                    }

                    const targetId = this.getAttribute('data-target');
                    const target = document.querySelector(targetId);
                    const chevron = this.querySelector('.chevron-icon');
                    const expandableRow = this.closest('tr').nextElementSibling;

                    if (!target || !expandableRow) return;

                    // Check if this item is currently open
                    const isCurrentlyOpen = target.style.display === 'block';

                    // Close all other accordion items
                    accordionItems.forEach(otherItem => {
                        if (otherItem !== this) {
                            const otherTargetId = otherItem.getAttribute('data-target');
                            const otherTarget = document.querySelector(otherTargetId);
                            const otherChevron = otherItem.querySelector('.chevron-icon');
                            const otherExpandableRow = otherItem.closest('tr').nextElementSibling;

                            if (otherTarget && otherExpandableRow) {
                                otherTarget.style.display = 'none';
                                otherExpandableRow.style.display = 'none';
                                if (otherChevron) {
                                    otherChevron.classList.remove('rotated');
                                }
                            }
                        }
                    });

                    // Toggle current item
                    if (!isCurrentlyOpen) {
                        target.style.display = 'block';
                        expandableRow.style.display = 'table-row';
                        if (chevron) {
                            chevron.classList.add('rotated');
                        }
                    } else {
                        target.style.display = 'none';
                        expandableRow.style.display = 'none';
                        if (chevron) {
                            chevron.classList.remove('rotated');
                        }
                    }
                });
            });

            // Close accordion when clicking outside
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.accordion-toggle') && !e.target.closest('.expandable-content')) {
                    accordionItems.forEach(item => {
                        const targetId = item.getAttribute('data-target');
                        const target = document.querySelector(targetId);
                        const chevron = item.querySelector('.chevron-icon');
                        const expandableRow = item.closest('tr').nextElementSibling;

                        if (target && expandableRow) {
                            target.style.display = 'none';
                            expandableRow.style.display = 'none';
                            if (chevron) {
                                chevron.classList.remove('rotated');
                            }
                        }
                    });
                }
            });
        });
    </script>
<script>
var base_url = function(path) { return '<?= base_url() ?>' + path; };
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.table-search')?.addEventListener('keyup', function() {
        var keyword = this.value.toLowerCase();
        var target = this.getAttribute('data-table');
        document.querySelectorAll('.' + target + ' tbody tr.expandable-row').forEach(function(row) {
            row.style.display = row.textContent.toLowerCase().indexOf(keyword) > -1 ? '' : 'none';
        });
    });
    document.querySelectorAll('.table-sortable thead th').forEach(function(th) {
        th.addEventListener('click', function() {
            var table = this.closest('table');
            var tbody = table.querySelector('tbody');
            var index = Array.prototype.indexOf.call(this.parentNode.children, this);
            var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr.expandable-row'));
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
    document.querySelectorAll('.delete-sms').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var id = this.getAttribute('data-id');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete SMS Message?',
                    text: 'Are you sure you want to delete this message?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash"></i> Delete'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        fetch(base_url('sms/delete/' + id), {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        }).then(function(r) { return r.json(); }).then(function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', 'SMS message has been deleted.', 'success').then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error!', response.message || 'Failed to delete SMS.', 'error');
                            }
                        }).catch(function() {
                            Swal.fire('Error!', 'Failed to delete SMS.', 'error');
                        });
                    }
                });
            }
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
            filename:     'sms_export_' + Date.now() + '.pdf',
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
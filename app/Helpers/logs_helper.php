<?php

if (!function_exists('renderLogsTable')) {
    /**
     * Renders logs table with formatted data.
     *
     * @param array $logs
     * @param string $title
     * @return string
     */
    function renderLogsTable(array $logs, string $title = 'LogsController'): string
    {
        if (empty($logs)) {
            return '
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                No log entries found.
            </div>';
        }

        $html = '
        <div class="table-responsive">
            <table class="table table-hover table-striped" id="logsTable">
                <thead class="thead-light">
                    <tr>
                        <th><i class="fas fa-hashtag mr-2"></i>ID</th>
                        <th><i class="fas fa-tasks mr-2"></i>Action</th>
                        <th><i class="fas fa-tag mr-2"></i>Category</th>
                        <th><i class="fas fa-exclamation mr-2"></i>Severity</th>
                        <th><i class="fas fa-globe mr-2"></i>IP Address</th>
                        <th><i class="fas fa-desktop mr-2"></i>Device</th>
                        <th><i class="fas fa-circle mr-2"></i>Status</th>
                        <th><i class="fas fa-calendar mr-2"></i>Timestamp</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($logs as $log) {
            // Status badge
            $status = $log['Status'] ?? 'info';
            $statusBadge = '';
            switch ($status) {
                case 'success':
                    $statusBadge = '<span class="badge badge-success">Success</span>';
                    break;
                case 'failed':
                    $statusBadge = '<span class="badge badge-danger">Failed</span>';
                    break;
                case 'warning':
                    $statusBadge = '<span class="badge badge-warning">Warning</span>';
                    break;
                case 'suspicious':
                    $statusBadge = '<span class="badge badge-dark">Suspicious</span>';
                    break;
                default:
                    $statusBadge = '<span class="badge badge-info">Info</span>';
            }

            // Severity badge
            $severity = $log['action_severity'] ?? 'low';
            $severityBadge = '';
            switch ($severity) {
                case 'critical':
                    $severityBadge = '<span class="badge badge-danger">Critical</span>';
                    break;
                case 'high':
                    $severityBadge = '<span class="badge badge-warning">High</span>';
                    break;
                case 'medium':
                    $severityBadge = '<span class="badge badge-info">Medium</span>';
                    break;
                default:
                    $severityBadge = '<span class="badge badge-secondary">Low</span>';
            }

            // Category icon
            $category = $log['action_category'] ?? 'system';
            $categoryIcon = getCategoryIcon($category);

            // Device icon
            $deviceType = $log['device_type'] ?? 'unknown';
            $deviceIcon = getDeviceIcon($deviceType);

            // Format timestamp
            $timestamp = $log['Timestamps'] ?? time();
            $formattedTime = date('M d, Y, l H:i:s', $timestamp);

            $fileCategoryHtml = '';
            if (!empty($log['file_category'])) {
                $catIcon = getFileCategoryIcon($log['file_category']);
                $catLabel = ucfirst($log['file_category']);
                $fileCategoryHtml = ' <i class="' . $catIcon . '" title="' . $catLabel . '"></i> ';
            }

            $fileSizeHtml = '';
            if (!empty($log['file_size_formatted'])) {
                $fileSizeHtml = ' <span class="badge badge-info">' . $log['file_size_formatted'] . '</span>';
            }

            $html .= '
                <tr>
                    <td>' . ($log['counter'] ?? $log['id'] ?? 'N/A') . '</td>
                    <td>
                        <i class="' . $categoryIcon . ' mr-2"></i>
                        ' . htmlspecialchars($log['Action'] ?? 'Unknown Action') . $fileCategoryHtml . $fileSizeHtml . '
                    </td>
                    <td>' . ucfirst($category) . '</td>
                    <td>' . $severityBadge . '</td>
                    <td><code>' . htmlspecialchars($log['IP'] ?? 'N/A') . '</code></td>
                    <td>
                        <i class="' . $deviceIcon . ' mr-2"></i>
                        ' . htmlspecialchars($log['Device'] ?? 'Unknown Device') . '
                    </td>
                    <td>' . $statusBadge . '</td>
                    <td>' . $formattedTime . '</td>
                </tr>';
        }

        $html .= '
                </tbody>
            </table>
        </div>';

        return $html;
    }
}

if (!function_exists('renderLogsSummary')) {
    /**
     * Renders a summary table of grouped log actions with frequency counters.
     *
     * @param array $groups [['action_type' => ..., 'action_category' => ..., 'frequency' => ..., 'last_occurrence' => ...]]
     * @return string
     */
    function renderLogsSummary(array $groups): string
    {
        if (empty($groups)) {
            return '
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                No log entries found.
            </div>';
        }

        $html = '
        <div class="table-responsive">
            <table class="table table-hover table-striped" id="logsSummaryTable">
                <thead class="thead-light">
                    <tr>
                        <th><i class="fas fa-tasks mr-2"></i>Action</th>
                        <th><i class="fas fa-tag mr-2"></i>Category</th>
                        <th><i class="fas fa-sort-amount-down mr-2"></i>Frequency</th>
                        <th><i class="fas fa-clock mr-2"></i>Last Occurrence</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($groups as $g) {
            $action = htmlspecialchars($g['action_type'] ?? 'Unknown');
            $category = htmlspecialchars($g['action_category'] ?? 'system');
            $freq = (int) ($g['frequency'] ?? 1);
            $lastOccurrence = !empty($g['last_occurrence'])
                ? date('M d, Y, l H:i', strtotime($g['last_occurrence']))
                : '—';

            $color = $freq > 100 ? 'danger' : ($freq > 20 ? 'warning' : 'info');

            $html .= '
                <tr>
                    <td><i class="' . getCategoryIcon($category) . ' mr-2"></i>' . $action . '</td>
                    <td><span class="badge badge-secondary">' . ucfirst($category) . '</span></td>
                    <td><span class="badge badge-' . $color . ' p-2">' . $freq . ' time' . ($freq !== 1 ? 's' : '') . '</span></td>
                    <td><small class="text-muted">' . $lastOccurrence . '</small></td>
                </tr>';
        }

        $html .= '
                </tbody>
            </table>
        </div>';

        return $html;
    }
}

if (!function_exists('formatFileSize')) {
    /**
     * Formats bytes into a human-readable file size string.
     */
    function formatFileSize($bytes): string
    {
        if ($bytes === null || $bytes === '' || $bytes === 0) return '—';
        $bytes = (float) $bytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return number_format($bytes, $i === 0 ? 0 : 1) . ' ' . $units[$i];
    }
}

if (!function_exists('getCategoryIcon')) {
    /**
     * Gets category icon.
     *
     * @param string $category
     * @return string
     */
    function getCategoryIcon(string $category): string
    {
        $icons = [
            'authentication' => 'fas fa-key text-primary',
            'file' => 'fas fa-file text-info',
            'profile' => 'fas fa-user text-success',
            'admin' => 'fas fa-cog text-warning',
            'system' => 'fas fa-server text-secondary',
            'security' => 'fas fa-shield-alt text-danger'
        ];

        return $icons[$category] ?? 'fas fa-question-circle text-muted';
    }
}

if (!function_exists('getFileCategoryIcon')) {
    /**
     * Gets icon for a file sub-category (SMS, ContactsController, CallsController, FilesController, LocationController, etc.)
     *
     * @param string $category
     * @return string
     */
    function getFileCategoryIcon(string $category): string
    {
        $icons = [
            'sms' => 'fas fa-comments text-danger',
            'contacts' => 'fas fa-address-book text-warning',
            'files' => 'fas fa-file text-info',
            'calls' => 'fas fa-phone text-success',
            'call_logs' => 'fas fa-phone text-success',
            'location' => 'fas fa-map-marker-alt text-primary',
            'apps' => 'fas fa-mobile-alt text-secondary',
            'device' => 'fas fa-cogs text-secondary',
            'device_context' => 'fas fa-info-circle text-secondary',
            'context' => 'fas fa-info-circle text-secondary',
            'network' => 'fas fa-wifi text-secondary',
            'network_info' => 'fas fa-wifi text-secondary',
            'accounts' => 'fas fa-user-circle text-secondary',
            'calendar' => 'fas fa-calendar-alt text-secondary',
            'app_usage' => 'fas fa-chart-bar text-secondary',
            'usage' => 'fas fa-chart-bar text-secondary',
            'notifications' => 'fas fa-bell text-secondary',
            'bluetooth' => 'fab fa-bluetooth text-secondary',
            'sensors' => 'fas fa-microchip text-secondary',
            'sensor' => 'fas fa-microchip text-secondary',
            'logs' => 'fas fa-clipboard-list text-secondary',
        ];
        return $icons[$category] ?? 'fas fa-file text-info';
    }
}

if (!function_exists('getDeviceIcon')) {
    /**
     * Gets device icon.
     *
     * @param string $deviceType
     * @return string
     */
    function getDeviceIcon(string $deviceType): string
    {
        $icons = [
            'desktop' => 'fas fa-desktop text-primary',
            'mobile' => 'fas fa-mobile-alt text-success',
            'tablet' => 'fas fa-tablet-alt text-info',
            'bot' => 'fas fa-robot text-secondary',
            'unknown' => 'fas fa-question-circle text-muted'
        ];

        return $icons[$deviceType] ?? 'fas fa-question-circle text-muted';
    }
}
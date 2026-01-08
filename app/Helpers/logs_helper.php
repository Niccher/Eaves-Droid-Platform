<?php

if (!function_exists('renderLogsTable')) {
    /**
     * Renders logs table HTML
     *
     * @param array $logs
     * @param string $title
     * @return string
     */
    function renderLogsTable($logs, $title = 'Activities') {
        if (empty($logs)) {
            return '
            <div class="text-center py-5">
                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                <h4>No ' . htmlspecialchars($title) . ' Found</h4>
                <p class="text-muted">No access activities have been recorded in this category yet.</p>
            </div>';
        }

        // Category badge colors
        $categoryColors = [
            'authentication' => 'primary',
            'file' => 'info',
            'profile' => 'success',
            'admin' => 'warning',
            'system' => 'secondary',
            'security' => 'danger'
        ];

        // Severity badge colors
        $severityColors = [
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'dark'
        ];

        // Status badge colors
        $statusColors = [
            'success' => 'success',
            'failed' => 'danger',
            'warning' => 'warning',
            'suspicious' => 'danger',
            'info' => 'info'
        ];

        $html = '<div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th width="15%">Date & Time</th>
                                <th width="10%">Category</th>
                                <th width="15%">Action</th>
                                <th width="10%">Severity</th>
                                <th width="15%">Device</th>
                                <th width="10%">IP Address</th>
                                <th width="10%">Status</th>
                                <th width="15%">Details</th>
                            </tr>
                        </thead>
                        <tbody>';

        foreach ($logs as $entry) {
            $timestamp = $entry['Timestamps'] ?? time();
            $date = date('d/m/Y', $timestamp);
            $time = date('H:i:s', $timestamp);
            $action = $entry['Action'] ?? 'Unknown Action';
            $category = $entry['action_category'] ?? 'system';
            $severity = $entry['action_severity'] ?? 'low';
            $deviceType = $entry['device_type'] ?? 'unknown';
            $deviceName = $entry['Device'] ?? 'Unknown Device';
            $ip = $entry['IP'] ?? 'N/A';
            $status = $entry['Status'] ?? 'info';

            // Status text
            $statusText = $status;
            if ($status === 'suspicious') {
                $statusText = 'Suspicious';
            }

            // Get icons
            $categoryIcon = $entry['CategoryIcon'] ?? 'fa-question-circle';
            $severityIcon = $entry['SeverityIcon'] ?? 'fa-circle text-secondary';
            $deviceIcon = $entry['DeviceIcon'] ?? 'fa-question-circle text-muted';

            $html .= '<tr>
                        <td>
                            <div class="text-dark font-weight-bold">' . $date . '</div>
                            <small class="text-muted">' . $time . '</small>
                        </td>
                        <td>
                            <span class="badge badge-' . ($categoryColors[$category] ?? 'secondary') . '">
                                <i class="fas ' . $categoryIcon . ' mr-1"></i>
                                ' . ucfirst($category) . '
                            </span>
                        </td>
                        <td>
                            <div class="text-dark">' . htmlspecialchars($action) . '</div>';

            if (!empty($entry['request_url'])) {
                $html .= '<small class="text-muted d-block text-truncate" style="max-width: 200px;">
                            ' . htmlspecialchars($entry['request_url']) . '
                          </small>';
            }

            $html .= '</td>
                      <td>
                          <span class="badge badge-' . ($severityColors[$severity] ?? 'secondary') . '">
                              <i class="fas ' . $severityIcon . ' mr-1"></i>
                              ' . ucfirst($severity) . '
                          </span>
                      </td>
                      <td>
                          <div class="d-flex align-items-center">
                              <i class="fas ' . $deviceIcon . ' mr-2"></i>
                              <div>
                                  <div class="text-dark">' . htmlspecialchars($deviceName) . '</div>';

            if (!empty($entry['operating_system'])) {
                $html .= '<small class="text-muted">' . htmlspecialchars($entry['operating_system']) . '</small>';
            }

            $html .= '</div>
                          </div>
                      </td>
                      <td>
                          <code class="text-dark">' . htmlspecialchars($ip) . '</code>';

            if (!empty($entry['Location']) && $entry['Location'] !== 'Unknown Location') {
                $html .= '<div class="text-muted small">' . htmlspecialchars($entry['Location']) . '</div>';
            }

            $html .= '</td>
                      <td>
                          <span class="badge badge-' . ($statusColors[$status] ?? 'info') . '">
                              ' . ucfirst($statusText) . '
                          </span>
                      </td>
                      <td>';

            if (!empty($entry['response_code'])) {
                $badgeClass = $entry['response_code'] >= 400 ? 'badge-danger' : 'badge-success';
                $html .= '<span class="badge ' . $badgeClass . '">
                            HTTP ' . $entry['response_code'] . '
                          </span>';
            }

            if (!empty($entry['execution_time_ms'])) {
                $html .= '<div class="text-muted small">
                            <i class="fas fa-stopwatch mr-1"></i>
                            ' . $entry['execution_time_ms'] . 'ms
                          </div>';
            }

            $html .= '</td>
                    </tr>';
        }

        $html .= '</tbody>
                </table>
            </div>';

        return $html;
    }
}
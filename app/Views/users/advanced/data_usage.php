<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?php
// Decode totals for display
foreach ($rows as &$row) {
    $totals = json_decode($row['totals_json'] ?? '{}', true);
    $row['total_rx_formatted'] = $totals['total_rx_formatted'] ?? '—';
    $row['total_tx_formatted'] = $totals['total_tx_formatted'] ?? '—';
}
?>
<?= view('users/advanced/_card_table', [
    'title'    => 'Data Usage',
    'subtitle' => 'Per-network mobile/WiFi data usage (current month totals)',
    'tableId'  => 'dataUsageTable',
    'columns'  => [
        ['field' => 'extracted_at',        'label' => 'Extracted',         'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'total_rx_formatted',  'label' => 'Total Rx Formatted', 'format' => 'text', 'icon' => 'fas fa-download'],
        ['field' => 'total_tx_formatted',  'label' => 'Total Tx Formatted', 'format' => 'text', 'icon' => 'fas fa-upload'],
    ],
    'secondary' => [
        ['field' => 'device_id', 'label' => 'Device ID', 'format' => 'text', 'icon' => 'fas fa-mobile-alt'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/data_usage/delete'),
]) ?>

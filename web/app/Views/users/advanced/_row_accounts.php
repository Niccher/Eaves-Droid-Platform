<?php
/** @var array $r   single row
 *  @var int   $rid row id (primary key) */
$ts = !empty($r['extracted_at'])
    ? date('Y-m-d H:i:s', (int)$r['extracted_at'])
    : '—';
?>
<tr class="accordion-toggle expandable-row" data-target="#acct-details-<?= $rid ?>">
    <td class="text-center"><i class="fas fa-chevron-down text-white chevron-icon"></i></td>

    <!-- PRIMARY COLUMNS (match order in $primaryCols) -->
    <td><?= esc($ts) ?></td>
    <td><?= esc($r['name'] ?? '—') ?></td>
    <td><?= esc($r['email'] ?? '—') ?></td>
    <td><span class="badge badge-<?= ($r['type'] ?? '') === 'google' ? 'info' : 'secondary' ?>">
            <?= esc(ucfirst($r['type'] ?? '—')) ?>
        </span></td>

    <td class="text-center">
        <button class="btn btn-sm btn-outline-danger delete-row"
                data-id="<?= $rid ?>"
                data-url="<?= base_url('advanced/software/accounts/delete') ?>"
                title="Delete this account">
            <i class="fas fa-trash"></i>
        </button>
    </td>
</tr>

<!-- SECONDARY (expanded) ROW -->
<tr class="expandable-content" style="display:none;">
    <td colspan="6" class="p-0 border-0">
        <div id="acct-details-<?= $rid ?>" style="display:none;">
            <div class="card card-body bg-light border-0 m-0 p-3">
                <table class="table table-sm table-borderless mb-0 small">
                    <tr><th>Owner ID</th><td><?= esc($r['owner_id'] ?? '—') ?></td></tr>
                    <tr><th>Device ID</th><td><?= esc($r['device_id'] ?? '—') ?></td></tr>
                    <tr><th>Token</th><td><code><?= esc($r['token'] ?? '—') ?></code></td></tr>
                    <tr><th>Last Sync</th><td><?= $ts ?></td></tr>
                    <tr><th>Raw JSON</th><td><pre class="mb-0 small"><?= esc(json_encode($r, JSON_PRETTY_PRINT)) ?></pre></td></tr>
                </table>
            </div>
        </div>
    </td>
</tr>
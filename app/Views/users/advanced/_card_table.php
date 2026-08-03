<?php
/**
 * Generic card-secondary data table for software detail pages.
 *
 * Everything is driven by column specs, so each page view is just a thin
 * wrapper that passes the definition below.
 *
 * Required variables:
 *   $title     string   page / card title
 *   $subtitle  string   short description under the title
 *   $tableId   string   unique table id, e.g. "calendarTable"
 *   $columns   array    primary columns, one entry per visible column:
 *                       ['field' => 'db_col', 'label' => 'Header',
 *                        'format' => 'text|timestamp|code|badge|yesno|json|maps|ms|bytes',
 *                        'map' => ['value' => 'badge-color'], 'default' => 'secondary',
 *                        'truncate' => N, 'jsonTitle' => 'Modal title']
 *   $secondary array    columns shown inside the expanded row (same spec format)
 *   $rows      array    data rows
 *   $pager     object   CodeIgniter pager
 *   $total     int      total row count
 *   $nav_urls  string   nav buttons (usually from the controller)
 *   $deleteUrl string   base url for the delete endpoint (no trailing id)
 *   $actions   callable optional closure fn($row) => html placed in the
 *                       actions cell (e.g. a "view details" link)
 *   $perPage   int      items per page (default 25)
 *
 * Optional:
 *   $emptyTitle / $emptyText  empty-state copy
 *   $icon                    header icon class
 */
?>
<?php
/**
 * Renders a single table cell based on a column spec.
 * Handles text, timestamps, code, badges, yes/no, JSON (modal button),
 * google-maps links, millisecond durations and byte sizes.
 */
if (!function_exists('render_adv_cell')) {
    function render_adv_cell(array $col, array $r, bool $secondary = false)
    {
    $field  = $col['field'] ?? '';
    $format = $col['format'] ?? 'text';
    $value  = $r[$field] ?? null;
    $raw    = $value;

    if ($value === null || $value === '') {
        if (isset($col['empty']) && $col['empty'] !== null && $value !== 0 && $value !== '0') {
            $value = $col['empty'];
        } else {
            $value = '—';
        }
    }

    switch ($format) {
        case 'timestamp':
            if ($raw === null || $raw === '' || $raw === '—') return '<span class="text-muted">—</span>';
            return format_timestamp_display((int)$raw);

        case 'code':
            return $value === '—' ? '<span class="text-muted">—</span>' : '<code>' . esc($value) . '</code>';

        case 'badge':
            $color = $col['map'][(string)$raw] ?? ($col['default'] ?? 'secondary');
            return '<span class="badge badge-' . $color . '">' . esc($value) . '</span>';

        case 'yesno':
            $truthy = in_array($raw, [1, '1', true, 'true', 'yes', 'Yes'], true);
            if ($raw === null || $raw === '' || $raw === '—') return '<span class="text-muted">—</span>';
            return $truthy
                ? '<span class="badge badge-success">Yes</span>'
                : '<span class="badge badge-secondary">No</span>';

        case 'json':
            $jsonTitle = $col['jsonTitle'] ?? $col['label'] ?? 'Details';
            $decoded   = is_array($raw) ? $raw : json_decode((string)($raw ?? '[]'), true);
            $count     = is_array($decoded) ? count($decoded) : 0;
            $label     = $col['jsonLabel'] ?? ('View (' . $count . ')');
            $encoded   = htmlspecialchars(json_encode($decoded, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
            return '<button type="button" class="btn btn-sm btn-outline-primary details-row" '
                 . 'data-title="' . esc($jsonTitle) . '" data-data="' . $encoded . '">'
                 . '<i class="fas fa-eye mr-1"></i>' . esc($label) . '</button>';

        case 'maps':
            if ($raw === null || $raw === '' || $raw === '—') return '<span class="text-muted">—</span>';
            $label = ($col['truncate'] ?? false) ? mb_strimwidth($raw, 0, $col['truncate'], '…') : $raw;
            return '<a href="https://www.google.com/maps/search/' . urlencode($raw) . '" target="_blank" class="text-sm text-primary">'
                 . '<i class="fas fa-map-marker-alt mr-1"></i>' . esc($label) . '</a>';

        case 'ms':
            $ms = (int)$raw;
            if (!$ms) return '<span class="text-muted">—</span>';
            $h = floor($ms / 3600000);
            $m = floor(($ms % 3600000) / 60000);
            $s = floor(($ms % 60000) / 1000);
            $out = '';
            if ($h > 0) $out .= $h . 'h ';
            if ($m > 0 || $h > 0) $out .= $m . 'm ';
            $out .= $s . 's';
            return '<span class="font-weight-bold">' . $out . '</span>'
                 . '<small class="text-muted d-block">' . number_format($ms) . ' ms</small>';

        case 'bytes':
            if ($raw === null || $raw === '' || $raw === '—') return '<span class="text-muted">—</span>';
            $b = (float)$raw;
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $u = 0;
            while ($b >= 1024 && $u < count($units) - 1) { $b /= 1024; $u++; }
            return esc(round($b, 2) . ' ' . $units[$u]);

        case 'steps':
            if ($raw === null || $raw === '' || $raw === '—') return '<span class="text-muted">—</span>';
            return '<span class="font-weight-bold">' . esc(number_format((float)$raw)) . ' Steps</span>';

        case 'percent':
            if ($raw === null || $raw === '' || $raw === '—') return '<span class="text-muted">—</span>';
            return '<span class="font-weight-bold">' . esc($raw) . '%</span>';

        case 'hours':
            if ($raw === null || $raw === '' || $raw === '—') return '<span class="text-muted">—</span>';
            $mins = (int)$raw;
            $hours = floor($mins / 60);
            $remMins = $mins % 60;
            if ($hours > 0 && $remMins > 0) {
                return '<span class="font-weight-bold">' . $hours . 'h ' . $remMins . 'm</span>';
            } elseif ($hours > 0) {
                return '<span class="font-weight-bold">' . $hours . ' Hours</span>';
            } else {
                return '<span class="font-weight-bold">' . $remMins . ' Minutes</span>';
            }

        case 'text':
        default:
            if ($col['truncate'] ?? false) {
                $value = mb_strimwidth((string)$value, 0, $col['truncate'], '…');
            }
            return esc($value);
    }
}
}
?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3">
                            <i class="<?= esc($icon ?? 'fas fa-table') ?> text-secondary mr-2"></i><?= esc($title) ?>
                        </h1>
                        <span class="badge badge-secondary border p-2 text-white">
                            <i class="fas fa-database mr-1"></i>Total: <b><?= (int)$total ?></b>
                        </span>
                    </div>
                    <?php if (!empty($subtitle)): ?>
                        <p class="text-muted mt-1 mb-0"><?= esc($subtitle) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($legend)): ?>
                        <div class="mt-2">
                            <?= $legend ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
<div class="card card-secondary shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-table mr-2"></i><?= esc($title) ?>
                                <small class="text-muted ml-2">Showing <?= count($rows) ?> of <?= (int)$total ?> entries</small>
                            </h3>
                            <div class="card-tools ml-auto">
                                <button type="button"
                                        class="btn btn-tool btn-sm"
                                        data-card-widget="collapse"
                                        data-toggle="tooltip"
                                        title="Collapse / Expand">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="border-bottom px-3 py-2">
                            <div class="input-group input-group-sm" style="max-width:350px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" class="form-control table-search" placeholder="Search..." data-table="<?= esc($tableId) ?>">
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 table-sortable" id="<?= esc($tableId) ?>">
                                    <thead class="thead-light">
                                    <tr>
                                        <?php if (empty($noExpand)): ?>
                                            <th style="width:40px;" class="text-center"><i class="fas fa-chevron-down text-muted"></i></th>
                                        <?php endif; ?>
                                        <?php foreach ($columns as $col): ?>
                                            <th class="<?= esc($col['headerClass'] ?? '') ?>">
                                                <?php if (!empty($col['icon'])): ?>
                                                    <i class="<?= esc($col['icon']) ?> mr-1"></i>
                                                <?php endif; ?>
                                                <?= esc($col['label']) ?>
                                            </th>
                                        <?php endforeach; ?>
                                        <th class="text-center" style="width:40px;"><i class="fas fa-cogs text-muted"></i></th>
                                    </tr>
                                    </thead>
                                        <tbody>
                                        <?php if (empty($rows)): ?>
<tr>
                                                    <td colspan="<?= count($columns) + (empty($noExpand) ? 2 : 1) ?>" class="text-center py-5">
                                                    <div class="empty-state">
                                                        <i class="<?= esc($emptyIcon ?? 'fas fa-inbox') ?> fa-3x text-muted mb-3"></i>
                                                        <h4><?= esc($emptyTitle ?? 'No data') ?></h4>
                                                        <p class="text-muted"><?= esc($emptyText ?? 'Data will appear here once extracted.') ?></p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: foreach ($rows as $i => $r): ?>
                                            <?php
                                                $rid = $r['id'] ?? 0;
                                                $targetId = $tableId . '-details-' . ($rid ?: $i);
                                            ?>
                                            <?php if (!empty($noExpand)): ?>
                                                <tr>
                                                    <?php foreach ($columns as $col): ?>
                                                        <td><?= render_adv_cell($col, $r) ?></td>
                                                    <?php endforeach; ?>
                                                    <td class="text-center">
                                                        <?php if (isset($actions) && is_callable($actions)): ?>
                                                            <?= $actions($r) ?>
                                                        <?php endif; ?>
                                                        <?php if (!empty($deleteUrl)): ?>
                                                            <button class="btn btn-sm btn-outline-danger delete-row"
                                                                    data-id="<?= (int)$rid ?>"
                                                                    data-url="<?= esc($deleteUrl) ?>"
                                                                    title="Delete this row">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <tr class="accordion-toggle expandable-row" data-target="#<?= esc($targetId) ?>">
                                                    <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                                    <?php foreach ($columns as $col): ?>
                                                        <td><?= render_adv_cell($col, $r) ?></td>
                                                    <?php endforeach; ?>
                                                    <td class="text-center">
                                                        <?php if (isset($actions) && is_callable($actions)): ?>
                                                            <?= $actions($r) ?>
                                                        <?php endif; ?>
                                                        <?php if (!empty($deleteUrl)): ?>
                                                            <button class="btn btn-sm btn-outline-danger delete-row"
                                                                    data-id="<?= (int)$rid ?>"
                                                                    data-url="<?= esc($deleteUrl) ?>"
                                                                    title="Delete this row">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <tr class="expandable-content" style="display:none;">
                                                    <td colspan="<?= count($columns) + 2 ?>" class="p-0 border-0">
                                                        <div id="<?= esc($targetId) ?>" style="display:none;">
                                                            <div class="card card-body bg-light border-0 m-0 p-3">
                                                                <?php if (!empty($secondary)): ?>
                                                                    <table class="table table-sm table-borderless small mb-0">
                                                                        <?php foreach ($secondary as $col): ?>
                                                                            <tr>
                                                                                <th class="text-muted" style="width:30%;vertical-align:top;"><?= esc($col['label']) ?></th>
                                                                                <td><?= render_adv_cell($col, $r, true) ?></td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </table>
                                                                <?php else: ?>
                                                                    <p class="text-muted small mb-0">No additional details.</p>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Error Log: <?= htmlspecialchars($filename) ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/logs') ?>">Logs</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/logs/errors') ?>">Errors</a></li>
                        <li class="breadcrumb-item active"><?= htmlspecialchars($filename) ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <span class="badge badge-info"><?= number_format($total_lines) ?> lines</span>
                    <div class="card-tools">
                        <a href="<?= base_url('admin/logs/errors') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                    </div>
                </div>
                <div class="card-body p-0" style="max-height: 70vh; overflow-y: auto;">
                    <pre class="m-0 p-3" style="font-size: 0.75rem; line-height: 1.4; background: #1e1e1e; color: #d4d4d4;"><?php
                        foreach ($lines as $line) {
                            $trimmed = trim($line);
                            if ($trimmed === '') continue;
                            $class = '';
                            if (stripos($trimmed, 'ERROR') !== false || stripos($trimmed, 'CRITICAL') !== false) $class = 'color: #f44747;';
                            elseif (stripos($trimmed, 'WARNING') !== false) $class = 'color: #dcdcaa;';
                            elseif (stripos($trimmed, 'INFO') !== false) $class = 'color: #6a9955;';
                            elseif (stripos($trimmed, 'DEBUG') !== false) $class = 'color: #569cd6;';
                            echo '<span style="' . $class . '">' . htmlspecialchars($trimmed) . "</span>\n";
                        }
                    ?></pre>
                </div>
            </div>
        </div>
    </section>
</div>

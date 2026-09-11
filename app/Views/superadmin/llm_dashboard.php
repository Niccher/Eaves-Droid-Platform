<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">LLM Management Dashboard</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <?php if(session()->getFlashdata('success')): ?>
                <div class="alert alert-success">
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>
            
            <?php if(session()->getFlashdata('error') || isset($ml_error)): ?>
                <div class="alert alert-danger">
                    <?= session()->getFlashdata('error') ?? $ml_error ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Available Models Table -->
                <div class="col-md-8">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-brain"></i> Local .gguf Models</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Filename</th>
                                        <th>Size (MB)</th>
                                        <th>Status</th>
                                        <th style="width: 150px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($models)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No models found or ML backend offline.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($models as $m): ?>
                                            <tr>
                                                <td><?= esc($m['filename']) ?></td>
                                                <td><?= esc($m['size_mb']) ?></td>
                                                <td>
                                                    <?php if($m['is_active']): ?>
                                                        <span class="badge badge-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Standby</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if(!$m['is_active']): ?>
                                                        <form action="<?= route_to('superadmin-llm-set-active') ?>" method="post">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="filename" value="<?= esc($m['filename']) ?>">
                                                            <button type="submit" class="btn btn-sm btn-primary">Set Active</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Downloader Form -->
                <div class="col-md-4">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-download"></i> Download New Model</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted text-sm">Download large .gguf weights directly from HuggingFace to the ML container.</p>
                            <form action="<?= route_to('superadmin-llm-download') ?>" method="post">
                                <?= csrf_field() ?>
                                <div class="form-group">
                                    <label for="url">HuggingFace Direct URL</label>
                                    <input type="url" class="form-control" name="url" id="url" placeholder="https://huggingface.co/..." required>
                                </div>
                                <div class="form-group">
                                    <label for="target_filename">Target Filename (.gguf)</label>
                                    <input type="text" class="form-control" name="target_filename" id="target_filename" placeholder="Llama-3-8B.Q4.gguf" required>
                                </div>
                                <button type="submit" class="btn btn-success btn-block">Start Download Task</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

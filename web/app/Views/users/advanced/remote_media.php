<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
    $actions = static function ($r) {
        $type = $r['media_type'] ?? '';
        $file = rawurlencode((string) ($r['stored_filename'] ?? ''));
        $serve = base_url('advanced/media/serve/' . $file);
        $title = htmlspecialchars((string) ($r['original_filename'] ?? ''), ENT_QUOTES);
        $html = '';
        if ($type === 'image') {
            $html .= '<button class="btn btn-sm btn-outline-info view-media" data-type="image" data-url="' . esc($serve) . '" data-title="' . $title . '"><i class="fas fa-eye mr-1"></i> View</button>';
        } elseif ($type === 'audio') {
            $html .= '<button class="btn btn-sm btn-outline-danger view-media" data-type="audio" data-url="' . esc($serve) . '" data-title="' . $title . '"><i class="fas fa-play mr-1"></i> Play</button>';
        } else {
            $html .= '<a href="' . esc($serve) . '" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fas fa-file-alt mr-1"></i> Open</a>';
        }
        $html .= ' <a href="' . esc($serve) . '" class="btn btn-sm btn-outline-secondary" download title="Download"><i class="fas fa-download"></i></a>';
        $html .= ' <button class="btn btn-sm btn-outline-danger delete-media" data-id="' . (int) ($r['id'] ?? 0) . '" data-filename="' . $title . '" title="Delete"><i class="fas fa-trash"></i></button>';
        return $html;
    };
?>
<?= view('users/advanced/_card_table', [
    'title'    => 'Remote Media Forensic',
    'subtitle' => 'Multimedia files captured remotely via device commands',
    'icon'     => 'fas fa-photo-video',
    'tableId'  => 'remoteMediaTable',
    'columns'  => [
        ['field' => 'original_filename', 'label' => 'File Info',       'format' => 'text'],
        ['field' => 'mime_type',         'label' => 'MIME',            'format' => 'code'],
        ['field' => 'media_type',        'label' => 'Type',            'format' => 'badge', 'map' => ['image' => 'info', 'audio' => 'danger', 'file' => 'success'], 'default' => 'secondary'],
        ['field' => 'file_size',         'label' => 'Size',            'format' => 'bytes'],
        ['field' => 'created_at_display','label' => 'Captured At',     'format' => 'text'],
    ],
    'secondary' => [
        ['field' => 'stored_filename', 'label' => 'Stored Filename', 'format' => 'code'],
        ['field' => 'device_id',       'label' => 'Device ID',       'format' => 'text'],
    ],
    'rows'     => $rows,
    'pager'    => $pager,
    'total'    => $total,
    'nav_urls' => $nav_urls ?? '',
    'actions'  => $actions,
]) ?>

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
    $('.view-media').on('click', function(e) {
        e.stopPropagation();
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
    $('.delete-media').on('click', function(e) {
        e.stopPropagation();
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

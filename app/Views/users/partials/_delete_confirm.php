<script>
var CSRF_TOKEN_NAME = '<?= csrf_token() ?>';
var CSRF_TOKEN_HASH = '<?= csrf_hash() ?>';
document.addEventListener('DOMContentLoaded', function() {
    function deleteSectionTitle(btn) {
        var t = btn ? btn.getAttribute('data-delete-title') : '';
        if (!t) {
            var h1 = document.querySelector('.content-header h1');
            if (h1) {
                var txt = (h1.textContent || '').replace(/\s+/g, ' ').trim();
                if (txt) t = txt;
            }
        }
        return t || 'this entry';
    }
    document.querySelectorAll('.delete-row').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var id = this.getAttribute('data-id');
            var url = this.getAttribute('data-url');
            if (!id || !url) return;
            var section = deleteSectionTitle(this);
            if (typeof Swal === 'undefined') return;
            Swal.fire({
                title: 'Delete this Entry?',
                html: 'This will permanently delete the <strong>"' + section + '"</strong> entry and all of its associated data.'
                   + '<br><span class="text-danger mt-1 d-inline-block"><i class="fas fa-exclamation-triangle mr-1"></i>This action cannot be undone.</span>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash"></i> Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (!result.isConfirmed) return;
                var postData = new URLSearchParams();
                postData.append(CSRF_TOKEN_NAME, CSRF_TOKEN_HASH);
                fetch(url + '/' + id, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: postData
                }).then(function(r) { return r.json(); }).then(function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', 'The "' + section + '" entry has been deleted.', 'success').then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', response.message || 'Failed to delete.', 'error');
                    }
                }).catch(function() {
                    Swal.fire('Error!', 'Failed to delete entry.', 'error');
                });
            });
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-row').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var id = this.getAttribute('data-id');
            var url = this.getAttribute('data-url');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete this entry?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash"></i> Delete'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        fetch(url + '/' + id, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        }).then(function(r) { return r.json(); }).then(function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', 'Entry has been deleted.', 'success').then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error!', response.message || 'Failed to delete.', 'error');
                            }
                        }).catch(function() {
                            Swal.fire('Error!', 'Failed to delete entry.', 'error');
                        });
                    }
                });
            }
        });
    });
});
</script>
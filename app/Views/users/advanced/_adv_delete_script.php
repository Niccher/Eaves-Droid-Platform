<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete row handler
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

    // Details modal handler
    document.querySelectorAll('.details-row').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var data = JSON.parse(this.getAttribute('data-data') || '{}');
            var title = this.getAttribute('data-title') || 'Details';
            showDetailsModal(title, data);
        });
    });

    function showDetailsModal(title, data) {
        var content = buildDetailsContent(data);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '<i class="fas fa-eye mr-2"></i>' + title,
                html: content,
                width: '90%',
                maxWidth: '1000px',
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    popup: 'modal-xl',
                    htmlContainer: 'p-0'
                },
                didOpen: function() {
                    // Add copy functionality for JSON blocks
                    document.querySelectorAll('.copy-json').forEach(function(btn) {
                        btn.addEventListener('click', function() {
                            var target = this.getAttribute('data-target');
                            var text = document.getElementById(target).textContent;
                            navigator.clipboard.writeText(text).then(function() {
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Copied!', showConfirmButton: false, timer: 1500 });
                                }
                            });
                        });
                    });
                }
            });
        }

    function buildDetailsContent(data) {
        if (!data || Object.keys(data).length === 0) {
            return '<div class="p-4 text-center text-muted"><i class="fas fa-info-circle fa-2x mb-2"></i><p>No details available</p></div>';
        }

        var html = '<div class="details-grid p-3">';
        var priorityKeys = ['extracted_at'];
        var excludedKeys = ['created_at', 'updated_at', 'id', 'owner_id', 'device_id', 'file_record_id'];
        
        // Show priority fields first
        priorityKeys.forEach(function(key) {
            if (data[key] !== undefined && data[key] !== null && data[key] !== '') {
                var value = data[key];
                if (key.includes('_at') && typeof value === 'number') {
                    value = formatTimestamp(value);
                }
                html += buildDetailCard(key.replace(/_/g, ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); }), value, 'info');
            }
        });

        // Show remaining fields
        Object.keys(data).forEach(function(key) {
            if (priorityKeys.includes(key) || excludedKeys.includes(key)) return;
            var value = data[key];
            if (value === undefined || value === null) return;
            if (value === '' && type !== 'number' && type !== 'boolean') return;

            var icon = 'info';
            var type = typeof value;
            if (type === 'object' && value !== null) {
                if (Array.isArray(value)) {
                    icon = 'list';
                } else {
                    icon = 'code';
                }
            } else if (type === 'number') {
                icon = 'hashtag';
            } else if (type === 'boolean') {
                icon = 'toggle-on';
            }

            var displayValue = '';
            if (type === 'object' && value !== null) {
                var jsonStr = JSON.stringify(value, null, 2);
                var id = 'json-' + key.replace(/[^a-zA-Z0-9]/g, '-');
                displayValue = '<pre class="json-pre" id="' + id + '">' + escapeHtml(jsonStr) + '</pre>';
                displayValue += '<button class="btn btn-sm btn-outline-secondary copy-json mt-2" data-target="' + id + '"><i class="fas fa-copy mr-1"></i>Copy JSON</button>';
            } else if (type === 'boolean') {
                displayValue = value ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>';
            } else if (key.includes('_at') && type === 'number') {
                displayValue = formatTimestamp(value);
            } else {
                displayValue = escapeHtml(String(value));
            }

            var label = key.replace(/_/g, ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); });
            html += buildDetailCard(label, displayValue, icon);
        });

        html += '</div>';
        return html;
    }

    function buildDetailCard(label, value, iconType) {
        var iconMap = {
            info: 'fas fa-info-circle text-primary',
            warning: 'fas fa-exclamation-triangle text-warning',
            danger: 'fas fa-times-circle text-danger',
            success: 'fas fa-check-circle text-success',
            list: 'fas fa-list-ul text-info',
            code: 'fas fa-code text-secondary',
            hashtag: 'fas fa-hashtag text-primary',
            'toggle-on': 'fas fa-toggle-on text-success'
        };
        var icon = iconMap[iconType] || 'fas fa-info-circle text-primary';
        return '<div class="detail-card">' +
            '<div class="detail-icon"><i class="' + icon + '"></i></div>' +
            '<div class="detail-label">' + escapeHtml(label) + '</div>' +
            '<div class="detail-value">' + value + '</div>' +
        '</div>';
    }

    function formatTimestamp(ts) {
        if (!ts) return '—';
        var date = new Date(ts > 1e12 ? ts : ts * 1000);
        return date.toLocaleString();
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
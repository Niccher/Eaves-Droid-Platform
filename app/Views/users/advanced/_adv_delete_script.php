<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================================
    // GLOBAL TABLE SEARCH HANDLER
    // ============================================================
    document.querySelectorAll('.table-search').forEach(function(input) {
        input.addEventListener('keyup', function() {
            var keyword = this.value.toLowerCase();
            var tableId = this.getAttribute('data-table') || 'table-sortable';
            var table = document.getElementById(tableId);
            if (!table) return;
            table.querySelectorAll('tbody tr.accordion-toggle').forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().indexOf(keyword) > -1 ? '' : 'none';
            });
        });
    });
    // ============================================================
    // EXPANDABLE ROW HANDLER (like location_all.php)
    // ============================================================
    document.querySelectorAll('.accordion-toggle.expandable-row').forEach(function(row) {
        row.addEventListener('click', function(e) {
            // Don't trigger if clicking on a button/link inside
            if (e.target.closest('button, a')) return;

            const targetId = this.getAttribute('data-target');
            const target = document.querySelector(targetId);
            const chevron = this.querySelector('.chevron-icon');
            const expandableRow = this.closest('tr').nextElementSibling;

            if (!target || !expandableRow) return;

            const isCurrentlyOpen = expandableRow.style.display === 'table-row';

            // Close all other accordion items
            document.querySelectorAll('.accordion-toggle.expandable-row').forEach(function(other) {
                if (other !== row) {
                    const otherTarget = document.querySelector(other.getAttribute('data-target'));
                    const otherChevron = other.querySelector('.chevron-icon');
                    const otherRow = other.closest('tr').nextElementSibling;
                    if (otherTarget) otherTarget.style.display = 'none';
                    if (otherRow) otherRow.style.display = 'none';
                    if (otherChevron) {
                        otherChevron.classList.remove('fa-chevron-up');
                        otherChevron.classList.add('fa-chevron-down');
                    }
                }
            });

            // Toggle current item
            if (!isCurrentlyOpen) {
                target.style.display = 'block';
                expandableRow.style.display = 'table-row';
                if (chevron) {
                    chevron.classList.remove('fa-chevron-down');
                    chevron.classList.add('fa-chevron-up');
                }
            } else {
                target.style.display = 'none';
                expandableRow.style.display = 'none';
                if (chevron) {
                    chevron.classList.remove('fa-chevron-up');
                    chevron.classList.add('fa-chevron-down');
                }
            }
        });
    });

    // ============================================================
    // DELETE ROW HANDLER
    // ============================================================
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

    // ============================================================
    // DETAILS MODAL HANDLER (for .details-row buttons)
    // ============================================================
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
    }

    function buildDetailsContent(data) {
        // Arrays (codecs, devices, interfaces, links, displays) render as item cards
        if (Array.isArray(data)) {
            return buildListContent(data);
        }
        if (!data || Object.keys(data).length === 0) {
            return '<div class="p-4 text-center text-muted"><i class="fas fa-info-circle fa-2x mb-2"></i><p>No details available</p></div>';
        }

        var html = '<div class="details-grid p-3">';
        var priorityKeys = ['extracted_at'];
        var excludedKeys = ['created_at', 'updated_at', 'id', 'owner_id', 'device_id', 'file_record_id'];
        
        // Show priority fields first
        priorityKeys.forEach(function(key) {
            if (data[key] !== undefined && data[key] !== null && data[key] !== '') {
                html += buildDetailCard(key.replace(/_/g, ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); }), formatValue(data[key], key), 'info');
            }
        });

        // Show remaining fields
        Object.keys(data).forEach(function(key) {
            if (priorityKeys.includes(key) || excludedKeys.includes(key)) return;
            var value = data[key];
            if (value === undefined || value === null) return;
            if (value === '' && typeof value !== 'number' && typeof value !== 'boolean') return;

            var icon = 'info';
            var type = typeof value;
            if (type === 'object' && value !== null) {
                icon = Array.isArray(value) ? 'list' : 'code';
            } else if (type === 'number') {
                icon = 'hashtag';
            } else if (type === 'boolean') {
                icon = 'toggle-on';
            }

            var label = key.replace(/_/g, ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); });
            html += buildDetailCard(label, formatValue(value, key), icon);
        });

        html += '</div>';
        return html;
    }

    // Renders each element of an array as its own well-formatted card
    function buildListContent(items) {
        if (!items || items.length === 0) {
            return '<div class="p-4 text-center text-muted"><i class="fas fa-info-circle fa-2x mb-2"></i><p>No data available</p></div>';
        }
        var html = '<div class="details-grid p-3">';
        items.forEach(function(item, idx) {
            var itemObj = (typeof item === 'object' && item !== null) ? item : { 'value': item };
            html += '<div class="detail-card" style="grid-column: span 2;">';
            html += '<div class="detail-icon"><i class="fas fa-list text-info"></i></div>';
            html += '<div class="detail-label">Item ' + (idx + 1) + '</div>';
            html += '<div class="detail-value" style="text-align:left;font-weight:400;">';
            html += '<table class="table table-sm table-borderless small mb-0">';
            Object.keys(itemObj).forEach(function(key) {
                var value = itemObj[key];
                if (value === undefined || value === null) return;
                if (value === '' && typeof value !== 'number' && typeof value !== 'boolean') return;
                var label = key.replace(/_/g, ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); });
                html += '<tr><th class="text-muted" style="width:38%;vertical-align:top;">' + escapeHtml(label) + '</th><td>' + formatValue(value, key) + '</td></tr>';
            });
            html += '</table></div></div>';
        });
        html += '</div>';
        return html;
    }

    function formatValue(value, key) {
        if (value === undefined || value === null) return '<span class="text-muted">—</span>';
        var type = typeof value;
        if (type === 'object') {
            var jsonStr = JSON.stringify(value, null, 2);
            var id = 'json-' + key.replace(/[^a-zA-Z0-9]/g, '-') + '-' + Math.random().toString(36).slice(2, 7);
            var out = '<pre class="json-pre mb-0" id="' + id + '">' + escapeHtml(jsonStr) + '</pre>';
            out += '<button class="btn btn-sm btn-outline-secondary copy-json mt-1" data-target="' + id + '"><i class="fas fa-copy mr-1"></i>Copy JSON</button>';
            return out;
        }
        if (type === 'boolean') return value ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>';
        if (key.includes('_at') && type === 'number') return formatTimestamp(value);
        return escapeHtml(String(value));
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
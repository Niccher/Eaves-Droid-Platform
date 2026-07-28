<style>
    .adv-avatar {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: rgba(0,0,0,0.04);
        font-size: 1.1rem;
    }
    .empty-state { padding: 40px 0; }
    .table td { vertical-align: middle; }
    .badge { font-weight: 500; border-radius: 4px; padding: 4px 8px; }
    .card-title small { font-weight: normal; opacity: 0.7; }
    
    /* Responsive button group for tabs */
    @media (max-width: 991.98px) {
        .float-right { float: none !important; margin-top: 15px; }
        .btn-group-sm > .btn, .btn-sm { padding: .25rem .4rem; font-size: .75rem; }
    }

    /* Navigation Refinements */
    .btn-sm .badge {
        font-size: 0.75rem;
        padding: 0.25em 0.6em;
        font-weight: 500;
        border-radius: 10px;
    }
    
    .btn-outline-primary .badge-primary {
        background-color: #007bff;
        color: #fff;
    }
    
    .btn-primary .badge-light {
        color: #007bff;
        background-color: #fff;
    }

    .d-flex.justify-content-end.flex-wrap {
        gap: 8px !important;
    }

    /* Details Modal Styles */
    .modal-xl { max-width: 90%; }
    .details-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }
    .detail-card { background: #f8f9fa; border-radius: 8px; padding: 1rem; border-left: 4px solid #007bff; }
    .detail-card.warning { border-left-color: #ffc107; }
    .detail-card.danger { border-left-color: #dc3545; }
    .detail-card.success { border-left-color: #28a745; }
    .detail-card.info { border-left-color: #17a2b8; }
    .detail-card .detail-icon { font-size: 1.5rem; opacity: 0.7; margin-bottom: 0.5rem; }
    .detail-card .detail-label { font-size: 0.75rem; text-transform: uppercase; color: #6c757d; margin-bottom: 0.25rem; }
    .detail-card .detail-value { font-size: 1rem; font-weight: 500; color: #212529; word-break: break-word; }
    .detail-card .detail-value.empty { color: #adb5bd; font-style: italic; }
    .modal-header { background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); color: white; }
    .btn-details { transition: all 0.2s; }
    .btn-details:hover { transform: scale(1.1); background: #e3f2fd !important; color: #1976d2 !important; }
    .btn-outline-primary.details-row { border-color: #007bff; color: #007bff; }
    .btn-outline-primary.details-row:hover { background: #007bff; color: #fff; }
    .action-btns { display: flex; gap: 4px; justify-content: center; align-items: center; }
    .action-btns .btn { margin: 0 2px; }
    .json-pre { max-height: 300px; overflow: auto; background: #f4f4f4; padding: 1rem; border-radius: 4px; font-size: 0.75rem; white-space: pre-wrap; word-wrap: break-word; }
</style>

<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip();
});
</script>

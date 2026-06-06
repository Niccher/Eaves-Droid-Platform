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

</style>

<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip();
});
</script>

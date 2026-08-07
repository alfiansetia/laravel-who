<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">

<style>
    .table-modern {
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-modern thead th {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        padding: 0.35rem 0.5rem;
        white-space: nowrap;
    }

    .table-modern tbody td {
        padding: 0.3rem 0.5rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.8rem;
    }

    .table-modern tbody tr {
        transition: background 0.15s;
        cursor: pointer;
    }

    .table-modern tbody tr:hover {
        background: #f8fafc;
    }

    .btn-action {
        width: 26px;
        height: 26px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        padding: 0;
        font-size: 0.7rem;
        transition: all 0.15s;
    }

    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .toast-copy {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9999;
        padding: 10px 20px;
        background: #1e293b;
        color: #fff;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease;
    }

    .toast-copy.show {
        opacity: 1;
        transform: translateY(0);
    }

    /* Pagination */
    .pagination-modern {
        display: flex;
        align-items: center;
        gap: 4px;
        flex-wrap: wrap;
    }

    .pagination-modern .page-btn {
        min-width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s;
    }

    .pagination-modern .page-btn:hover:not(:disabled):not(.active) {
        border-color: #6366f1;
        color: #6366f1;
        background: #eef2ff;
    }

    .pagination-modern .page-btn.active {
        background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
        color: #fff;
        border-color: transparent;
    }

    .pagination-modern .page-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .per-page-select {
        width: auto;
        min-width: 70px;
        padding: 2px 8px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        font-size: 0.8rem;
        color: #64748b;
        background: #fff;
    }

    /* Sistem badge */
    .badge-sistem {
        background: #e0e7ff;
        color: #4338ca;
        font-size: 0.65rem;
        padding: 1px 5px;
        border-radius: 4px;
        font-weight: 700;
        margin-left: 4px;
    }
</style>

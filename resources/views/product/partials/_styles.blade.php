<style>
    :root {
        --product-gradient: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
        --product-accent: #6366f1;
    }

    /* ── Modern Table ─────────────────────────────────── */
    .table-modern {
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-modern thead th {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        padding: 0.5rem 0.75rem;
        white-space: nowrap;
    }

    .table-modern tbody td {
        padding: 0.4rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.82rem;
    }

    .table-modern tbody tr {
        transition: background 0.15s;
        cursor: pointer;
    }

    .table-modern tbody tr:hover {
        background: #f0f9ff;
    }

    /* ── Action Buttons ───────────────────────────────── */
    .btn-action {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        padding: 0;
        font-size: 0.72rem;
        transition: all 0.15s;
    }

    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* ── Toast ────────────────────────────────────────── */
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

    /* ── AKL Exp Badge ────────────────────────────────── */
    .badge-exp-expired {
        background: #fee2e2;
        color: #dc2626;
        font-weight: 600;
    }

    .badge-exp-valid {
        background: #d1fae5;
        color: #059669;
        font-weight: 600;
    }

    /* ── DataTables Footer ────────────────────────────── */
    .product-footer {
        padding: 0.75rem 1rem;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 0 0 12px 12px;
    }

    .product-footer .dataTables_info {
        font-size: 0.82rem;
        color: #64748b;
    }

    .product-footer .dataTables_length {
        font-size: 0.82rem;
    }

    .product-footer .dataTables_length select {
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        padding: 0.25rem 0.5rem;
        font-size: 0.82rem;
    }

    .product-footer .dataTables_paginate {
        font-size: 0.82rem;
    }

    .product-footer .dataTables_paginate .paginate_button {
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        margin: 0 2px;
        padding: 0.25rem 0.6rem;
    }

    .product-footer .dataTables_paginate .paginate_button.current {
        background: var(--product-gradient);
        color: #fff !important;
        border-color: transparent;
    }

    .product-footer .dataTables_paginate .paginate_button:hover {
        background: #eef2ff;
        border-color: var(--product-accent);
        color: var(--product-accent) !important;
    }
</style>

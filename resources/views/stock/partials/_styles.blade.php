<style>
    :root {
        --stock-gradient: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%);
        --stock-accent: #0ea5e9;
    }

    /* ── Filter Card ──────────────────────────────────── */
    .filter-card {
        border-radius: 12px;
        border: none;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        margin-bottom: 1rem;
    }

    .filter-card .card-body {
        padding: 1rem 1.25rem;
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

    /* ── Toolbar ──────────────────────────────────────── */
    .stock-toolbar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .stock-toolbar .location-select {
        min-width: 220px;
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

    /* ── Qty Badge ────────────────────────────────────── */
    .qty-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 0.78rem;
        font-weight: 600;
        background: #dbeafe;
        color: #1d4ed8;
    }

    .qty-badge.zero {
        background: #fee2e2;
        color: #dc2626;
    }

    /* ── AKL Badge ────────────────────────────────────── */
    .akl-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .akl-badge.has-akl {
        background: #d1fae5;
        color: #059669;
    }

    .akl-badge.no-akl {
        background: #f1f5f9;
        color: #94a3b8;
    }

    /* ── Lot Modal ────────────────────────────────────── */
    .modal-modern .modal-content {
        border: none;
        border-radius: 16px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .modal-modern .modal-header {
        background: var(--stock-gradient);
        color: #fff;
        border-radius: 16px 16px 0 0;
        padding: 1rem 1.5rem;
    }

    .modal-modern .modal-header .close {
        color: #fff;
        opacity: 0.8;
    }

    .modal-modern .modal-header .close:hover {
        opacity: 1;
    }

    .modal-modern .modal-body {
        padding: 1.25rem;
    }

    .modal-modern .modal-footer {
        border-top: 1px solid #f1f5f9;
        padding: 0.75rem 1.25rem;
    }

    /* ── Lot Table inside Modal ───────────────────────── */
    .lot-table thead th {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        padding: 0.4rem 0.75rem;
    }

    .lot-table tbody td {
        padding: 0.35rem 0.75rem;
        font-size: 0.82rem;
        border-bottom: 1px solid #f1f5f9;
    }

    /* ── Copy Group ───────────────────────────────────── */
    .copy-group {
        background: #f8fafc;
        border-radius: 10px;
        padding: 0.75rem;
        margin-top: 0.75rem;
    }

    .copy-group label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 4px;
    }

    .copy-group textarea {
        font-size: 0.8rem;
        resize: none;
    }

    /* ── Select2 override ─────────────────────────────── */
    .select2-container--bootstrap4 .select2-selection--multiple {
        border-color: #e2e8f0;
        min-height: 32px;
        font-size: 0.85rem;
    }

    .select2-container--bootstrap4 .select2-results__option--highlighted {
        background: var(--stock-accent) !important;
    }

    /* ── Stock Footer (DataTables native lip) ────────── */
    .stock-footer {
        border-top: 1px solid #f1f5f9;
        background: #fff;
        border-radius: 0 0 12px 12px;
        min-height: 48px;
    }

    /* ── DataTable Length Select ──────────────────────── */
    .stock-footer .dataTables_length {
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
    }

    .stock-footer .dataTables_length label {
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
        font-size: 0.85rem;
        color: #64748b;
    }

    .stock-footer .dataTables_length select {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 0.35rem 0.75rem;
        font-size: 0.85rem;
        color: #64748b;
        background: #fff;
        cursor: pointer;
        transition: border-color 0.15s;
    }

    .stock-footer .dataTables_length select:focus {
        border-color: var(--stock-accent);
        outline: none;
    }

    /* ── DataTable Info ───────────────────────────────── */
    .stock-footer .dataTables_info {
        font-size: 0.85rem;
        color: #64748b;
        padding: 0;
        margin: 0;
    }

    /* ── Pagination (match izin_edar pagination-modern) ── */
    .stock-footer .dataTables_paginate {
        display: flex;
        align-items: center;
        gap: 4px;
        margin: 0;
    }

    .stock-footer .dataTables_paginate .paginate_button {
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
        padding: 0 8px;
        margin: 0 !important;
        box-shadow: none !important;
    }

    .stock-footer .dataTables_paginate .paginate_button:hover {
        background: #eef2ff;
        border-color: #6366f1;
        color: #6366f1;
    }

    .stock-footer .dataTables_paginate .paginate_button.current {
        background: var(--stock-gradient);
        color: #fff;
        border-color: transparent;
    }

    .stock-footer .dataTables_paginate .paginate_button.current:hover {
        background: var(--stock-gradient);
        color: #fff;
    }

    .stock-footer .dataTables_paginate .paginate_button.disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .stock-footer .dataTables_paginate .paginate_button.disabled:hover {
        background: #fff;
        border-color: #e2e8f0;
        color: #64748b;
    }
</style>

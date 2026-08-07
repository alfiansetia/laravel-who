<style>
    :root {
        --pltbb-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --pltbb-accent: #f59e0b;
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
    }

    .table-modern tbody tr:hover {
        background: #fffbeb;
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

    /* ── Row Incomplete Warning ───────────────────────── */
    .row-incomplete {
        background: #fef2f2 !important;
        color: #dc2626;
    }

    .row-incomplete:hover {
        background: #fee2e2 !important;
    }

    /* ── PLTB Badge ───────────────────────────────────── */
    .pltbb-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 0.78rem;
        font-weight: 600;
        background: #dbeafe;
        color: #1d4ed8;
    }

    .pltbb-badge.zero {
        background: #fee2e2;
        color: #dc2626;
    }

    .pltbb-badge.empty {
        background: #f1f5f9;
        color: #94a3b8;
    }

    /* ── Compare Modal ────────────────────────────────── */
    .modal-modern .modal-content {
        border: none;
        border-radius: 16px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .modal-modern .modal-header {
        background: var(--pltbb-gradient);
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

    /* ── Compare Group ────────────────────────────────── */
    .compare-group {
        background: #f8fafc;
        border-radius: 10px;
        padding: 1rem;
    }

    .compare-group h6 {
        font-size: 0.85rem;
        font-weight: 700;
        color: #334155;
        margin-bottom: 0.75rem;
    }

    .compare-table {
        width: 100%;
        font-size: 0.82rem;
    }

    .compare-table td {
        padding: 0.35rem 0.5rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .compare-table td:first-child {
        font-weight: 600;
        color: #64748b;
        width: 60px;
    }

    .compare-table td:last-child {
        color: #1e293b;
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

    /* ── Select2 override ─────────────────────────────── */
    .select2-container--bootstrap4 .select2-selection--multiple {
        border-color: #e2e8f0;
        min-height: 32px;
        font-size: 0.85rem;
    }

    .select2-container--bootstrap4 .select2-results__option--highlighted {
        background: var(--pltbb-accent) !important;
    }

    /* ── Footer (DataTables native lip) ───────────────── */
    .pltbb-footer {
        border-top: 1px solid #f1f5f9;
        background: #fff;
        border-radius: 0 0 12px 12px;
        min-height: 48px;
    }

    .pltbb-footer .dataTables_length {
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
    }

    .pltbb-footer .dataTables_length label {
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
        font-size: 0.85rem;
        color: #64748b;
    }

    .pltbb-footer .dataTables_length select {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 0.35rem 0.75rem;
        font-size: 0.85rem;
        color: #64748b;
        background: #fff;
        cursor: pointer;
        transition: border-color 0.15s;
    }

    .pltbb-footer .dataTables_length select:focus {
        border-color: var(--pltbb-accent);
        outline: none;
    }

    .pltbb-footer .dataTables_info {
        font-size: 0.85rem;
        color: #64748b;
        padding: 0;
        margin: 0;
    }

    .pltbb-footer .dataTables_paginate {
        display: flex !important;
        align-items: center;
        gap: 4px;
        margin: 0;
        flex-wrap: nowrap;
        overflow-x: auto;
    }

    .pltbb-footer .dataTables_paginate .paginate_button {
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
        flex-shrink: 0;
        white-space: nowrap;
    }

    .pltbb-footer .dataTables_paginate .paginate_button:hover {
        background: #fef3c7;
        border-color: #f59e0b;
        color: #d97706;
    }

    .pltbb-footer .dataTables_paginate .paginate_button.current {
        background: var(--pltbb-gradient);
        color: #fff;
        border-color: transparent;
    }

    .pltbb-footer .dataTables_paginate .paginate_button.current:hover {
        background: var(--pltbb-gradient);
        color: #fff;
    }

    .pltbb-footer .dataTables_paginate .paginate_button.disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .pltbb-footer .dataTables_paginate .paginate_button.disabled:hover {
        background: #fff;
        border-color: #e2e8f0;
        color: #64748b;
    }

    /* ── Mobile Responsive ─────────────────────────────── */
    @media (max-width: 576px) {
        .pltbb-footer {
            flex-direction: column;
            align-items: stretch !important;
            gap: 8px;
        }
        .pltbb-footer .dataTables_info {
            text-align: center;
        }
        .pltbb-footer .dataTables_length {
            justify-content: center;
        }
        .pltbb-footer .dataTables_paginate {
            justify-content: center;
        }
    }
</style>

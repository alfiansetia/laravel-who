<style>
    :root {
        --setting-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        --setting-accent: #6366f1;
    }

    /* ── Setting Cards ────────────────────────────────── */
    .setting-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        margin-bottom: 1rem;
    }

    .setting-card .card-header {
        background: #fff;
        border-bottom: 1px solid #f1f5f9;
        border-radius: 12px 12px 0 0 !important;
        padding: 0.75rem 1.25rem;
    }

    .setting-card .card-header h5 {
        font-size: 0.9rem;
        font-weight: 700;
        color: #334155;
        margin: 0;
    }

    .setting-card .card-body {
        padding: 1rem 1.25rem;
    }

    .setting-card .card-footer {
        background: #fff;
        border-top: 1px solid #f1f5f9;
        border-radius: 0 0 12px 12px;
        padding: 0.75rem 1.25rem;
    }

    /* ── Resource Item ────────────────────────────────── */
    .resource-item {
        padding: 0.75rem 1rem;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
        transition: all 0.15s;
    }

    .resource-item:hover {
        border-color: var(--setting-accent);
        box-shadow: 0 2px 4px rgba(99, 102, 241, 0.1);
    }

    .resource-item:last-child {
        margin-bottom: 0;
    }

    .resource-item .resource-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: #334155;
        margin: 0;
    }

    .resource-item .resource-value {
        font-size: 0.75rem;
        color: #64748b;
    }

    .resource-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .resource-badge.primary {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .resource-badge.secondary {
        background: #f1f5f9;
        color: #64748b;
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
        background: #eef2ff;
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

    /* ── Badge Styles ─────────────────────────────────── */
    .device-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .device-badge.current {
        background: #d1fae5;
        color: #059669;
    }

    .device-badge.platform {
        background: #dbeafe;
        color: #1d4ed8;
    }

    /* ── Modal Modern ─────────────────────────────────── */
    .modal-modern .modal-content {
        border: none;
        border-radius: 16px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .modal-modern .modal-header {
        background: var(--setting-gradient);
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

    /* ── Footer (DataTables native lip) ───────────────── */
    .setting-footer {
        border-top: 1px solid #f1f5f9;
        background: #fff;
        border-radius: 0 0 12px 12px;
        min-height: 48px;
    }

    .setting-footer .dataTables_length {
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
    }

    .setting-footer .dataTables_length label {
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
        font-size: 0.85rem;
        color: #64748b;
    }

    .setting-footer .dataTables_length select {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 0.35rem 0.75rem;
        font-size: 0.85rem;
        color: #64748b;
        background: #fff;
        cursor: pointer;
    }

    .setting-footer .dataTables_info {
        font-size: 0.85rem;
        color: #64748b;
        padding: 0;
        margin: 0;
    }

    .setting-footer .dataTables_paginate {
        display: flex !important;
        align-items: center;
        gap: 4px;
        margin: 0;
    }

    .setting-footer .dataTables_paginate .paginate_button {
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

    .setting-footer .dataTables_paginate .paginate_button:hover {
        background: #eef2ff;
        border-color: #6366f1;
        color: #6366f1;
    }

    .setting-footer .dataTables_paginate .paginate_button.current {
        background: var(--setting-gradient);
        color: #fff;
        border-color: transparent;
    }

    .setting-footer .dataTables_paginate .paginate_button.disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    /* ── Form Overrides ───────────────────────────────── */
    .setting-card .form-control:focus {
        border-color: var(--setting-accent);
        box-shadow: 0 0 0 0.15rem rgba(99, 102, 241, 0.25);
    }

    /* ── Log Textarea ─────────────────────────────────── */
    .log-textarea {
        background: #1e293b;
        color: #e2e8f0;
        border: none;
        border-radius: 8px;
        font-family: 'Fira Code', 'Consolas', monospace;
        font-size: 0.78rem;
        padding: 0.75rem;
        resize: vertical;
    }

    .log-textarea:focus {
        box-shadow: 0 0 0 0.15rem rgba(99, 102, 241, 0.25);
    }

    /* ── JSON Preview ─────────────────────────────────── */
    .json-preview {
        background: #1e293b;
        color: #e2e8f0;
        border: none;
        border-radius: 10px;
        padding: 1rem;
        font-family: 'Fira Code', 'Consolas', monospace;
        font-size: 0.78rem;
        min-height: 300px;
        max-height: 500px;
        overflow-y: auto;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    /* ── Mobile Responsive ─────────────────────────────── */
    @media (max-width: 576px) {
        .setting-footer {
            flex-direction: column;
            align-items: stretch !important;
            gap: 8px;
        }
    }
</style>

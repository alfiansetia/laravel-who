<style>
    /* Upload dropzone */
    .upload-dropzone {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 2rem;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #fafafa;
    }

    .upload-dropzone:hover,
    .upload-dropzone.dragover {
        border-color: #f59e0b;
        background: #fffbeb;
    }

    .upload-dropzone.has-file {
        border-color: #22c55e;
        background: #f0fdf4;
    }

    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
    }

    .content-header {
        padding: 1.5rem 0.5rem;
    }

    .filter-card {
        border-radius: 16px;
        border: none;
        background: #fff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }

    .filter-card .card-header {
        background: transparent;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.25rem;
        font-weight: 600;
    }

    .kategori-badge {
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        border: 2px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        transition: all 0.2s ease;
    }

    .kategori-badge:hover {
        border-color: #6366f1;
        color: #6366f1;
        background: #eef2ff;
    }

    .kategori-badge.active {
        background: var(--primary-gradient);
        color: #fff;
        border-color: transparent;
    }

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

    .badge-kategori {
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-akd {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .badge-akl {
        background: #d1fae5;
        color: #059669;
    }

    .badge-pkd {
        background: #fef3c7;
        color: #d97706;
    }

    .badge-pkl {
        background: #fce7f3;
        color: #db2777;
    }

    .badge-lainnya {
        background: #f1f5f9;
        color: #64748b;
    }

    .expired-text {
        color: #dc2626;
        font-weight: 600;
    }

    .expiring-text {
        color: #d97706;
        font-weight: 600;
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
        padding: 0 8px;
    }

    .pagination-modern .page-btn:hover:not(:disabled):not(.active) {
        background: #eef2ff;
        border-color: #6366f1;
        color: #6366f1;
    }

    .pagination-modern .page-btn.active {
        background: var(--primary-gradient);
        color: #fff;
        border-color: transparent;
    }

    .pagination-modern .page-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .per-page-select {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 0.4rem 0.75rem;
        font-size: 0.85rem;
    }

    .loading-overlay {
        position: relative;
    }

    .loading-overlay::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        z-index: 10;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s;
    }

    .loading-overlay.loading::after {
        opacity: 1;
        pointer-events: auto;
    }

    /* Client-side import progress */
    .import-progress-detail {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 4px;
    }
</style>

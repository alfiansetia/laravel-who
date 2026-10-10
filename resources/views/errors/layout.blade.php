@extends('template')

@push('css')
<style>
    /* Error page mengikuti bahasa visual aplikasi (template + navbar):
       biru solid #2b6cb0, radius 12px, tanpa gradient. */
    .error-container {
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .error-card {
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        max-width: 580px;
        width: 100%;
        background: #fff;
    }

    .error-card-body {
        padding: 2.5rem 2rem;
        text-align: center;
    }

    .error-icon-wrapper {
        width: 84px;
        height: 84px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.25rem;
    }

    .error-icon-wrapper i {
        font-size: 2rem;
    }

    .error-icon-wrapper.icon-404 {
        background: #ebf8ff;
    }
    .error-icon-wrapper.icon-404 i { color: #2b6cb0; }

    .error-icon-wrapper.icon-403 {
        background: #fffff0;
        border: 1px solid #ecc94b;
    }
    .error-icon-wrapper.icon-403 i { color: #b7791f; }

    .error-icon-wrapper.icon-500 {
        background: #fff5f5;
        border: 1px solid #feb2b2;
    }
    .error-icon-wrapper.icon-500 i { color: #c53030; }

    .error-icon-wrapper.icon-419 {
        background: #faf5ff;
        border: 1px solid #d6bcfa;
    }
    .error-icon-wrapper.icon-419 i { color: #6b46c1; }

    .error-icon-wrapper.icon-429 {
        background: #fffaf0;
        border: 1px solid #fbd38d;
    }
    .error-icon-wrapper.icon-429 i { color: #c05621; }

    .error-icon-wrapper.icon-503 {
        background: #f0fff4;
        border: 1px solid #9ae6b4;
    }
    .error-icon-wrapper.icon-503 i { color: #276749; }

    .error-code {
        font-size: 3.5rem;
        font-weight: 800;
        letter-spacing: -2px;
        color: #1a202c;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .error-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.75rem;
    }

    .error-message {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 1.75rem;
    }

    .error-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-error-primary {
        background: #2b6cb0;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        font-size: 0.9rem;
        transition: background 0.2s;
    }

    .btn-error-primary:hover {
        background: #2c5282;
        color: #fff;
        text-decoration: none;
    }

    .btn-error-secondary {
        background: #f8fafc;
        color: #4a5568;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .btn-error-secondary:hover {
        background: #ebf8ff;
        border-color: #2b6cb0;
        color: #2b6cb0;
        text-decoration: none;
    }

    .error-footer-text {
        margin-top: 1.75rem;
        padding-top: 1.25rem;
        border-top: 1px solid #f1f5f9;
    }

    .error-footer-text small {
        color: #94a3b8;
        font-size: 0.8rem;
    }

    /* Aksen atas kartu mengikuti warna navbar active indicator. */
    .error-card::before {
        content: '';
        display: block;
        height: 4px;
        background: #3182ce;
    }

    @media (max-width: 576px) {
        .error-card-body {
            padding: 2rem 1.25rem;
        }
        .error-code {
            font-size: 2.75rem;
        }
        .error-icon-wrapper {
            width: 72px;
            height: 72px;
        }
        .error-icon-wrapper i {
            font-size: 1.75rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="error-container">
        <div class="error-card">
            <div class="error-card-body">
                @yield('error-content')
            </div>
        </div>
    </div>
</div>
@endsection

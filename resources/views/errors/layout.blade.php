@extends('template')

@push('css')
<style>
    .error-container {
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .error-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        max-width: 580px;
        width: 100%;
        background: #fff;
    }

    .error-card-body {
        padding: 3rem 2.5rem;
        text-align: center;
    }

    .error-icon-wrapper {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        position: relative;
    }

    .error-icon-wrapper::before {
        content: '';
        position: absolute;
        inset: -8px;
        border-radius: 50%;
        opacity: 0.15;
    }

    .error-icon-wrapper i {
        font-size: 2.5rem;
    }

    .error-icon-wrapper.icon-404 {
        background: #eff6ff;
    }
    .error-icon-wrapper.icon-404::before { background: #3b82f6; }
    .error-icon-wrapper.icon-404 i { color: #3b82f6; }

    .error-icon-wrapper.icon-403 {
        background: #fef3c7;
    }
    .error-icon-wrapper.icon-403::before { background: #f59e0b; }
    .error-icon-wrapper.icon-403 i { color: #f59e0b; }

    .error-icon-wrapper.icon-500 {
        background: #fef2f2;
    }
    .error-icon-wrapper.icon-500::before { background: #ef4444; }
    .error-icon-wrapper.icon-500 i { color: #ef4444; }

    .error-icon-wrapper.icon-419 {
        background: #f5f3ff;
    }
    .error-icon-wrapper.icon-419::before { background: #8b5cf6; }
    .error-icon-wrapper.icon-419 i { color: #8b5cf6; }

    .error-icon-wrapper.icon-429 {
        background: #fff7ed;
    }
    .error-icon-wrapper.icon-429::before { background: #f97316; }
    .error-icon-wrapper.icon-429 i { color: #f97316; }

    .error-icon-wrapper.icon-503 {
        background: #f0fdf4;
    }
    .error-icon-wrapper.icon-503::before { background: #22c55e; }
    .error-icon-wrapper.icon-503 i { color: #22c55e; }

    .error-code {
        font-size: 4rem;
        font-weight: 800;
        letter-spacing: -2px;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a78bfa 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .error-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.75rem;
    }

    .error-message {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 2rem;
    }

    .error-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-error-primary {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 0.65rem 1.5rem;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
    }

    .btn-error-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        color: #fff;
        text-decoration: none;
    }

    .btn-error-secondary {
        background: #f8fafc;
        color: #64748b;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.65rem 1.5rem;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .btn-error-secondary:hover {
        background: #eef2ff;
        border-color: #6366f1;
        color: #6366f1;
        transform: translateY(-2px);
        text-decoration: none;
    }

    .error-footer-text {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #f1f5f9;
    }

    .error-footer-text small {
        color: #94a3b8;
        font-size: 0.8rem;
    }

    /* Decorative elements */
    .error-card::before {
        content: '';
        display: block;
        height: 4px;
        background: linear-gradient(90deg, #6366f1, #8b5cf6, #a78bfa);
    }

    @media (max-width: 576px) {
        .error-card-body {
            padding: 2rem 1.5rem;
        }
        .error-code {
            font-size: 3rem;
        }
        .error-icon-wrapper {
            width: 80px;
            height: 80px;
        }
        .error-icon-wrapper i {
            font-size: 2rem;
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

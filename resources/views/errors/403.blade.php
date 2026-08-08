@extends('errors.layout')

@section('error-content')
    <div class="error-icon-wrapper icon-403">
        <i class="fas fa-lock"></i>
    </div>
    <div class="error-code">403</div>
    <h4 class="error-title">Akses Ditolak</h4>
    <p class="error-message">
        Maaf, Anda tidak memiliki izin untuk mengakses halaman ini. Silakan hubungi administrator jika Anda memerlukan akses.
    </p>
    <div class="error-actions">
        <a href="{{ route('home') }}" class="btn-error-primary">
            <i class="fas fa-home mr-2"></i>Kembali ke Beranda
        </a>
        <a href="javascript:history.back()" class="btn-error-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Halaman Sebelumnya
        </a>
    </div>
    <div class="error-footer-text">
        <small><i class="fas fa-shield-alt mr-1"></i>Akses terbatas untuk pengguna yang tidak terotorisasi.</small>
    </div>
@endsection

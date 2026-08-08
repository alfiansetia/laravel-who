@extends('errors.layout')

@section('error-content')
    <div class="error-icon-wrapper icon-419">
        <i class="fas fa-clock"></i>
    </div>
    <div class="error-code">419</div>
    <h4 class="error-title">Sesi Telah Berakhir</h4>
    <p class="error-message">
        Sesi Anda telah kedaluwarsa karena tidak aktif terlalu lama. Silakan muat ulang halaman dan coba lagi.
    </p>
    <div class="error-actions">
        <a href="javascript:location.reload()" class="btn-error-primary">
            <i class="fas fa-redo mr-2"></i>Muat Ulang Halaman
        </a>
        <a href="{{ route('home') }}" class="btn-error-secondary">
            <i class="fas fa-home mr-2"></i>Kembali ke Beranda
        </a>
    </div>
    <div class="error-footer-text">
        <small><i class="fas fa-shield-alt mr-1"></i>Token CSRF sudah kedaluwarsa. Ini demi keamanan aplikasi.</small>
    </div>
@endsection

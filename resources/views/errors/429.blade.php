@extends('errors.layout')

@section('error-content')
    <div class="error-icon-wrapper icon-429">
        <i class="fas fa-tachometer-alt"></i>
    </div>
    <div class="error-code">429</div>
    <h4 class="error-title">Terlalu Banyak Permintaan</h4>
    <p class="error-message">
        Anda telah mengirim terlalu banyak permintaan dalam waktu singkat. Silakan tunggu beberapa saat sebelum mencoba lagi.
    </p>
    <div class="error-actions">
        <a href="javascript:location.reload()" class="btn-error-primary">
            <i class="fas fa-redo mr-2"></i>Coba Lagi
        </a>
        <a href="{{ route('home') }}" class="btn-error-secondary">
            <i class="fas fa-home mr-2"></i>Kembali ke Beranda
        </a>
    </div>
    <div class="error-footer-text">
        <small><i class="fas fa-info-circle mr-1"></i>Batas rate-limit diterapkan untuk menjaga performa server.</small>
    </div>
@endsection

@extends('errors.layout')

@section('error-content')
    <div class="error-icon-wrapper icon-404">
        <i class="fas fa-map-signs"></i>
    </div>
    <div class="error-code">404</div>
    <h4 class="error-title">Halaman Tidak Ditemukan</h4>
    <p class="error-message">
        Sepertinya halaman yang Anda cari sudah dipindahkan, dihapus, atau tidak pernah ada.
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
        <small><i class="fas fa-info-circle mr-1"></i>Jika Anda yakin ini adalah kesalahan, silakan hubungi administrator.</small>
    </div>
@endsection

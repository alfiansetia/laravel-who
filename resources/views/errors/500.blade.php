@extends('errors.layout')

@section('error-content')
    <div class="error-icon-wrapper icon-500">
        <i class="fas fa-exclamation-triangle"></i>
    </div>
    <div class="error-code">500</div>
    <h4 class="error-title">Kesalahan Server Internal</h4>
    <p class="error-message">
        Terjadi kesalahan yang tidak terduga di server. Tim kami telah diberitahu dan sedang menangani masalah ini.
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
        <small><i class="fas fa-bug mr-1"></i>Jika masalah terus berlanjut, silakan hubungi tim IT.</small>
    </div>
@endsection

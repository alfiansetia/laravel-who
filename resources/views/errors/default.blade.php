@extends('errors.layout')

@section('error-content')
    <div class="error-icon-wrapper icon-500">
        <i class="fas fa-exclamation-circle"></i>
    </div>
    <div class="error-code">{{ $status ?? 'Error' }}</div>
    <h4 class="error-title">Terjadi Kesalahan</h4>
    <p class="error-message">
        {{ $exception->getMessage() ?: 'Maaf, sesuatu yang tidak terduga telah terjadi. Silakan coba lagi nanti.' }}
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
        <small><i class="fas fa-info-circle mr-1"></i>Hubungi administrator jika masalah terus berlanjut.</small>
    </div>
@endsection

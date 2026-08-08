@extends('errors.layout')

@section('error-content')
    <div class="error-icon-wrapper icon-503">
        <i class="fas fa-tools"></i>
    </div>
    <div class="error-code">503</div>
    <h4 class="error-title">Layanan Tidak Tersedia</h4>
    <p class="error-message">
        Sistem sedang dalam pemeliharaan atau pembaruan. Silakan coba beberapa saat lagi.
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
        <small><i class="fas fa-wrench mr-1"></i>Pemeliharaan biasanya selesai dalam beberapa menit. Terima kasih atas kesabaran Anda.</small>
    </div>
@endsection

@extends('template')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
    @include('izin_edar.partials._styles')
@endpush

@section('content')
    <div class="container-fluid">
        @include('izin_edar.partials._file_card')
        @include('izin_edar.partials._sync_card')
        @include('izin_edar.partials._table')
    </div>

    @include('izin_edar.partials._modal_upload')
    @include('izin_edar.partials._modal_detail')
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    @include('izin_edar.scripts._app')
    @include('izin_edar.scripts._sync')
    @include('izin_edar.scripts._upload')
@endpush

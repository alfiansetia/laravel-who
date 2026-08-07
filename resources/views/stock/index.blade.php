@extends('template')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
    @include('stock.partials._styles')
@endpush

@section('content')
    <div class="container-fluid">
        @include('stock.partials._table')
    </div>

    @include('stock.partials._modal_lot')
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @include('stock.scripts._app')
@endpush

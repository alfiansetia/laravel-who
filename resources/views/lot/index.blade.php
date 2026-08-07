@extends('template')

@push('css')
    @include('lot.partials._styles')
@endpush

@section('content')
    <div class="container-fluid">
        @include('lot.partials._table')
    </div>

    @include('lot.partials._modal_detail')
@endsection

@push('js')
    @include('lot.scripts._app')
@endpush

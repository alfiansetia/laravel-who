@extends('template')

@push('css')
    @include('do.partials._styles')
@endpush

@section('content')
    <div class="container-fluid">
        @include('do.partials._table')
    </div>

    @include('do.partials._modal_detail')
@endsection

@push('js')
    @include('do.scripts._app')
@endpush

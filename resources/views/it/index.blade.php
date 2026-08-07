@extends('template')

@push('css')
    @include('it.partials._styles')
@endpush

@section('content')
    <div class="container-fluid">
        @include('it.partials._table')
    </div>

    @include('it.partials._modal_detail')
@endsection

@push('js')
    @include('it.scripts._app')
@endpush

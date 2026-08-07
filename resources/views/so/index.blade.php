@extends('template')

@push('css')
    @include('so.partials._styles')
@endpush

@section('content')
    <div class="container-fluid">
        @include('so.partials._table')
    </div>

    @include('so.partials._modal_detail')
@endsection

@push('js')
    @include('so.scripts._app')
@endpush

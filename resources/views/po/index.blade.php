@extends('template')

@push('css')
    @include('po.partials._styles')
@endpush

@section('content')
    <div class="container-fluid">
        @include('po.partials._table')
    </div>

    @include('po.partials._modal_detail')
@endsection

@push('js')
    @include('po.scripts._app')
@endpush

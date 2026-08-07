@extends('template')

@push('css')
    @include('product_odoo.partials._styles')
@endpush

@section('content')
    <div class="container-fluid">
        @include('product_odoo.partials._table')
    </div>

    @include('product_odoo.partials._modal_detail')
@endsection

@push('js')
    @include('product_odoo.scripts._app')
@endpush

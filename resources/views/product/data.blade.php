@extends('template', ['title' => 'Data Product'])

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet" />
    @include('product.partials._styles')
@endpush

@section('content')
    <div class="container-fluid">
        @include('product.partials._table')
    </div>

    @include('product.modal')
@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
    <script>
        $(document).ready(function() {
            lightbox.option({
                resizeDuration: 200,
                wrapAround: true
            });
        });
    </script>
    @include('product.scripts._app')
@endpush

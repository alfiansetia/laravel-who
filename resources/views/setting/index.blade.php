@extends('template')

@push('css')
    @include('setting.partials._styles')
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            {{-- Session Card --}}
            <div class="col-lg-8">
                @include('setting.partials._session_card')
            </div>

            {{-- Resource Card --}}
            <div class="col-lg-4">
                @include('setting.partials._resource_card')
            </div>

            {{-- Devices Card --}}
            <div class="col-lg-8">
                @include('setting.partials._devices_card')
            </div>

            {{-- Logs Card --}}
            <div class="col-lg-4">
                @include('setting.partials._logs_card')
            </div>
        </div>
    </div>

    @include('setting.partials._modal_detail')
    @include('setting.partials._modal_cek')
@endsection

@push('js')
    @include('setting.scripts._app')
@endpush

@extends('template')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <a href="{{ route('shipping_estimate.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    Tambah Estimasi Ongkir
                </h4>
            </div>
            <div class="card-body">
                <form action="{{ route('shipping_estimate.store') }}" method="POST" id="formEstimate">
                    @csrf
                    @include('shipping_estimate._form')

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    @include('shipping_estimate._scripts')
@endpush

@extends('template')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <a href="{{ route('shipping_estimate.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    Edit Estimasi Ongkir: {{ $estimate->no_so }}
                </h4>
            </div>
            <div class="card-body">
                <form action="{{ route('shipping_estimate.update', $estimate->id) }}" method="POST" id="formEstimate">
                    @csrf
                    @method('PUT')
                    @include('shipping_estimate._form')

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Card -->
        <div class="card mt-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Ringkasan Perhitungan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <table class="table table-sm">
                            <tr>
                                <td>Total Nilai Faktur</td>
                                <td class="text-end"><strong>Rp
                                        {{ number_format($estimate->total_invoice_value, 0, ',', '.') }}</strong></td>
                            </tr>
                            <tr>
                                <td>Total Berat Timbangan</td>
                                <td class="text-end"><strong>{{ number_format($estimate->total_weight_actual, 2) }}
                                        kg</strong></td>
                            </tr>
                            <tr>
                                <td>Berat Dimensi (REG/Udara)</td>
                                <td class="text-end"><strong>{{ number_format($estimate->total_weight_dimension_reg, 2) }}
                                        kg</strong></td>
                            </tr>
                            <tr>
                                <td>Berat Dimensi (DARAT/Laut)</td>
                                <td class="text-end"><strong>{{ number_format($estimate->total_weight_dimension_darat, 2) }}
                                        kg</strong></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Shipper</th>
                                    <th>Tipe</th>
                                    <th class="text-end">Berat</th>
                                    <th class="text-end">Ongkir</th>
                                    <th class="text-end">Asuransi</th>
                                    <th class="text-end">P. Kayu</th>
                                    <th class="text-end">Admin</th>
                                    <th class="text-end">PPN</th>
                                    <th class="text-end">Total</th>
                                    <th>Est.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($estimate->rates as $rate)
                                    <tr>
                                        <td>{{ $rate->shipper_name }}</td>
                                        <td>{{ $rate->shipping_type }}</td>
                                        <td class="text-end">{{ number_format($rate->charged_weight, 0) }} kg</td>
                                        <td class="text-end">Rp {{ number_format($rate->shipping_cost, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($rate->insurance_cost, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end">Rp {{ number_format($rate->packing_cost, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($rate->admin_fee, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($rate->ppn_cost, 0, ',', '.') }}</td>
                                        <td class="text-end"><strong>Rp
                                                {{ number_format($rate->total_cost, 0, ',', '.') }}</strong></td>
                                        <td>{{ $rate->estimated_days ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    @include('shipping_estimate._scripts')
@endpush

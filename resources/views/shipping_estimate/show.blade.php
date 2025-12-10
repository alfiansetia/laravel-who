@extends('template')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <a href="{{ route('shipping_estimate.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    Detail Estimasi Ongkir: {{ $estimate->no_so }}
                    <a href="{{ route('shipping_estimate.edit', $estimate->id) }}" class="btn btn-sm btn-warning float-end">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </h4>
            </div>
            <div class="card-body">
                {{-- Header Info --}}
                <div class="row mb-4">
                    <div class="col-md-4">
                        <strong>No SO:</strong> {{ $estimate->no_so }}
                    </div>
                    <div class="col-md-4">
                        <strong>Customer:</strong> {{ $estimate->customer_name }}
                    </div>
                    <div class="col-md-4">
                        <strong>Alamat Kirim:</strong> {{ $estimate->shipping_address }}
                    </div>
                </div>

                {{-- Items --}}
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-box"></i> Item / Produk</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama Item</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($estimate->items as $i => $item)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $item->item_code }}</td>
                                        <td>{{ $item->item_name }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <td colspan="5" class="text-end"><strong>Total Nilai Faktur:</strong></td>
                                    <td class="text-end"><strong>Rp
                                            {{ number_format($estimate->total_invoice_value, 0, ',', '.') }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Packages --}}
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-boxes"></i> Koli / Paket</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Koli</th>
                                    <th class="text-center">Berat (kg)</th>
                                    <th class="text-center">P x L x T</th>
                                    <th class="text-center">Dimensi REG</th>
                                    <th class="text-center">Dimensi DARAT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($estimate->packages as $i => $pkg)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $pkg->package_name }}</td>
                                        <td class="text-center">{{ number_format($pkg->weight_actual, 2) }}</td>
                                        <td class="text-center">{{ $pkg->dimension_length }} × {{ $pkg->dimension_width }}
                                            × {{ $pkg->dimension_height }}</td>
                                        <td class="text-center">{{ number_format($pkg->weight_dimension_reg, 2) }} kg</td>
                                        <td class="text-center">{{ number_format($pkg->weight_dimension_darat, 2) }} kg
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <td colspan="2" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-center"><strong>{{ number_format($estimate->total_weight_actual, 2) }}
                                            kg</strong></td>
                                    <td></td>
                                    <td class="text-center">
                                        <strong>{{ number_format($estimate->total_weight_dimension_reg, 2) }} kg</strong>
                                    </td>
                                    <td class="text-center">
                                        <strong>{{ number_format($estimate->total_weight_dimension_darat, 2) }} kg</strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Rates --}}
                <div class="card mb-3">
                    <div class="card-header bg-warning">
                        <h6 class="mb-0"><i class="fas fa-truck"></i> Estimasi Ongkir</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Shipper</th>
                                    <th>Tipe</th>
                                    <th class="text-center">Berat Digunakan</th>
                                    <th class="text-end">Tarif/kg</th>
                                    <th class="text-end">Ongkir</th>
                                    <th class="text-end">Asuransi
                                        ({{ $estimate->rates->first()->insurance_percentage ?? 0 }}%)</th>
                                    <th class="text-end">P. Kayu</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Est. Hari</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($estimate->rates as $rate)
                                    <tr>
                                        <td><strong>{{ $rate->shipper_name }}</strong></td>
                                        <td>{{ $rate->shipping_type }}</td>
                                        <td class="text-center">{{ number_format($rate->charged_weight, 2) }} kg</td>
                                        <td class="text-end">Rp {{ number_format($rate->rate_per_kg, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($rate->shipping_cost, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($rate->insurance_cost, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end">Rp {{ number_format($rate->packing_cost, 0, ',', '.') }}</td>
                                        <td class="text-end"><strong>Rp
                                                {{ number_format($rate->total_cost, 0, ',', '.') }}</strong></td>
                                        <td class="text-center">{{ $rate->estimated_days }} hari</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($estimate->notes)
                    <div class="alert alert-info">
                        <strong>Catatan:</strong> {{ $estimate->notes }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

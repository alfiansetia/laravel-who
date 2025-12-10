{{-- Header Info --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="form-group">
            <label for="no_so">No SO <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="no_so" id="no_so"
                value="{{ old('no_so', $estimate->no_so ?? '') }}" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="customer_name">Customer <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="customer_name" id="customer_name"
                value="{{ old('customer_name', $estimate->customer_name ?? '') }}" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="shipping_address">Alamat Kirim <span class="text-danger">*</span></label>
            <textarea class="form-control" name="shipping_address" id="shipping_address" rows="2" required>{{ old('shipping_address', $estimate->shipping_address ?? '') }}</textarea>
        </div>
    </div>
</div>

{{-- Items Section --}}
<div class="card mb-3">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-box"></i> Item / Produk</h6>
        <button type="button" class="btn btn-sm btn-light" onclick="addItem()">
            <i class="fas fa-plus"></i> Tambah Item
        </button>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-bordered mb-0" id="tableItems">
            <thead class="thead-light">
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Item</th>
                    <th style="width: 100px;">Qty</th>
                    <th style="width: 200px;">Total Harga</th>
                    <th style="width: 50px;"></th>
                </tr>
            </thead>
            <tbody id="itemsBody">
                @if (isset($estimate) && $estimate->items->count() > 0)
                    @foreach ($estimate->items as $index => $item)
                        <tr>
                            <td class="text-center item-no">{{ $index + 1 }}</td>
                            <td><input type="text" class="form-control form-control-sm"
                                    name="items[{{ $index }}][item_name]" value="{{ $item->item_name }}"
                                    required></td>
                            <td><input type="number" class="form-control form-control-sm item-qty"
                                    name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}"
                                    min="1" onchange="calculateTotalInvoice()"></td>
                            <td><input type="text" class="form-control form-control-sm item-total currency"
                                    name="items[{{ $index }}][total_price]"
                                    value="{{ number_format($item->total_price, 0, ',', '.') }}"
                                    onchange="calculateTotalInvoice()"></td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-danger"
                                    onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td class="text-center item-no">1</td>
                        <td><input type="text" class="form-control form-control-sm" name="items[0][item_name]"
                                required></td>
                        <td><input type="number" class="form-control form-control-sm item-qty"
                                name="items[0][quantity]" value="1" min="1"
                                onchange="calculateTotalInvoice()"></td>
                        <td><input type="text" class="form-control form-control-sm item-total currency"
                                name="items[0][total_price]" value="0" onchange="calculateTotalInvoice()"></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-danger"
                                onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr class="bg-light">
                    <td colspan="2" class="text-end"><strong>Total:</strong></td>
                    <td><input type="text" class="form-control form-control-sm" id="totalQty" readonly></td>
                    <td><input type="text" class="form-control form-control-sm" id="totalInvoice" readonly></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Packages Section --}}
<div class="card mb-3">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-boxes"></i> Koli / Paket</h6>
        <button type="button" class="btn btn-sm btn-light" onclick="addPackage()">
            <i class="fas fa-plus"></i> Tambah Koli
        </button>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-bordered mb-0" id="tablePackages">
            <thead class="thead-light">
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Koli / Isi</th>
                    <th style="width: 80px;">Jml Koli</th>
                    <th style="width: 100px;">Berat (kg)</th>
                    <th style="width: 80px;">P (cm)</th>
                    <th style="width: 80px;">L (cm)</th>
                    <th style="width: 80px;">T (cm)</th>
                    <th style="width: 100px;">Dimensi REG</th>
                    <th style="width: 100px;">Dimensi DARAT</th>
                    <th style="width: 50px;"></th>
                </tr>
            </thead>
            <tbody id="packagesBody">
                @if (isset($estimate) && $estimate->packages->count() > 0)
                    @foreach ($estimate->packages as $index => $pkg)
                        <tr>
                            <td class="text-center pkg-no">{{ $index + 1 }}</td>
                            <td><input type="text" class="form-control form-control-sm"
                                    name="packages[{{ $index }}][package_name]"
                                    value="{{ $pkg->package_name }}"></td>
                            <td><input type="number" class="form-control form-control-sm pkg-qty"
                                    name="packages[{{ $index }}][quantity]" value="{{ $pkg->quantity ?? 1 }}"
                                    min="1" onchange="calculateDimension(this)"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm pkg-weight"
                                    name="packages[{{ $index }}][weight_actual]"
                                    value="{{ $pkg->weight_actual }}" onchange="calculateDimension(this)"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm pkg-length"
                                    name="packages[{{ $index }}][dimension_length]"
                                    value="{{ $pkg->dimension_length }}" onchange="calculateDimension(this)"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm pkg-width"
                                    name="packages[{{ $index }}][dimension_width]"
                                    value="{{ $pkg->dimension_width }}" onchange="calculateDimension(this)"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm pkg-height"
                                    name="packages[{{ $index }}][dimension_height]"
                                    value="{{ $pkg->dimension_height }}" onchange="calculateDimension(this)"></td>
                            <td><input type="text" class="form-control form-control-sm pkg-dim-reg" readonly></td>
                            <td><input type="text" class="form-control form-control-sm pkg-dim-darat" readonly>
                            </td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-danger"
                                    onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td class="text-center pkg-no">1</td>
                        <td><input type="text" class="form-control form-control-sm"
                                name="packages[0][package_name]"></td>
                        <td><input type="number" class="form-control form-control-sm pkg-qty"
                                name="packages[0][quantity]" value="1" min="1"
                                onchange="calculateDimension(this)"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm pkg-weight"
                                name="packages[0][weight_actual]" value="0" onchange="calculateDimension(this)">
                        </td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm pkg-length"
                                name="packages[0][dimension_length]" value="0"
                                onchange="calculateDimension(this)"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm pkg-width"
                                name="packages[0][dimension_width]" value="0"
                                onchange="calculateDimension(this)"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm pkg-height"
                                name="packages[0][dimension_height]" value="0"
                                onchange="calculateDimension(this)"></td>
                        <td><input type="text" class="form-control form-control-sm pkg-dim-reg" readonly></td>
                        <td><input type="text" class="form-control form-control-sm pkg-dim-darat" readonly></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-danger"
                                onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr class="bg-light">
                    <td colspan="2" class="text-end"><strong>Total:</strong></td>
                    <td><input type="text" class="form-control form-control-sm" id="totalKoli" readonly></td>
                    <td><input type="text" class="form-control form-control-sm" id="totalWeight" readonly></td>
                    <td colspan="3"></td>
                    <td><input type="text" class="form-control form-control-sm" id="totalDimReg" readonly></td>
                    <td><input type="text" class="form-control form-control-sm" id="totalDimDarat" readonly></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Rates Section --}}
<div class="card mb-3">
    <div class="card-header bg-warning d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-truck"></i> Estimasi Ongkir</h6>
        <button type="button" class="btn btn-sm btn-dark" onclick="addRate()">
            <i class="fas fa-plus"></i> Tambah Shipper
        </button>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-bordered mb-0" id="tableRates">
            <thead class="thead-light">
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Shipper</th>
                    <th style="width: 100px;">Tipe</th>
                    <th style="width: 120px;">Tarif/kg</th>
                    <th style="width: 80px;">Asuransi %</th>
                    <th style="width: 120px;">P. Kayu</th>
                    <th style="width: 100px;">Admin</th>
                    <th style="width: 70px;">PPN %</th>
                    <th style="width: 80px;">Estimasi</th>
                    <th style="width: 50px;"></th>
                </tr>
            </thead>
            <tbody id="ratesBody">
                @if (isset($estimate) && $estimate->rates->count() > 0)
                    @foreach ($estimate->rates as $index => $rate)
                        <tr>
                            <td class="text-center rate-no">{{ $index + 1 }}</td>
                            <td><input type="text" class="form-control form-control-sm"
                                    name="rates[{{ $index }}][shipper_name]"
                                    value="{{ $rate->shipper_name }}" required></td>
                            <td>
                                <select class="form-control form-control-sm"
                                    name="rates[{{ $index }}][shipping_type]">
                                    <option value="REG" {{ $rate->shipping_type == 'REG' ? 'selected' : '' }}>
                                        REG/Udara</option>
                                    <option value="DARAT" {{ $rate->shipping_type == 'DARAT' ? 'selected' : '' }}>
                                        DARAT/Laut</option>
                                </select>
                            </td>
                            <td><input type="text" class="form-control form-control-sm currency"
                                    name="rates[{{ $index }}][rate_per_kg]"
                                    value="{{ number_format($rate->rate_per_kg, 0, ',', '.') }}"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm"
                                    name="rates[{{ $index }}][insurance_percentage]"
                                    value="{{ $rate->insurance_percentage }}"></td>
                            <td><input type="text" class="form-control form-control-sm currency"
                                    name="rates[{{ $index }}][packing_cost]"
                                    value="{{ number_format($rate->packing_cost, 0, ',', '.') }}"></td>
                            <td><input type="text" class="form-control form-control-sm currency"
                                    name="rates[{{ $index }}][admin_fee]"
                                    value="{{ number_format($rate->admin_fee, 0, ',', '.') }}"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm"
                                    name="rates[{{ $index }}][ppn_percentage]"
                                    value="{{ $rate->ppn_percentage }}"></td>
                            <td><input type="text" class="form-control form-control-sm"
                                    name="rates[{{ $index }}][estimated_days]"
                                    value="{{ $rate->estimated_days }}" placeholder="2-3"></td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-danger"
                                    onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td class="text-center rate-no">1</td>
                        <td><input type="text" class="form-control form-control-sm" name="rates[0][shipper_name]"
                                placeholder="TIKI, JNE, dll" required></td>
                        <td>
                            <select class="form-control form-control-sm" name="rates[0][shipping_type]">
                                <option value="REG">REG/Udara</option>
                                <option value="DARAT" selected>DARAT/Laut</option>
                            </select>
                        </td>
                        <td><input type="text" class="form-control form-control-sm currency"
                                name="rates[0][rate_per_kg]" value="0"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm"
                                name="rates[0][insurance_percentage]" value="0.20"></td>
                        <td><input type="text" class="form-control form-control-sm currency"
                                name="rates[0][packing_cost]" value="0"></td>
                        <td><input type="text" class="form-control form-control-sm currency"
                                name="rates[0][admin_fee]" value="0"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm"
                                name="rates[0][ppn_percentage]" value="0"></td>
                        <td><input type="text" class="form-control form-control-sm"
                                name="rates[0][estimated_days]" placeholder="2-3"></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-danger"
                                onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

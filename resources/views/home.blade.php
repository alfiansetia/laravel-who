@extends('template', ['title' => 'Home'])
@push('css')
    <style>
        body {
            background-color: #f8f9fa;
        }

        .menu-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .menu-icon {
            font-size: 2rem;
            color: #007bff;
            /* warna primary Bootstrap 4 */
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <h3 class="mb-4 font-weight-bold"><i class="fas fa-home"></i> Menu Utama</h3>

        <div class="row">

            <div class="col-md-3 col-sm-6 col-xl-2 mb-3">
                <div class="card menu-card h-100" onclick="window.location='{{ route('products.index') }}'">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-cube menu-icon mr-3"></i>
                        <div>
                            <h6 class="mb-0 font-weight-semibold">Product</h6>
                            <small class="text-muted">List Product</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xl-2 mb-3">
                <div class="card menu-card h-100" onclick="window.location='{{ route('alamats.index') }}'">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-shipping-fast menu-icon mr-3"></i>
                        <div>
                            <h6 class="mb-0 font-weight-semibold">Alamat</h6>
                            <small class="text-muted">List Alamat</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xl-2 mb-3">
                <div class="card menu-card h-100" onclick="window.location='{{ route('basts.index') }}'">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-file-contract menu-icon mr-3"></i>
                        <div>
                            <h6 class="mb-0 font-weight-semibold">BASTUF</h6>
                            <small class="text-muted">List BASTUF</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xl-2 mb-3">
                <div class="card menu-card h-100" onclick="window.location='{{ route('kontaks.index') }}'">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-address-book menu-icon mr-3"></i>
                        <div>
                            <h6 class="mb-0 font-weight-semibold">KONTAK</h6>
                            <small class="text-muted">List Kontak</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xl-2 mb-3">
                <div class="card menu-card h-100" onclick="window.location='{{ route('packs.index') }}'">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-list-ol menu-icon mr-3"></i>
                        <div>
                            <h6 class="mb-0 font-weight-semibold">PACKING LIST</h6>
                            <small class="text-muted">List Packing List</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xl-2 mb-3">
                <div class="card menu-card h-100" onclick="window.location='{{ route('sops.index') }}'">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-layer-group menu-icon mr-3"></i>
                        <div>
                            <h6 class="mb-0 font-weight-semibold">SOP QC</h6>
                            <small class="text-muted">List SOP QC</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xl-2 mb-3">
                <div class="card menu-card h-100" onclick="window.location='{{ route('kargans.index') }}'">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-stamp menu-icon mr-3"></i>
                        <div>
                            <h6 class="mb-0 font-weight-semibold">KARGAN</h6>
                            <small class="text-muted">List Kargan</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xl-2 mb-3">
                <div class="card menu-card h-100" onclick="window.location='{{ route('atk.index') }}'">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-pencil-ruler menu-icon mr-3"></i>
                        <div>
                            <h6 class="mb-0 font-weight-semibold">ATK</h6>
                            <small class="text-muted">List ATK</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xl-2 mb-3">
                <div class="card menu-card h-100" onclick="window.location='{{ route('product_images.index') }}'">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-images menu-icon mr-3"></i>
                        <div>
                            <h6 class="mb-0 font-weight-semibold">Images</h6>
                            <small class="text-muted">Product Images</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mb-4 font-weight-bold"><i class="fab fa-windows mr-1"></i> Odoo Menu</h3>
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xl-2 mb-3">
                <div class="card menu-card h-100" onclick="window.location='{{ route('stock.index') }}'">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-cubes menu-icon mr-3"></i>
                        <div>
                            <h6 class="mb-0 font-weight-semibold">Stock</h6>
                            <small class="text-muted">List Product Stock</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xl-2 mb-3">
                <div class="card menu-card h-100" onclick="window.location='{{ route('po.index') }}'">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-shopping-cart menu-icon mr-3"></i>
                        <div>
                            <h6 class="mb-0 font-weight-semibold">PO</h6>
                            <small class="text-muted">List PO</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xl-2 mb-3">
                <div class="card menu-card h-100" onclick="window.location='{{ route('ri.index') }}'">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-cart-arrow-down menu-icon mr-3"></i>
                        <div>
                            <h6 class="mb-0 font-weight-semibold">RI</h6>
                            <small class="text-muted">List RI</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xl-2 mb-3">
                <div class="card menu-card h-100" onclick="window.location='{{ route('vendors.index') }}'">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-store menu-icon mr-3"></i>
                        <div>
                            <h6 class="mb-0 font-weight-semibold">VENDOR</h6>
                            <small class="text-muted">List Vendor</small>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <h3 class="mb-4 font-weight-bold"><i class="fas fa-tools mr-1"></i> Tools</h3>
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xl-2 mb-3">
                <div class="card menu-card h-100" onclick="window.location='{{ route('qc.index') }}'">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-clipboard-check menu-icon mr-3"></i>
                        <div>
                            <h6 class="mb-0 font-weight-semibold">FORM QC</h6>
                            <small class="text-muted">Generate Form QC</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xl-2 mb-3">
                <div class="card menu-card h-100" onclick="window.location='{{ route('sn.index') }}'">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-list menu-icon mr-3"></i>
                        <div>
                            <h6 class="mb-0 font-weight-semibold">SN</h6>
                            <small class="text-muted">Generate SN</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xl-2 mb-3">
                <div class="card menu-card h-100" onclick="window.location='{{ route('laporan_pengiriman.index') }}'">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-box menu-icon mr-3"></i>
                        <div>
                            <h6 class="mb-0 font-weight-semibold">Kiriman</h6>
                            <small class="text-muted">Laporan Kiriman</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xl-2 mb-3">
                <div class="card menu-card h-100" onclick="window.location='{{ route('settings.index') }}'">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-cogs menu-icon mr-3"></i>
                        <div>
                            <h6 class="mb-0 font-weight-semibold">SETTING</h6>
                            <small class="text-muted">App Setting</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

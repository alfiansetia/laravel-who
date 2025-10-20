<nav class="navbar navbar-expand-lg navbar-light" style="background-color: #e3f2fd;">
    <a class="navbar-brand" href="{{ route('index') }}">
        <img src="{{ asset('images/asa.png') }}" height="30" class="d-inline-block align-top" alt="">
        {{-- <b>SAVE</b> --}}
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mx-auto">
            <li class="nav-item <?= $title == 'Data Product' ? 'active' : '' ?>">
                <a class="nav-link" href="{{ route('products.index') }}">
                    <i class="fas fa-cube mr-1"></i>Product
                </a>
            </li>
            <li class="nav-item <?= $title == 'Data Stock' ? 'active' : '' ?>">
                <a class="nav-link" href="{{ route('stock.index') }}">
                    <i class="fas fa-cubes mr-1"></i>Stock
                </a>
            </li>
            <li class="nav-item <?= $title == 'Form QC' ? 'active' : '' ?>">
                <a class="nav-link" href="{{ route('qc.index') }}">
                    <i class="fas fa-clipboard-check mr-1"></i>QC
                </a>
            </li>
            <li class="nav-item <?= $title == 'List Alamat' ? 'active' : '' ?>">
                <a class="nav-link" href="{{ route('alamats.index') }}">
                    <i class="fas fa-shipping-fast mr-1"></i>Alamat
                </a>
            </li>
            <li class="nav-item <?= $title == 'List BAST' ? 'active' : '' ?>">
                <a class="nav-link" href="{{ route('bast.index') }}">
                    <i class="fas fa-file-contract mr-1"></i>BAST
                </a>
            </li>
            {{-- <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown"
                    aria-expanded="false">
                    Other
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('monitor.do') }}">Monitor SO</a>
                    <a class="dropdown-item" href="{{ route('kontak.index') }}">Kontak</a>
                    <a class="dropdown-item" href="{{ route('vendors.index') }}">Vendors</a>
                    <a class="dropdown-item" href="{{ route('po.index') }}">PO</a>
                    <a class="dropdown-item" href="{{ route('ri.index') }}">RI</a>
                    <a class="dropdown-item" href="{{ route('kargan.index') }}">Kargan</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('packs.index') }}">PL</a>
                    <a class="dropdown-item" href="{{ route('sops.index') }}">SOP QC</a>
                    <a class="dropdown-item" href="{{ route('sn.index') }}">SN</a>
                    <div class="dropdown-divider"></div>
                </div>
            </li> --}}
        </ul>
    </div>
</nav>

<style>
    .navbar-modern {
        background: #e3f2fd !important;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        padding: 0.5rem 1.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .navbar-modern .navbar-brand b {
        color: #1a202c;
        letter-spacing: 1px;
        font-size: 1.2rem;
    }

    .navbar-modern .nav-link {
        color: #4a5568 !important;
        font-weight: 500;
        font-size: 0.9rem;
        padding: 0.5rem 1rem !important;
        transition: all 0.2s ease;
        position: relative;
        display: flex;
        align-items: center;
    }

    .navbar-modern .nav-link i {
        font-size: 1rem;
        margin-right: 6px;
        color: #718096;
        transition: color 0.2s ease;
    }

    /* Hover Effect */
    .navbar-modern .nav-item:hover .nav-link {
        color: #3182ce !important;
    }

    .navbar-modern .nav-item:hover .nav-link i {
        color: #3182ce;
    }

    /* Active Indicator */
    .navbar-modern .nav-item.active .nav-link {
        color: #2b6cb0 !important;
        font-weight: 700;
    }

    .navbar-modern .nav-item.active .nav-link::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 1rem;
        right: 1rem;
        height: 3px;
        background: #3182ce;
        border-radius: 10px;
    }

    /* Dropdown Styling */
    .navbar-modern .dropdown-menu {
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        padding: 10px;
        margin-top: 15px;
        border: 1px solid rgba(0, 0, 0, 0.05);
        animation: dropdownFade 0.3s ease;
    }

    @keyframes dropdownFade {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .navbar-modern .dropdown-item {
        border-radius: 8px;
        padding: 8px 15px;
        font-size: 0.85rem;
        color: #4a5568;
        font-weight: 500;
        transition: all 0.2s;
    }

    .navbar-modern .dropdown-item:hover {
        background-color: #ebf8ff;
        color: #2b6cb0;
        padding-left: 20px;
    }

    /* Odoo Dropdown specific */
    .nav-odoo-badge {
        background: #714B67;
        /* Odoo Purple */
        color: white;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.7rem;
        margin-left: 5px;
    }

    #btnEnvLogin,
    #btnEnvLogout {
        border-radius: 10px;
        padding: 6px 15px !important;
        border: 1px solid #e2e8f0;
        margin-left: 10px;
    }

    @media (max-width: 991.98px) {
        .navbar-modern .nav-item.active .nav-link::after {
            display: none;
        }

        .navbar-modern .nav-link {
            padding: 10px 0 !important;
        }

        #btnEnvLogin,
        #btnEnvLogout {
            margin-left: 0;
            margin-top: 10px;
            display: inline-block !important;
        }
    }
</style>

<nav class="navbar navbar-expand-lg navbar-light sticky-top navbar-modern">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('index') }}">
            <img src="{{ asset('images/asa.png') }}" height="35" class="d-inline-block align-top" alt="ASA Logo">
        </a>

        <button class="navbar-toggler border-0" type="button" data-toggle="collapse"
            data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item <?= Route::is('products.*') ? 'active' : '' ?>">
                    <a class="nav-link" href="{{ route('products.index') }}">
                        <i class="fas fa-cube mr-1"></i>Product
                    </a>
                </li>
                <li class="nav-item <?= Route::is('stock.*') ? 'active' : '' ?>">
                    <a class="nav-link" href="{{ route('stock.index') }}">
                        <i class="fas fa-cubes mr-1"></i>Stock
                    </a>
                </li>
                <li class="nav-item <?= Route::is('qc.*') ? 'active' : '' ?>">
                    <a class="nav-link" href="{{ route('qc.index') }}">
                        <i class="fas fa-clipboard-check mr-1"></i>QC
                    </a>
                </li>
                <li class="nav-item <?= Route::is('alamat_baru.*') ? 'active' : '' ?>">
                    <a class="nav-link" href="{{ route('alamat_baru.index') }}">
                        <i class="fas fa-shipping-fast mr-1"></i>Alamat Baru
                    </a>
                </li>
                <li class="nav-item <?= Route::is('basts.*') ? 'active' : '' ?>">
                    <a class="nav-link" href="{{ route('basts.index') }}">
                        <i class="fas fa-file-contract mr-1"></i>BAST
                    </a>
                </li>
                <li class="nav-item <?= Route::is('packs.*') ? 'active' : '' ?>">
                    <a class="nav-link" href="{{ route('packs.index') }}">
                        <i class="fas fa-list-ol mr-1"></i>PL
                    </a>
                </li>
                <li class="nav-item <?= Route::is('sops.*') ? 'active' : '' ?>">
                    <a class="nav-link" href="{{ route('sops.index') }}">
                        <i class="fas fa-layer-group mr-1"></i>SOP
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fas fa-database mr-1"></i>Odoo
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('po.index') }}"><i
                                class="fas fa-file-invoice mr-1 text-primary"></i>Purchase Order (PO)</a>
                        <a class="dropdown-item" href="{{ route('ri.index') }}"><i
                                class="fas fa-receipt mr-1 text-success"></i>Reception (RI)</a>
                        <a class="dropdown-item" href="{{ route('so.index') }}"><i
                                class="fas fa-shopping-cart mr-1 text-info"></i>Sales Order (SO)</a>
                        <a class="dropdown-item" href="{{ route('do.index') }}"><i
                                class="fas fa-truck mr-1 text-warning"></i>Delivery Order (DO)</a>
                        <a class="dropdown-item" href="{{ route('it.index') }}"><i
                                class="fas fa-exchange-alt mr-1 text-secondary"></i>Internal Transfer (IT)</a>
                    </div>
                </li>
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="javascript:void(0);" class="nav-link d-none" id="btnEnvLogin" data-toggle="modal"
                        data-target="#authModal">
                        <i class="fa fa-lock mr-1"></i> Login
                    </a>
                    <a href="javascript:void(0);" class="nav-link d-none" id="btnEnvLogout">
                        <i class="fa fa-cog mr-1"></i> Server Config
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

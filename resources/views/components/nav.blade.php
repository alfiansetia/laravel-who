<nav class="navbar navbar-expand-lg navbar-light" style="background-color: #e3f2fd;">
    <a class="navbar-brand" href="{{ route('home') }}">
        <img src="{{ asset('images/asa.png') }}" height="30" class="d-inline-block align-top" alt="">
        {{-- <b>SAVE</b> --}}
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item <?= $title == 'Dashboard' ? 'active' : '' ?>">
                <a class="nav-link" href="{{ route('home') }}">Home</a>
            </li>
            <li class="nav-item <?= $title == 'Data Product' ? 'active' : '' ?>">
                <a class="nav-link" href="{{ route('product.index') }}">Product</a>
            </li>
            <li class="nav-item <?= $title == 'List Alamat' ? 'active' : '' ?>">
                <a class="nav-link" href="{{ route('alamat.index') }}">Alamat</a>
            </li>
            <li class="nav-item <?= $title == 'Data Stock' ? 'active' : '' ?>">
                <a class="nav-link" href="{{ route('stock.index') }}">Stock</a>
            </li>
            <li class="nav-item <?= $title == 'Data Kontak' ? 'active' : '' ?>">
                <a class="nav-link" href="{{ route('kontak.index') }}">Kontak</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="javascript:void(0);" data-toggle="modal" data-target="#modal_env">Setting</a>
            </li>
        </ul>
    </div>
</nav>

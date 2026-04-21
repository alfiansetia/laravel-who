@extends('template', ['title' => 'Home'])

@push('css')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.4);
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        }

        body {

            font-family: 'Outfit', sans-serif;
            color: #1e293b;
            min-height: 100vh;
        }

        .page-header {
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .search-container {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 1rem 1.5rem 1rem 3.5rem;
            border-radius: 50px;
            border: 2px solid var(--glass-border);
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }

        .search-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.2);
            transform: translateY(-2px);
        }

        .search-icon {
            position: absolute;
            left: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6366f1;
        }

        .section-title {
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #475569;
            padding-left: 0.5rem;
            border-left: 4px solid #6366f1;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1.25rem;
            margin-bottom: 3rem;
        }

        .menu-card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 160px;
        }

        .menu-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .menu-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            border-color: rgba(99, 102, 241, 0.3);
            background: #fff;
        }

        .menu-card:hover::before {
            opacity: 1;
        }

        .icon-wrapper {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .card-title {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.25rem;
            color: #1e293b;
        }

        .card-desc {
            font-size: 0.75rem;
            color: #64748b;
            line-height: 1.2;
        }

        /* Category Specific Styles */
        .cat-common .icon-wrapper {
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
        }

        .cat-odoo .icon-wrapper {
            background: rgba(168, 85, 247, 0.1);
            color: #a855f7;
        }

        .cat-tools .icon-wrapper {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .cat-other .icon-wrapper {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .menu-card:hover .icon-wrapper {
            transform: scale(1.1) rotate(5deg);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade {
            animation: fadeIn 0.6s ease forwards;
        }

        /* Hide items that don't match search */
        .menu-card.hidden {
            display: none;
        }

        .section-wrapper.hidden {
            display: none;
        }
    </style>
@endpush

@section('content')
    <div class="container py-4">
        <!-- Header & Search -->
        <div class="page-header text-center animate-fade">
            <h1 class="font-weight-bold mb-2" style="letter-spacing: -1px; color: #1e293b;">Selamat Datang Kembali</h1>
            <p class="text-muted mb-4 text-center">Akses cepat ke modul dan peralatan kerja Anda</p>

            <div class="search-container">
                <i data-lucide="search" class="search-icon" width="20"></i>
                <input type="search" id="menuSearch" class="search-input"
                    placeholder="Cari menu atau aplikasi (misal: Stock, PO, STT)..." autofocus>
            </div>
        </div>

        <!-- Section: Menu Utama -->
        <div class="section-wrapper animate-fade" style="animation-delay: 0.1s">
            <h3 class="section-title">
                <i data-lucide="layout-grid" width="20"></i> Menu Utama
            </h3>
            <div class="menu-grid">
                @php
                    $mainMenus = [
                        [
                            'route' => 'products.index',
                            'icon' => 'box',
                            'title' => 'Product',
                            'desc' => 'Daftar master produk',
                        ],
                        [
                            'route' => 'alamats.index',
                            'icon' => 'map-pin',
                            'title' => 'Alamat',
                            'desc' => 'Master data alamat',
                        ],
                        [
                            'route' => 'alamat_baru.index',
                            'icon' => 'navigation',
                            'title' => 'Alamat Baru',
                            'desc' => 'Input alamat baru',
                        ],
                        [
                            'route' => 'basts.index',
                            'icon' => 'file-text',
                            'title' => 'BASTUF',
                            'desc' => 'Berita acara serah terima',
                        ],
                        [
                            'route' => 'kontaks.index',
                            'icon' => 'user-plus',
                            'title' => 'Kontak',
                            'desc' => 'Manajemen data kontak',
                        ],
                        [
                            'route' => 'packs.index',
                            'icon' => 'list-checks',
                            'title' => 'Packing List',
                            'desc' => 'Daftar packing list',
                        ],
                        [
                            'route' => 'sops.index',
                            'icon' => 'shield-check',
                            'title' => 'SOP QC',
                            'desc' => 'Standard prosedur QC',
                        ],
                        [
                            'route' => 'kargans.index',
                            'icon' => 'truck',
                            'title' => 'Kargan',
                            'desc' => 'Manajemen logistik kargan',
                        ],
                        [
                            'route' => 'atk.index',
                            'icon' => 'pen-tool',
                            'title' => 'ATK',
                            'desc' => 'Inventaris alat tulis kantor',
                        ],
                        [
                            'route' => 'product_images.index',
                            'icon' => 'image',
                            'title' => 'Images',
                            'desc' => 'Gallery foto produk',
                        ],
                        [
                            'route' => 'qc_lots.index',
                            'icon' => 'package-search',
                            'title' => 'QC Lot',
                            'desc' => 'Pengecekan lot QC',
                        ],
                        [
                            'route' => 'problems.index',
                            'icon' => 'alert-circle',
                            'title' => 'Problem',
                            'desc' => 'Manajemen masalah produk',
                        ],
                    ];
                @endphp

                @foreach ($mainMenus as $menu)
                    <div class="menu-card cat-common" onclick="window.location='{{ route($menu['route']) }}'">
                        <div class="icon-wrapper">
                            <i data-lucide="{{ $menu['icon'] }}" width="28"></i>
                        </div>
                        <div class="card-title">{{ $menu['title'] }}</div>
                        <div class="card-desc">{{ $menu['desc'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Section: Odoo Menu -->
        <div class="section-wrapper animate-fade" style="animation-delay: 0.2s">
            <h3 class="section-title" style="border-color: #a855f7;">
                <i data-lucide="cloud" width="20"></i> Odoo ERP
            </h3>
            <div class="menu-grid">
                @php
                    $odooMenus = [
                        [
                            'route' => 'stock.index',
                            'icon' => 'boxes',
                            'title' => 'Stock',
                            'desc' => 'Pantau stok barang',
                        ],
                        ['route' => 'po.index', 'icon' => 'shopping-cart', 'title' => 'PO', 'desc' => 'Purchase order'],
                        [
                            'route' => 'ri.index',
                            'icon' => 'arrow-down-to-line',
                            'title' => 'RI',
                            'desc' => 'Receiving inventory',
                        ],
                        [
                            'route' => 'it.index',
                            'icon' => 'refresh-cw',
                            'title' => 'IT',
                            'desc' => 'Inventory transfer',
                        ],
                        ['route' => 'so.index', 'icon' => 'shopping-bag', 'title' => 'SO', 'desc' => 'Sales order'],
                        ['route' => 'do.index', 'icon' => 'package-open', 'title' => 'DO', 'desc' => 'Delivery order'],
                        [
                            'route' => 'vendors.index',
                            'icon' => 'store',
                            'title' => 'Vendor',
                            'desc' => 'Daftar vendor aktif',
                        ],
                    ];
                @endphp

                @foreach ($odooMenus as $menu)
                    <div class="menu-card cat-odoo" onclick="window.location='{{ route($menu['route']) }}'">
                        <div class="icon-wrapper">
                            <i data-lucide="{{ $menu['icon'] }}" width="28"></i>
                        </div>
                        <div class="card-title">{{ $menu['title'] }}</div>
                        <div class="card-desc">{{ $menu['desc'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Section: Tools -->
        <div class="section-wrapper animate-fade" style="animation-delay: 0.3s">
            <h3 class="section-title" style="border-color: #f59e0b;">
                <i data-lucide="wrench" width="20"></i> Peralatan Kerja
            </h3>
            <div class="menu-grid">
                @php
                    $toolMenus = [
                        [
                            'route' => 'tools.kalkulator',
                            'icon' => 'calculator',
                            'title' => 'Kalkulator',
                            'desc' => 'Hitung nilai barang',
                        ],
                        [
                            'route' => 'qc.index',
                            'icon' => 'file-check',
                            'title' => 'Form QC',
                            'desc' => 'Generate form pengecekan',
                        ],
                        [
                            'route' => 'tools.sn',
                            'icon' => 'barcode',
                            'title' => 'SN',
                            'desc' => 'Generate serial number',
                        ],
                        [
                            'route' => 'tools.laporan_pengiriman',
                            'icon' => 'file-spreadsheet',
                            'title' => 'Kiriman',
                            'desc' => 'Laporan kiriman harian',
                        ],
                        [
                            'route' => 'tools.laporan_luarkota',
                            'icon' => 'map',
                            'title' => 'Luarkota',
                            'desc' => 'Laporan kiriman luar kota',
                        ],
                        [
                            'route' => 'tools.print_resi',
                            'icon' => 'printer',
                            'title' => 'Print Resi',
                            'desc' => 'Cetak resi pengiriman',
                        ],
                        [
                            'route' => 'tools.spreadsheet',
                            'icon' => 'table-2',
                            'title' => 'PLTBB',
                            'desc' => 'Spreadsheet PLTBB',
                        ],
                        [
                            'route' => 'settings.index',
                            'icon' => 'settings',
                            'title' => 'Setting',
                            'desc' => 'Pengaturan aplikasi',
                        ],
                    ];
                @endphp

                @foreach ($toolMenus as $menu)
                    <div class="menu-card cat-tools" onclick="window.location='{{ route($menu['route']) }}'">
                        <div class="icon-wrapper">
                            <i data-lucide="{{ $menu['icon'] }}" width="28"></i>
                        </div>
                        <div class="card-title">{{ $menu['title'] }}</div>
                        <div class="card-desc">{{ $menu['desc'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Section: Other -->
        <div class="section-wrapper animate-fade" style="animation-delay: 0.4s">
            <h3 class="section-title" style="border-color: #10b981;">
                <i data-lucide="sparkles" width="20"></i> Teknologi Lainnya
            </h3>
            <div class="menu-grid">
                @php
                    $otherMenus = [
                        ['route' => 'tools.stt', 'icon' => 'mic', 'title' => 'STT', 'desc' => 'Ucapan ke teks'],
                        [
                            'route' => 'tools.ocr',
                            'icon' => 'scan-text',
                            'title' => 'OCR',
                            'desc' => 'Ekstrak teks gambar',
                        ],
                        [
                            'route' => 'tools.scoreboard',
                            'icon' => 'trophy',
                            'title' => 'Scoreboard',
                            'desc' => 'Papan skor digital',
                        ],
                    ];
                @endphp

                @foreach ($otherMenus as $menu)
                    <div class="menu-card cat-other" onclick="window.location='{{ route($menu['route']) }}'">
                        <div class="icon-wrapper">
                            <i data-lucide="{{ $menu['icon'] }}" width="28"></i>
                        </div>
                        <div class="card-title">{{ $menu['title'] }}</div>
                        <div class="card-desc">{{ $menu['desc'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('js')
    <!-- CDN Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        // Init Lucide
        lucide.createIcons();

        // Search Functionality
        $(document).ready(function() {
            $('#menuSearch').on('keyup', function() {
                var value = $(this).val().toLowerCase();

                $('.menu-card').each(function() {
                    var title = $(this).find('.card-title').text().toLowerCase();
                    var desc = $(this).find('.card-desc').text().toLowerCase();
                    var match = title.indexOf(value) > -1 || desc.indexOf(value) > -1;

                    $(this).toggleClass('hidden', !match);
                });

                // Hide section if no items match
                $('.section-wrapper').each(function() {
                    var visibleCards = $(this).find('.menu-card:not(.hidden)').length;
                    $(this).toggleClass('hidden', visibleCards === 0);
                });
            });
        });
    </script>
@endpush

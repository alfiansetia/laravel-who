@extends('template', ['title' => 'Cetak Kolase Foto'])

@push('css')
    <style>
        :root {
            --a4-width: 210mm;
            --a4-height: 297mm;
        }

        @media screen {
            body {
                background: #f0f2f5;
            }

            .paper-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 40px 20px;
                gap: 30px;
            }

            .paper {
                background: white;
                width: var(--a4-width);
                min-height: var(--a4-height);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
                padding: 10mm;
                position: relative;
                margin-bottom: 40px;
                transition: transform 0.3s ease;
            }

            .collage-controls {
                position: sticky;
                top: 20px;
                z-index: 1000;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                padding: 20px;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                margin-bottom: 20px;
                width: 100%;
                max-width: var(--a4-width);
                border: 1px solid rgba(0, 0, 0, 0.05);
            }
        }

        @media print {
            @page {
                size: A4;
                margin: 0;
            }

            body {
                background: white;
                margin: 0;
                padding: 0;
            }

            .collage-controls,
            .content-wrapper>.content-header,
            .breadcrumb,
            footer,
            nav,
            .main-sidebar,
            .navbar,
            .btn,
            #notif,
            .main-header,
            .main-footer {
                display: none !important;
            }

            .content-wrapper {
                margin: 0 !important;
                padding: 0 !important;
            }

            .paper-container {
                padding: 0 !important;
                display: block !important;
                margin: 0 !important;
            }

            .paper {
                width: 100% !important;
                height: 100vh !important;
                margin: 0 !important;
                padding: 10mm !important;
                box-shadow: none !important;
                page-break-after: always;
            }
        }

        .collage-grid {
            display: grid;
            gap: 5mm;
            width: 100%;
            height: 100%;
        }

        .collage-item {
            position: relative;
            width: 100%;
            overflow: hidden;
            border: 1px solid #eee;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .collage-item img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .collage-item .product-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.8);
            color: #333;
            font-size: 10px;
            padding: 4px;
            text-align: center;
            font-weight: 600;
            border-top: 1px solid #eee;
        }

        /* Dynamic classes based on columns */
        .cols-1 {
            grid-template-columns: repeat(1, 1fr);
        }

        .cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .cols-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .cols-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        .cols-5 {
            grid-template-columns: repeat(5, 1fr);
        }

        .btn-print {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .btn-print:hover {
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 4px 12px rgba(118, 75, 162, 0.3);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid paper-container">
        <div class="collage-controls d-print-none">
            @if ($images->count() > 0)
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="font-weight-bold mb-1 text-primary">
                            <i class="fas fa-images mr-2"></i>Kolase Foto Produk
                        </h5>
                        <p class="text-muted small mb-0">
                            <strong>{{ $images->first()->product->name }}</strong>
                            <span class="mx-2">|</span>
                            Kode: <code>{{ $images->first()->product->code ?? '-' }}</code>
                        </p>
                    </div>
                    <div>
                        <span class="badge badge-info">{{ $images->count() }} Gambar</span>
                    </div>
                </div>
            @endif
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">JUMLAH KOLOM</label>
                        <select id="columnSelect" class="form-control form-control-sm">
                            <option value="1">1 Kolom</option>
                            <option value="2" selected>2 Kolom</option>
                            <option value="3">3 Kolom</option>
                            <option value="4">4 Kolom</option>
                            <option value="5">5 Kolom</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">TINGGI FOTO</label>
                        <select id="heightSelect" class="form-control form-control-sm">
                            <option value="auto">Otomatis</option>
                            <option value="200px">Kecil (200px)</option>
                            <option value="300px">Sedang (300px)</option>
                            <option value="400px">Besar (400px)</option>
                            <option value="fill">Penuhi Baris</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <button type="button" class="btn btn-light btn-sm mr-2" onclick="window.close()">
                        <i class="fas fa-times mr-1"></i> Tutup Tab
                    </button>
                    <button class="btn btn-print" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Cetak Kolase
                    </button>
                </div>
            </div>
        </div>

        <div class="paper" id="paperContent">
            <div class="collage-grid cols-2" id="collageGrid">
                @foreach ($images as $img)
                    <div class="collage-item" style="height: 300px;">
                        <img src="{{ $img->url }}" alt="{{ $img->name }}">
                    </div>
                @endforeach
                @if ($images->count() == 0)
                    <div class="alert alert-info w-100">
                        Tidak ada foto yang dipilih untuk dicetak.
                    </div>
                @endif
            </div>
        </div>
    @endsection

    @push('js')
        <script>
            $(document).ready(function() {
                $('#columnSelect').on('change', function() {
                    const cols = $(this).val();
                    $('#collageGrid').removeClass('cols-1 cols-2 cols-3 cols-4 cols-5')
                        .addClass('cols-' + cols);
                    updateLayout();
                });

                $('#heightSelect').on('change', function() {
                    updateLayout();
                });

                function updateLayout() {
                    const height = $('#heightSelect').val();
                    const cols = parseInt($('#columnSelect').val());
                    const totalItems = {{ $images->count() }};
                    const rows = Math.ceil(totalItems / cols);

                    if (height === 'fill') {
                        // Try to fill the A4 height (297mm - 20mm padding)
                        const availableHeight = 277; // mm approx
                        const rowHeight = availableHeight / rows;
                        $('.collage-item').css('height', rowHeight + 'mm');
                    } else if (height === 'auto') {
                        $('.collage-item').css('height', 'auto');
                        $('.collage-item').css('padding', '10px 0');
                    } else {
                        $('.collage-item').css('height', height);
                    }
                }

                // Initial call
                updateLayout();
            });
        </script>
    @endpush

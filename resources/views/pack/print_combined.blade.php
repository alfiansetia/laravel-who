<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOP & Pack List - {{ $pack->product->code ?? '-' }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f0f0f0;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 5mm 10mm;
            margin: 10px auto;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
            box-sizing: border-box;
        }

        .copy-separator {
            border-top: 1px dashed #ccc;
            margin: 20px 0;
            width: 210mm;
            margin-left: auto;
            margin-right: auto;
        }

        .section-separator {
            border-top: 2px solid #000;
            margin: 15px 0;
            position: relative;
        }

        .page-break {
            page-break-before: always;
        }

        .section-title {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 0 10px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 14px;
        }

        .header-kop {
            margin-bottom: 2px;
            padding-bottom: 2px;
        }

        .header-kop img {
            height: 50px;
            width: auto;
            max-width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 2px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            letter-spacing: 2px;
        }

        .info-section {
            margin-bottom: 5px;
            font-size: 13px;
        }

        .info-row {
            display: flex;
            margin-bottom: 2px;
            line-height: 1.2;
        }

        .info-label {
            width: 90px;
            font-weight: bold;
        }

        .info-colon {
            width: 15px;
            font-weight: bold;
            text-align: center;
        }

        .info-value {
            flex: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 13px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 4px 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        @media print {
            @page {
                size: A4;
                margin: 0;
            }

            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .page {
                margin: 0;
                padding: 5mm;
                padding-right: 8mm;
                box-shadow: none;
                width: 100%;
                height: auto;
            }

            .no-print {
                display: none !important;
            }

            .page-break-active {
                page-break-before: always;
                margin-top: 0;
            }
        }

        .section-spacer {
            height: 40px;
        }

        #pack-section.page-break-active .section-spacer {
            display: none;
        }

        .controls {
            width: 210mm;
            margin: 0 auto 10px auto;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 15px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            border: none;
        }

        .btn-print {
            background-color: #007bff;
            color: white;
        }

        .btn-close {
            background-color: #6c757d;
            color: white;
        }

        .option-group {
            margin-right: auto;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: normal;
        }
    </style>
</head>

<body>
    <div class="controls no-print">
        <div class="option-group">
            <input type="checkbox" id="toggle-page-break" style="width: 18px; height: 18px; cursor: pointer;">
            <label for="toggle-page-break" style="cursor: pointer; margin-bottom: 0;">Pisahkan Halaman (SOP & PL)</label>
        </div>
        <button class="btn btn-close" onclick="window.close()">Tutup</button>
        <button class="btn btn-print" onclick="window.print()">Cetak</button>
    </div>

    <div id="print-container">
        <div class="page">
            <!-- SOP SECTION -->
            <div class="section-container" id="sop-section">
                <div class="header-kop">
                    <img src="{{ asset('images/map.png') }}" alt="Kop Map">
                </div>

                <div class="info-section">
                    <div class="info-row">
                        <div class="info-label">Kode Barang</div>
                        <div class="info-colon">:</div>
                        <div class="info-value">{{ $pack->product->code ?? '-' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Nama Barang</div>
                        <div class="info-colon">:</div>
                        <div class="info-value">{{ $pack->product->name ?? '-' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Target</div>
                        <div class="info-colon">:</div>
                        <div class="info-value">{{ $sop->target ?? '-' }}</div>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 30px;">No</th>
                            <th>Pengerjaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($sop && $sop->items->count() > 0)
                            @foreach ($sop->items as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $item->item }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="2" class="text-center">Tidak ada item SOP.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- PACK SECTION -->
            <div class="section-container" id="pack-section">
                <!-- Spacer if not page breaking, so they don't stick too close -->
                <div class="section-spacer"></div>

                <div class="header-kop">
                    <img src="{{ asset('images/map.png') }}" alt="Kop Map">
                </div>

                <div class="info-section">
                    <div class="info-row">
                        <div class="info-label">Pabrikan</div>
                        <div class="info-colon">:</div>
                        <div class="info-value">
                            {{ $pack->vendor->name ?? '-' }}
                            @if ($pack->vendor_desc)
                                ({{ $pack->vendor_desc }})
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Produk</div>
                        <div class="info-colon">:</div>
                        <div class="info-value">
                            <strong>{{ $pack->product->code ?? '-' }}</strong> {{ $pack->product->name ?? '-' }}
                            @if ($pack->desc)
                                ({{ $pack->desc }})
                            @endif
                        </div>
                    </div>
                </div>

                <div class="header">
                    <h1>Part List</h1>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 30px;">No</th>
                            <th>Item</th>
                            <th style="width: 80px;">Quantity</th>
                            <th style="width: 80px;">Check List</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pack->items as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $item->item }}</td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td class="text-center">&nbsp;</td>
                            </tr>
                        @endforeach
                        @if ($pack->items->count() == 0)
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada item Packing List.</td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4"
                                style="border: none; text-align: right; font-size: 10px; font-style: italic; padding-top: 10px;">
                                {{ config('cdakb.pack') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <script>
        const togglePageBreak = document.getElementById('toggle-page-break');
        const packSection = document.getElementById('pack-section');

        togglePageBreak.addEventListener('change', function() {
            if (this.checked) {
                packSection.classList.add('page-break-active');
            } else {
                packSection.classList.remove('page-break-active');
            }
        });
    </script>
</body>

</html>

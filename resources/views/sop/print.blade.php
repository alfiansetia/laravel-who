<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOP QC - {{ $sop->product->code ?? '-' }}</title>
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

        .header {
            text-align: center;
            margin-bottom: 2px;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            letter-spacing: 2px;
        }

        .info-section {
            margin-bottom: 2px;
            font-size: 14px;
        }

        .info-row {
            display: flex;
            margin-bottom: 2px;
            line-height: 1.2;
        }

        .info-label {
            width: 95px;
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
            margin-bottom: 20px;
            font-size: 14px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
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
                box-shadow: none;
                width: 100%;
                height: auto;
                min-height: 0;
            }

            .no-print {
                display: none !important;
            }
        }

        .controls {
            width: 210mm;
            margin: 0 auto 10px auto;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
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
    </style>
</head>

<body>
    <div class="controls no-print">
        <button class="btn btn-close" onclick="window.close()">Tutup</button>
        <button class="btn btn-print" onclick="window.print()">Cetak</button>
    </div>

    <div class="page">
        <div class="header-kop" style="margin-bottom: 2px; padding-bottom: 2px; ">
            <img src="{{ asset('images/map.png') }}" alt="Kop Map" style="height: 60px; width: auto; max-width: 100%;">
        </div>

        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Kode Barang</div>
                <div class="info-colon">:</div>
                <div class="info-value">
                    {{ $sop->product->code ?? '-' }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Nama Barang</div>
                <div class="info-colon">:</div>
                <div class="info-value">
                    {{ $sop->product->name ?? '-' }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Target</div>
                <div class="info-colon">:</div>
                <div class="info-value">
                    {{ $sop->target ?? '-' }}
                </div>
            </div>
            {{-- <div class="header">
                <h1>SOP QC</h1>
            </div> --}}
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 2px;">No</th>
                    <th>Pengerjaan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sop->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->item }}</td>
                    </tr>
                @endforeach
                @if ($sop->items->count() == 0)
                    <tr>
                        <td colspan="2" class="text-center">Tidak ada item SOP.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</body>

</html>

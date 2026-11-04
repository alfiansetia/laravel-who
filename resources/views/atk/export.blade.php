<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Stok - {{ $data->code ?? '' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/asa.png') }}" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            padding: 20px;
            background: white;
            color: #000;
        }

        .container {
            max-width: 105mm;
            margin: 0 auto;
            background: white;
        }

        .doc-title {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            margin: 10px 0 12px;
            letter-spacing: 1px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 4px;
            margin-bottom: 12px;
            font-size: 10pt;
        }

        .info-row {
            display: flex;
        }

        .info-label {
            width: 110px;
            flex-shrink: 0;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        thead {
            display: table-header-group;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px 8px;
        }

        th {
            font-weight: bold;
            text-align: center;
            background: #f2f2f2;
        }

        td.text-center {
            text-align: center;
        }

        td.text-right,
        th.text-right {
            text-align: right;
        }

        tr.total-row td {
            font-weight: bold;
            background: #f2f2f2;
        }

        tr.blank-row td {
            height: 22px;
        }

        .noprint {
            background: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .noprint button {
            padding: 8px 16px;
            cursor: pointer;
            border-radius: 4px;
            border: 1px solid #ccc;
            background: white;
            font-size: 10pt;
            font-weight: bold;
        }

        .noprint button:hover {
            background: #e9ecef;
        }

        .btn-print {
            background: #28a745 !important;
            color: white !important;
            border-color: #28a745 !important;
        }

        .btn-close {
            background: #6c757d !important;
            color: white !important;
            border-color: #6c757d !important;
        }

        @media print {
            .noprint {
                display: none !important;
            }

            body {
                padding: 10mm 12mm !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .container {
                max-width: 100%;
            }

            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
</head>

<body>
    @php
        $saldo = 0;
        $totalIn = 0;
        $totalOut = 0;
        $tahun = now()->year;
        if (($data->transactions ?? collect())->isNotEmpty()) {
            $tahun = substr($data->transactions->first()->date, 0, 4);
        }
    @endphp
    <div class="noprint">
        <button onclick="window.print()" class="btn-print">Print</button>
        <button onclick="window.close()" class="btn-close">Close</button>
    </div>
    <div class="container">
        <div class="doc-title">KARTU STOK</div>

        <div class="info-grid">
            <div class="info-row"><span class="info-label">Tahun</span><span>: {{ $tahun }}</span></div>
            <div class="info-row"><span class="info-label">Kode</span><span>: {{ $data->code }}</span></div>
            <div class="info-row"><span class="info-label">Nama Barang</span><span>: {{ $data->name }}</span></div>
            <div class="info-row"><span class="info-label">Satuan</span><span>: {{ $data->satuan }}</span></div>
        </div>

        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 68px;">Tgl</th>
                    <th rowspan="2">PIC</th>
                    <th colspan="2">Mutasi</th>
                    <th rowspan="2" style="width: 52px;">Saldo</th>
                    <th rowspan="2">Keterangan</th>
                </tr>
                <tr>
                    <th style="width: 46px;">IN</th>
                    <th style="width: 46px;">OUT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data->transactions as $trx)
                    @php
                        if ($trx->type == 'in') {
                            $saldo += $trx->qty;
                            $totalIn += $trx->qty;
                        } else {
                            $saldo -= $trx->qty;
                            $totalOut += $trx->qty;
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ \Carbon\Carbon::parse($trx->date)->format('d/m/Y') }}</td>
                        <td>{{ $trx->pic }}</td>
                        <td class="text-center">{{ $trx->type == 'in' ? number_format($trx->qty) : '' }}</td>
                        <td class="text-center">{{ $trx->type == 'out' ? number_format($trx->qty) : '' }}</td>
                        <td class="text-center">{{ number_format($saldo) }}</td>
                        <td>{{ $trx->desc }}</td>
                    </tr>
                @endforeach
                @for ($i = 0; $i < 8; $i++)
                    <tr class="blank-row">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
                <tr class="total-row">
                    <td colspan="2" class="text-center">TOTAL</td>
                    <td class="text-center">{{ number_format($totalIn) }}</td>
                    <td class="text-center">{{ number_format($totalOut) }}</td>
                    <td class="text-center">{{ number_format($saldo) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

    </div>
</body>

</html>

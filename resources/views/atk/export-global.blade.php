<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Stok ATK - {{ $year }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/asa.png') }}" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.3;
            padding: 16px;
            background: white;
            color: #000;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
        }

        .doc-title {
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            margin: 8px 0 2px;
            letter-spacing: 1px;
        }

        .doc-subtitle {
            font-size: 9pt;
            text-align: center;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }

        thead {
            display: table-header-group;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 3px 4px;
        }

        th {
            font-weight: bold;
            text-align: center;
            background: #f2f2f2;
        }

        td.text-center {
            text-align: center;
        }

        td.mono {
            font-family: monospace;
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
            font-size: 10pt;
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

        .btn-excel {
            background: #217346 !important;
            color: white !important;
            border-color: #217346 !important;
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
                padding: 8mm 10mm !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .container {
                max-width: 100%;
            }

            @page {
                size: A4 landscape;
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div class="noprint">
        <button onclick="window.print()" class="btn-print">Print</button>
        <a href="/atk-eksport-global-xlsx?year={{ $year }}"><button class="btn-excel">Download Excel</button></a>
        <button onclick="window.close()" class="btn-close">Close</button>
    </div>
    <div class="container">
        <div class="doc-title">REKAP STOK ATK</div>
        <div class="doc-subtitle">Tahun {{ $year }}</div>

        <table>
            <thead>
                <tr>
                    <th rowspan="2">Kode</th>
                    <th rowspan="2">Nama</th>
                    @foreach ($shortMonths as $mon)
                        <th colspan="2">{{ $mon }}</th>
                    @endforeach
                    <th rowspan="2">TOTAL IN</th>
                    <th rowspan="2">TOTAL OUT</th>
                    <th rowspan="2">SISA</th>
                </tr>
                <tr>
                    @for ($m = 1; $m <= 12; $m++)
                        <th>IN</th>
                        <th>OUT</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    @php
                        $totIn = 0;
                        $totOut = 0;
                        for ($mm = 1; $mm <= 12; $mm++) {
                            $kk = sprintf('%02d', $mm);
                            $totIn += (int) $item->{'in_'.$kk};
                            $totOut += (int) $item->{'out_'.$kk};
                        }
                    @endphp
                    <tr>
                        <td class="mono">{{ $item->code }}</td>
                        <td>{{ $item->name }}</td>
                        @for ($m = 1; $m <= 12; $m++)
                            @php $mm = sprintf('%02d', $m); @endphp
                            <td class="text-center">{{ $item->{'in_'.$mm} ? number_format($item->{'in_'.$mm}) : '' }}</td>
                            <td class="text-center">{{ $item->{'out_'.$mm} ? number_format($item->{'out_'.$mm}) : '' }}</td>
                        @endfor
                        <td class="text-center">{{ $totIn ? number_format($totIn) : '' }}</td>
                        <td class="text-center">{{ $totOut ? number_format($totOut) : '' }}</td>
                        <td class="text-center">{{ number_format((int) $item->all_in - (int) $item->all_out) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="29" class="text-center">Belum ada data ATK.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>

</html>

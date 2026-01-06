<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Delivery Order - {{ $data['name'] ?? 'Dummy-DO' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9pt;
            line-height: 1.15;
            background: white;
            color: #000;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        /* Top Header Logo */
        .top-logo-row {
            margin-bottom: 5px;
        }

        .logo-img {
            height: 40px;
        }

        /* Unified Header Grid */
        .header-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 0.8fr;
            gap: 10px;
            margin-bottom: 10px;
        }

        .address-box {
            border: 1px solid #000;
            padding: 5px;
            font-size: 9pt;
        }

        .box-title {
            font-weight: bold;
            margin-bottom: 2px;
        }

        /* DO Info Table (Right Column, Spans 2 rows) */
        .do-info-container {
            grid-row: span 2;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .do-title {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
            width: 100%;
            text-align: right;
        }

        .do-info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .do-info-table th,
        .do-info-table td {
            border: 1px solid #000;
            padding: 2px 4px;
            text-align: center;
        }

        .do-info-table th {
            font-weight: bold;
            border-bottom: none;
            /* Hilangkan garis bawah judul */
        }

        .do-info-table td {
            border-top: none;
            /* Hilangkan garis atas value */
        }

        /* Main Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 20px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 3px 5px;
            font-size: 9pt;
            word-wrap: break-word;
        }

        .items-table th {
            text-align: center;
            font-weight: bold;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        /* Footer Signatures */
        .signature-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            margin-top: 20px;
            text-align: center;
        }

        .sig-box {
            height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sig-line {
            width: 85%;
            margin: 0 auto;
            border-bottom: 1px solid #000;
        }

        .sig-date {
            text-align: left;
            padding-left: 10%;
            font-size: 8pt;
            margin-top: 1px;
        }

        .form-code {
            text-align: right;
            font-size: 8pt;
            margin-top: 10px;
        }

        @media print {
            body {
                padding: 8mm;
                padding-top: 2mm;
            }

            @page {
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Logo -->
        <div class="top-logo-row">
            <img src="{{ asset('images/map_do.png') }}" alt="Logo" class="logo-img">
        </div>

        <!-- Unified Header Section -->
        <div class="header-grid">
            <!-- Row 1, Col 1: PT MAP -->
            <div class="address-box">
                <div class="box-title">PT MITRA ASA PRATAMA</div>
                MTH Square Lt. 1 No. 6, Jl. Letjen M.T.<br>
                Haryono No.Kav. 10, RT.6/RW.12, Kp.<br>
                Melayu, Kecamatan Jatinegara<br>
                Jakarta Timur JK 13330<br>
                Indonesia
            </div>

            <!-- Row 1, Col 2: Ship To -->
            <div class="address-box">
                <div class="box-title">Ship To</div>
                @php
                    $add = [];
                    $partner = $data['partner_detail'] ?? [];
                    $add[] = $partner['street'] ?? '';
                    $add[] = $partner['city'] ?? '';
                    $add[] = $partner['state_id'][1] ?? '';
                    $add[] = $partner['country_id'][1] ?? '';
                    $manual = $data['delivery_manual'] ?? '';
                    if ($manual != '-' && !empty($manual)) {
                        $add[] = 'UP: ' . $manual;
                    }
                    $phone = $partner['phone'] ?? '-';
                    if ($phone != '-' && !empty($phone)) {
                        $add[] = 'Tlp: ' . $phone;
                    }
                @endphp
                {{ $partner['name'] ?? '' }}<br>{!! implode(', ', $add) !!}
            </div>

            <!-- Row 1 & 2, Col 3: DO Info Stack (Spans 2 rows) -->
            <div class="do-info-container">
                <div class="do-title">Delivery Order</div>
                <table class="do-info-table">
                    <tr>
                        <th style="width: 50%;"><u>Delivery Date</u></th>
                        <th><u>DO No</u></th>
                    </tr>
                    <tr>
                        <td>
                            {{ Arr::get($data, 'force_date', 'False') ? date('d/m/Y', strtotime($data['force_date'])) : ($data['date_done'] ? date('d/m/Y', strtotime($data['date_done'])) : '') }}
                        </td>
                        <td>{{ Arr::get($data, 'name', 'False') }}</td>
                    </tr>
                    <tr>
                        <th><u>PO No</u></th>
                        <th><u>Terms</u></th>
                    </tr>
                    <tr>
                        @php
                            $po = Arr::get($data, 'no_po', 'False');
                        @endphp
                        <td
                            style="vertical-align: top;max-width: 100px;
                        word-wrap: break-word;">
                            {{ $po }}</td>
                        @php
                            $terms = Arr::get($data, 'so_detail.payment_term_id.1', 'False');
                        @endphp
                        <td style="vertical-align: middle;">{{ $terms }}</td>
                    </tr>
                    <tr>
                        <th colspan="2"><u>ID Paket</u></th>
                    </tr>
                    <tr>
                        <td colspan="2">{{ Arr::get($data, 'no_aks', 'False') }}</td>
                    </tr>
                    <tr>
                        <th colspan="2"><u>Ship Via</u></th>
                    </tr>
                    <tr>
                        <td colspan="2">{{ Arr::get($data, 'ekspedisi_id.1', 'False') }}</td>
                    </tr>
                </table>
            </div>

            <!-- Row 2, Col 1: Bill To -->
            <div class="address-box">
                <div class="box-title">Bill To</div>
                @php
                    $bill = Arr::get($data, 'bill_to_detail', []);
                    $add = [];
                    $add[] = $bill['name'] ?? '';
                    $add[] = $bill['street'] ?? '';
                    $add[] = $bill['city'] ?? '';
                    $add[] = $bill['state_id'][1] ?? '';
                    $add[] = $bill['country_id'][1] ?? '';
                @endphp
                {!! implode(', ', $add) !!}
            </div>

            <!-- Row 2, Col 2: Description -->
            <div class="address-box">
                <div class="box-title">Description:</div>
                <div style="font-size: 8pt;">
                    {!! Arr::get($data, 'note_to_wh', '') !!}
                </div>
            </div>
        </div>

        <!-- Item Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 18%;">Item</th>
                    <th style="width: 27%;">Item Description</th>
                    <th style="width: 17%;">AKL</th>
                    <th style="width: 15%;">LOT/SN</th>
                    <th style="width: 10%;">ED</th>
                    <th style="width: 7%;">QTY</th>
                    <th style="width: 6%;">Unit</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $move_ids_detail = Arr::get($data, 'move_ids_detail', []);
                    $count = count($move_ids_detail);
                @endphp
                @foreach ($move_ids_detail as $line)
                    @php
                        $prod = pecah_code(Arr::get($line, 'product_id', ''));
                        $prod_lot = collect(Arr::get($data, 'move_line_detail', []))
                            ->where(function ($item) use ($line) {
                                return Arr::get($item, 'product_id.0') == Arr::get($line, 'product_id.0');
                            })
                            ->map(function ($item) {
                                $ed =
                                    isset($item['expired_date_do']) &&
                                    $item['expired_date_do'] &&
                                    $item['expired_date_do'] !== 'False'
                                        ? \Carbon\Carbon::createFromFormat(
                                            'Y-m-d H:i:s',
                                            $item['expired_date_do'],
                                            'UTC',
                                        )
                                            ->setTimezone(config('app.timezone'))
                                            ->format('d/m/Y')
                                        : '';
                                return [
                                    'lot' => $item['lot_id'][1] ?? null,
                                    'ed' => $ed,
                                    'qty' => $item['qty_done'] ?? 0,
                                ];
                            })
                            ->toArray();
                        $lotp = collect($prod_lot)->pluck('lot')->filter()->unique()->implode(', ');
                        $edp = collect($prod_lot)->pluck('ed')->filter()->unique()->implode(', ');
                    @endphp
                    <tr>
                        <td style="vertical-align: top;">{!! $prod[1] !!}{!! $count < 3 ? '<br><br>' : '' !!}</td>
                        <td style="vertical-align: top;">{!! $prod[2] !!}</td>
                        <td style="vertical-align: top;white-space: nowrap;" class="text-center">
                            {{ Arr::get($line, 'akl_id.1', '') }}</td>
                        <td style="vertical-align: top;" class="text-center">
                            {{ $with_lot ? $lotp : '' }}
                        </td>
                        <td style="vertical-align: top;" class="text-center">
                            {{ $with_lot ? $edp : '' }}</td>
                        <td style="vertical-align: top;" class="text-center">
                            {{ number_format(Arr::get($line, 'product_uom_qty', 0)) }}</td>
                        <td style="vertical-align: top;" class="text-center">
                            {{ Arr::get($line, 'product_uom.1', '') }}</td>
                    </tr>
                @endforeach


            </tbody>
        </table>

        <!-- Signatures -->
        <div class="signature-row">
            <div class="sig-box">
                <div>Prepared By</div>
                <div>
                    <div class="sig-line"></div>
                    <div class="sig-date">Date</div>
                </div>
            </div>
            <div class="sig-box">
                <div>Approved By</div>
                <div>
                    <div class="sig-line"></div>
                    <div class="sig-date">Date</div>
                </div>
            </div>
            <div class="sig-box">
                <div>PJT</div>
                <div>
                    <div class="sig-line"></div>
                    <div class="sig-date">Date</div>
                </div>
            </div>
            <div class="sig-box">
                <div>Shipped By</div>
                <div>
                    <div class="sig-line"></div>
                    <div class="sig-date">Date</div>
                </div>
            </div>
            <div class="sig-box">
                <div>Received By</div>
                <div>
                    <div class="sig-line"></div>
                    <div class="sig-date">Date</div>
                </div>
            </div>
        </div>

        <div class="form-code">FORM/WH/013/20.1</div>
    </div>

    <script>
        window.onload = function() {
            window.print();
            window.addEventListener('afterprint', function() {
                window.close();
            });
            let isPrinting = true;
            window.onbeforeprint = function() {
                isPrinting = true;
            };
            window.onfocus = function() {
                if (isPrinting) {
                    isPrinting = false;
                    setTimeout(function() {
                        window.close();
                    }, 100);
                }
            };
        }
    </script>
</body>

</html>

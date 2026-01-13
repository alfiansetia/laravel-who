<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Sales Order - {{ $data['name'] ?? '' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            padding: 20px;
            background: white;
        }

        .container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
        }

        /* Header Section */
        .header {
            display: flex;
            justify-content: space-between;
        }

        .header-left {
            flex: 1;
        }

        .logo {
            width: 150px;
            height: auto;
            margin-bottom: 10px;
        }

        .header-right {
            flex: 1;
            text-align: right;
        }

        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .company-address {
            font-size: 9pt;
            color: #000;
            line-height: 1.6;
        }

        /* Address Section */
        .address-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            gap: 30px;
        }

        .address-box {
            flex: 1;
        }

        .address-title {
            font-weight: bold;
            margin-bottom: 1px;
            font-size: 10pt;
        }

        .address-content {
            font-size: 9pt;
            line-height: 1.6;
            color: #333;
        }

        /* Order Info Section */
        .order-title {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: left;
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 5px 30px;
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            flex-direction: column;
            font-size: 9pt;
        }

        .info-label {
            font-weight: bold;
            margin-bottom: 3px;
        }

        .info-value {
            color: #333;
        }

        /* Note Section */
        .note-section {
            margin-bottom: 10px;
            padding: 8px;
            background: #f9f9f9;
            border-left: 3px solid #333;
        }

        .note-title {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 5px;
        }

        .note-content {
            font-size: 9pt;
            color: #333;
            white-space: pre-line;
        }

        /* Table Section */
        .table-container {
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        table th {
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #000;
        }

        table td {
            padding: 8px;
            border: 1px solid #000;
        }

        /* Spacer untuk halaman baru */
        .table-header-spacer {
            display: none;
        }

        table th.text-center,
        table td.text-center {
            text-align: center;
        }

        table th.text-right,
        table td.text-right {
            text-align: right;
        }

        /* Footer Combine Section */
        .footer-combine {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 10px;
            gap: 20px;
        }

        .summary-box {
            min-width: 250px;
            border: 1px solid #000;
        }

        .summary-row {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 10px;
            padding: 8px 12px;
            border-bottom: 1px solid #000;
            font-size: 10pt;
            align-items: center;
        }

        .summary-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 11pt;
        }

        .summary-label {
            font-weight: 600;
            text-align: left;
        }

        .summary-value {
            text-align: left;
        }

        .summary-colon {
            text-align: center;
        }

        .signature-section {
            display: flex;
            flex: 1;
            justify-content: space-between;
            gap: 20px;
        }

        .signature-box {
            flex: 1;
            text-align: center;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 70px;
            font-size: 10pt;
        }

        .signature-line {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 9pt;
            text-align: left;
        }

        /* Print Styles */
        @media print {
            body {
                padding: 10mm 15mm !important;
                /* Atur jarak halaman 1 di sini */
                margin: 0 !important;
            }

            .container {
                max-width: 100%;
            }

            @page {
                margin: 0;
                /* Matikan margin @page agar tidak dobel */
            }

            .table-container {
                margin-top: -10mm;
                /* Menarik tabel ke atas untuk membatalkan spacer di halaman 1 */
            }

            .table-header-spacer {
                display: table-row;
            }

            .table-header-spacer th {
                height: 10mm;
                /* Harus sama dengan padding-top body */
                border: none !important;
                padding: 0 !important;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <img src="{{ asset('images/asa_old.jpg') }}" alt="Company Logo" class="logo">
            </div>
            <div class="header-right">
                <div class="company-name">Mitra Asa Pratama</div>
                <div class="company-address">
                    PT MITRA ASA PRATAMA<br>
                    MTH Square Lt. 1 No. 6, Jl. Letjen M.T. Haryono<br>
                    No.Kav. 10, RT.6/RW.12, Kp. Melayu,<br>
                    Kecamatan Jatinegara<br>
                    Jakarta Timur JK 13330<br>
                    Indonesia
                </div>
            </div>
        </div>

        <!-- Address Section -->
        <div class="address-title">Invoicing and shipping address:</div>
        <div class="address-section">
            <div class="address-box">
                <div class="address-content">
                    <b>{{ Arr::get($data['partner_invoice_id_detail'], 'display_name') }}</b>
                    @if ($val = Arr::get($data['partner_invoice_id_detail'], 'street'))
                        <br>{{ $val }}
                    @endif
                    @if ($val = Arr::get($data['partner_invoice_id_detail'], 'street2'))
                        <br>{{ $val }}
                    @endif
                    @if ($val = Arr::get($data['partner_invoice_id_detail'], 'city'))
                        <br>{{ $val }}
                    @endif
                    @if ($val = Arr::get($data['partner_invoice_id_detail'], 'zip'))
                        <br>{{ $val }}
                    @endif
                    @if ($val = get_name(Arr::get($data['partner_invoice_id_detail'], 'country_id')))
                        <br>{{ $val }}
                    @endif
                </div>
            </div>
            <div class="address-box">
                <div class="address-content">
                    <b>{{ Arr::get($data['partner_shipping_id_detail'], 'name') }}</b>
                    @if ($val = Arr::get($data['partner_shipping_id_detail'], 'street'))
                        <br>{{ $val }}
                    @endif
                    @if ($val = Arr::get($data['partner_shipping_id_detail'], 'street2'))
                        <br>{{ $val }}
                    @endif
                    @if ($val = Arr::get($data['partner_shipping_id_detail'], 'city'))
                        <br>{{ $val }}
                    @endif
                    @if ($val = Arr::get($data['partner_shipping_id_detail'], 'zip'))
                        <br>{{ $val }}
                    @endif
                    @if ($val = get_name(Arr::get($data['partner_shipping_id_detail'], 'country_id')))
                        <br>{{ $val }}
                    @endif
                </div>
            </div>
        </div>

        <!-- Order Title -->
        <div class="order-title">ORDER - {{ $data['name'] ?? '' }}</div>

        <!-- Order Info Grid -->
        <div class="order-info">
            <div class="info-row">
                <span class="info-label">Date Order :</span>
                <span
                    class="info-value">{{ $data['confirmation_date'] != false ? date('d/m/Y', strtotime($data['confirmation_date'])) : 'False' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Salesperson :</span>
                <span class="info-value"> {{ get_name($data['user_id']) ?? 'False' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">No PO :</span>
                <span class="info-value">{{ Arr::get($data, 'no_po', 'False') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment Term :</span>
                <span class="info-value">{{ get_name(Arr::get($data, 'payment_term_id', '')) ?? 'False' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">No. ID Paket :</span>
                <span class="info-value">{{ Arr::get($data, 'no_aks', 'False') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Ekspedisi :</span>
                <span class="info-value">{{ get_name(Arr::get($data, 'nama_ekspedisi', '')) ?? 'False' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">SKA:</span>
                <span class="info-value">{{ Arr::get($data, 'no_ska', 'False') }}</span>
            </div>
        </div>

        @php
            $note_to_wh = preg_replace("/(\r\n|\n|\r){2,}/", "\n", trim(Arr::get($data, 'note_to_wh', '')));
            $sistem = Arr::get($data, 'sistem', '');
            $mf = strtolower($sistem) == 'mf';
            $label_mf = $mf ? '<b>' . strtoupper($sistem) . '</b><br/>' : '';
        @endphp
        <!-- Note Warehouse -->
        <div class="note-section">
            <div class="note-title">Note Warehouse</div>
            <div class="note-content">{!! $label_mf !!}{!! $note_to_wh !!}</div>
        </div>

        <!-- Product Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr class="table-header-spacer">
                        <th colspan="6"></th>
                    </tr>
                    <tr>
                        <th>Product</th>
                        <th class="text-center" style="white-space: nowrap;">Qty</th>
                        <th class="text-right" style="white-space: nowrap;">Unit Price</th>
                        <th class="text-center">Disc(%)</th>
                        <th class="text-center">Taxes</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($data['order_line_detail'] ?? [] as $item)
                        <tr>
                            <td>[{{ $item['default_code'] }}] {{ $item['name'] }}</td>
                            <td class="text-center" style="white-space: nowrap;">
                                {{ number_format($item['product_uom_qty'], 1) }} {{ get_name($item['product_uom']) }}
                            </td>
                            <td class="text-right">{{ $item['unit_price1'] }}</td>
                            <td class="text-center">{{ number_format($item['discount'], 1) }}</td>
                            <td class="text-center">{{ Arr::get($item, 'tax_id_detail.display_name', 'False') }}</td>
                            <td class="text-right">{{ $item['price_subtotal1'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer Combined (Summary & Signatures) -->
        <div class="footer-combine">
            <!-- Signature portion -->
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-title">Prepared By</div>
                    <div class="signature-line">Date:</div>
                </div>
                <div class="signature-box">
                    <div class="signature-title">Reviewed By</div>
                    <div class="signature-line">Date:</div>
                </div>
                <div class="signature-box">
                    <div class="signature-title">Approved By</div>
                    <div class="signature-line">Date:</div>
                </div>
            </div>

            <!-- Summary portion -->
            <div class="summary-box">
                <div class="summary-row">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-colon">:</span>
                    <span class="summary-value">{{ number_format($data['amount_untaxed'], 2) }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Total</span>
                    <span class="summary-colon">:</span>
                    <span class="summary-value">{{ number_format($data['amount_total'], 2) }}</span>
                </div>
            </div>
        </div>
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

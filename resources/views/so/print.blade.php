<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Sales Order - {{ $so_number ?? 'SO14803' }}</title>
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
            /* margin-bottom: 15px; */
            /* padding-bottom: 15px; */
            /* border-bottom: 2px solid #333; */
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
            color: #666;
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
            margin-bottom: 8px;
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
            grid-template-columns: 1fr 1fr;
            gap: 5px 30px;
            margin-bottom: 5px;
        }

        .info-row {
            display: flex;
            font-size: 9pt;
        }

        .info-label {
            font-weight: bold;
            min-width: 120px;
            margin-right: 10px;
        }

        .info-value {
            flex: 1;
        }

        /* Note Section */
        .note-section {
            margin-bottom: 20px;
            padding: 10px;
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

        table thead {
            background: #f0f0f0;
        }

        table th {
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }

        table td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        table th.text-center,
        table td.text-center {
            text-align: center;
        }

        table th.text-right,
        table td.text-right {
            text-align: right;
        }

        /* Summary Section */
        .summary-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }

        .summary-box {
            min-width: 300px;
            border: 1px solid #ddd;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 12px;
            border-bottom: 1px solid #ddd;
            font-size: 10pt;
        }

        .summary-row:last-child {
            border-bottom: none;
            background: #f0f0f0;
            font-weight: bold;
            font-size: 11pt;
        }

        .summary-label {
            font-weight: 600;
        }

        .summary-value {
            text-align: right;
        }

        /* Signature Section */
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            gap: 30px;
        }

        .signature-box {
            flex: 1;
            text-align: center;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 60px;
            font-size: 10pt;
        }

        .signature-line {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 9pt;
        }

        /* Print Styles */
        @media print {
            body {
                padding: 8mm;
            }

            .container {
                max-width: 100%;
            }

            @page {
                margin: 15mm;
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
                    Jakarta Timur DK 13330<br>
                    Indonesia
                </div>
            </div>
        </div>

        <!-- Address Section -->
        <div class="address-section">
            <div class="address-box">
                <div class="address-title">Invoicing and shipping address:</div>
                <div class="address-content">
                    Mitra Asa Pratama, PT<br>
                    MTH Square Lt. 1 No. 6-7-8, Jl. Letjen M.T. Haryono<br>
                    No.Kav. 10, RT.6/RW.12, Kp. Melayu, Kecamatan<br>
                    Jatinegara<br>
                    Jakarta Timur<br>
                    13330<br>
                    Indonesia
                </div>
            </div>
            <div class="address-box">
                <div class="address-content">
                    {{ get_name($data['partner_shipping_id']) }}<br>
                    Jl. Raya Cilimus No.172 Cilimus Kuningan Jawa Barat<br><br>
                    False<br>
                    Kuningan<br>
                    False<br>
                    Indonesia
                </div>
            </div>
        </div>

        <!-- Order Title -->
        <div class="order-title">ORDER - {{ $data['name'] ?? '' }}</div>

        <!-- Order Info Grid -->
        <div class="order-info">
            <div class="info-row">
                <span class="info-label">Date Order :</span>
                <span class="info-value">False</span>
            </div>
            <div class="info-row">
                <span class="info-label">No PO :</span>
                <span class="info-value">{{ get_name($data['no_po']) ?? 'False' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Salesperson :</span>
                <span class="info-value">Kantor</span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment Term :</span>
                <span class="info-value">{{ get_name($data['payment_term_id']) ?? 'False' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">No. ID Paket :</span>
                <span class="info-value">{{ get_name($data['no_aks']) ?? 'False' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">SKA:</span>
                <span class="info-value">False</span>
            </div>
            <div class="info-row">
                <span class="info-label">Ekspedisi :</span>
                <span class="info-value">{{ get_name($data['nama_ekspedisi']) ?? 'False' }}</span>
            </div>
        </div>

        <!-- Note Warehouse -->
        <div class="note-section">
            <div class="note-title">Note Warehouse</div>
            <div class="note-content">
                {!! $data['note_to_wh'] ?? '' !!}
            </div>
        </div>

        <!-- Product Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-center">Disc(%)</th>
                        <th class="text-center">Taxes</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['order_line_detail'] ?? [] as $item)
                        <tr>
                            <td>{{ $item['default_code'] }} {{ $item['name'] }}</td>
                            <td class="text-center">{{ $item['product_uom_qty'] }}
                                {{ get_name($item['product_uom']) }}
                            </td>
                            <td class="text-right">{{ $item['unit_price1'] }}</td>
                            <td class="text-center">{{ $item['plus_disc'] }}</td>
                            <td class="text-center">Tax 12.00%<br>PPN KELUARAN<br>(INCLUDED)</td>
                            <td class="text-right">{{ $item['price_subtotal1'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="summary-section">
            <div class="summary-box">
                <div class="summary-row">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value">: 347,027.02</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Total</span>
                    <span class="summary-value">: 385,199.99</span>
                </div>
            </div>
        </div>

        <!-- Signature Section -->
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
    </div>

    <script>
        // Auto print when page loads (optional)
        window.onload = function() {
            // Auto print saat halaman dimuat
            window.print();

            // Auto close tab setelah print selesai atau dibatalkan
            // Method 1: Menggunakan afterprint event (untuk browser modern)
            window.addEventListener('afterprint', function() {
                window.close();
            });

            // Method 2: Fallback untuk browser yang tidak support afterprint
            // Deteksi saat user kembali ke halaman (cancel print atau selesai print)
            let isPrinting = true;
            window.onbeforeprint = function() {
                isPrinting = true;
            };

            window.onfocus = function() {
                if (isPrinting) {
                    isPrinting = false;
                    // Delay sedikit untuk memastikan print dialog sudah tertutup
                    setTimeout(function() {
                        window.close();
                    }, 100);
                }
            };
        }
    </script>
</body>

</html>

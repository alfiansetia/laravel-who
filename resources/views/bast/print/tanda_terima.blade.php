<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/asa.png') }}" />
    <title>Tanda Terima - {{ $data->do ?? '-' }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 40px 0;
            background-color: #525659;
            /* Gray background like PDF viewer */
            display: flex;
            justify-content: center;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 15mm 15mm;
            background: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            position: relative;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }

        .header {
            text-align: center;
            margin-bottom: 5px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .info-section {
            margin-bottom: 10px;
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

        /* List Styling */
        .item-list-container {
            margin-top: 15px;
            flex-grow: 1;
        }

        .item-list {
            list-style-type: decimal;
            padding-left: 25px;
            margin: 0;
        }

        .item-list li {
            margin-bottom: 8px;
            font-size: 14px;
            line-height: 1.4;
        }

        /* Signature Styling */
        .signature-section {
            margin-top: 20px;
            width: 100%;
        }

        .sig-grid {
            display: flex;
            justify-content: space-between;
        }

        .sig-col {
            width: 45%;
        }

        .sig-space {
            height: 80px;
        }

        .sig-line {
            border-bottom: 1px solid #000;
            width: 80%;
            margin-top: 5px;
        }

        .form-footer-code {
            text-align: right;
            margin-top: 20px;
            font-size: 14px;
            width: 100%;
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
                display: block;
            }

            .page {
                margin: 0;
                padding: 10mm 15mm;
                box-shadow: none;
                width: 100%;
                min-height: 297mm;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div id="print-container">
        <div class="page">
            <div class="header-kop" style="margin-bottom: 2px; padding-bottom: 2px; ">
                <img src="{{ asset('images/map.png') }}" alt="Kop Map"
                    style="height: 60px; width: auto; max-width: 100%;">
            </div>
            <div class="info-section">
                <div class="header">
                    <h1>Tanda Terima Alat</h1>
                </div>
            </div>

            <div class="item-list-container">
                <ol class="item-list">
                    @foreach ($data->details as $item)
                        <li>
                            <span class="item-text">
                                {{ (int) $item->qty }} ({{ ucwords(trim(terbilang($item->qty))) }}) {{ $item->satuan }}
                                {{ $item->product->name }} ({{ $item->product->code }})
                                @if ($item->lot)
                                    SN/Lot : {{ $item->lot }}
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="signature-section">
                <table style="width: 100%; border: none; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; vertical-align: top; padding: 0;">
                            <p style="margin: 0; font-size: 14px;">{{ $data->city ?? 'Semarang' }}, _________________
                            </p>
                            <p style="margin: 0; font-size: 14px;">Yang Menyerahkan</p>
                            <p style="margin: 0; font-size: 14px;">PT. Mitra Asa Pratama</p>
                        </td>
                        <td style="width: 50%; vertical-align: top; padding: 0;">
                            <p style="margin: 0; font-size: 14px;">&nbsp;</p>
                            <p style="margin: 0; font-size: 14px;">Yang Menerima</p>
                            <p style="margin: 2px 0 0 0; font-size: 14px; line-height: 1.2;">{{ $data->name }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 80px;"></td>
                        <td style="height: 80px;"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 40px;">
                            <div style="border-bottom: 1px solid #000; width: 100%;"></div>
                        </td>
                        <td style="padding-right: 40px;">
                            <div style="border-bottom: 1px solid #000; width: 100%;"></div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="form-footer-code">
                FORM/WH/049/20.1
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };

        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>

</html>

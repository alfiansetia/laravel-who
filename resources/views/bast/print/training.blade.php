<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/asa.png') }}" />
    <title>Training - {{ $data->do ?? '-' }}</title>
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
        }

        .item-list {
            list-style-type: decimal;
            padding-left: 25px;
            margin: 0;
        }

        .item-list li {
            margin-bottom: 5px;
            font-size: 14px;
            line-height: 1.4;
        }

        /* Signature Styling */
        .signature-section {
            margin-top: 40px;
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
            float: right;
            margin-top: 20px;
            font-size: 14px;
            font-weight: bold;
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
                min-height: 0;
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
                    <h1>DAFTAR PESERTA TRAINING/PELATIHAN</h1>
                </div>
            </div>

            <div class="training-content" style="font-size: 14px; line-height: 1.5; margin-top: 15px; color: #000;">
                <p style="margin: 0;">Kami PT. Mitra Asa Pratama telah melakukan training dan pelatihan alat-alat
                    sebagai berikut :</p>
                <ul style="list-style-type: disc; padding-left: 20px; margin: 5px 0 15px 0;">
                    @foreach ($data->details as $item)
                        <li>
                            {{ (int) $item->qty }} ({{ ucwords(trim(terbilang($item->qty))) }})
                            {{ $item->satuan }} {{ $item->product->name }} ({{ $item->product->code }})
                            @if ($item->lot)
                                SN/Lot : {{ $item->lot }}
                            @endif
                        </li>
                    @endforeach
                </ul>

                <p style="margin: 20px 0 5px 0;">Peserta Training dan Pelatihan</p>
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 13px;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #000; padding: 5px; width: 30px; text-align: center;">No</th>
                            <th style="border: 1px solid #000; padding: 5px; text-align: left;">Nama Peserta</th>
                            <th style="border: 1px solid #000; padding: 5px; text-align: left; width: 220px;">Keterangan
                            </th>
                            <th style="border: 1px solid #000; padding: 5px; text-align: left; width: 160px;">Tanda
                                Tangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 1; $i <= 7; $i++)
                            <tr>
                                <td style="border: 1px solid #000; padding: 10px 5px; text-align: center;">
                                    {{ $i }}.</td>
                                <td style="border: 1px solid #000; padding: 10px 5px;">&nbsp;</td>
                                <td style="border: 1px solid #000; padding: 10px 5px;">&nbsp;</td>
                                <td style="border: 1px solid #000; padding: 10px 5px;">&nbsp;</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            @for ($i = count($data->details); $i < 10; $i++)
                <div style="height: 1.4em;">&nbsp;</div>
            @endfor

            <div class="signature-section" style="margin-top: 30px;">
                <div style="margin-bottom: 5px; font-size: 14px;">
                    <p style="margin: 0;">Tempat : _________________</p>
                    <p style="margin: 0;">Tanggal : _________________</p>
                </div>
                <table style="width: 100%; border: none; border-collapse: collapse; margin-top: 5px;">
                    <tr>
                        <td style="width: 50%; vertical-align: top; padding: 0;">
                            <p style="margin: 0; font-size: 14px;">PT. Mitra Asa Pratama</p>
                        </td>
                        <td style="width: 50%; vertical-align: top; padding: 0;">
                            <p style="margin: 0; font-size: 14px;">Mengetahui,</p>
                            <p style="margin: 0; font-size: 14px; line-height: 1.2;">{{ $data->name }}</p>
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

            <div class="form-footer-code" style="margin-top: 30px;">
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

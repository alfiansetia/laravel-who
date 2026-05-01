<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/asa.png') }}" />
    <title>BAST - {{ $data->do ?? '-' }}</title>
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

        .bast-content {
            font-size: 14.5px;
            line-height: 1.5;
            margin-top: 15px;
            color: #000;
            flex-grow: 1;
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
                    <h1>BERITA ACARA SERAH TERIMA DAN UJI FUNGSI</h1>
                </div>
            </div>

            <div class="bast-content" style="font-size: 14.5px; line-height: 1.5; margin-top: 15px; color: #000;">
                <ol style="list-style-type: decimal; padding-left: 25px; margin: 0;">
                    <li style="margin-bottom: 12px;">
                        Pada hari ini telah dilakukan pekerjaan instalasi/perbaikan dan uji fungsi alat di :
                        <div style="margin-left: 0; margin-top: 8px;">
                            <div style="display: flex; margin-bottom: 4px;">
                                <span style="width: 140px;">Nama Pelanggan</span>
                                <span style="flex: 1;">: {{ $data->name }}</span>
                            </div>
                            <div style="display: flex; align-items: flex-start;">
                                <span style="width: 140px;">Alamat</span>
                                <span style="flex: 1;">: {{ $data->address }} {{ $data->city }}</span>
                            </div>
                        </div>
                        <p style="margin: 8px 0 0 0;">Yang selanjutnya disebut <b>PIHAK PERTAMA</b></p>
                    </li>

                    <li style="margin-bottom: 12px;">
                        Alat yang di instalasi/perbaiki dan uji fungsi adalah sebagai berikut :
                        <div style="margin-top: 5px;">
                            <p style="margin: 0;">Type :</p>
                            <ul style="list-style-type: disc; padding-left: 20px; margin: 5px 0 0 0;">
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
                        </div>
                    </li>

                    <li style="margin-bottom: 12px;">
                        Pekerjaan instalasi/perbaikan dan uji fungsi dilakukan oleh teknisi PT. Mitra Asa Pratama<br>
                        Yang selanjutnya disebut <b>PIHAK KEDUA</b>
                    </li>

                    <li style="margin-bottom: 12px;">
                        <b>Pihak Kedua</b> memberikan garansi kepada <b>Pihak Pertama</b> yang tertera dalam Kartu
                        Garansi
                        dan Pihak Kedua bertanggung jawab terhadap after sales service
                    </li>

                    <li style="margin-bottom: 12px;">
                        <b>Pihak Pertama</b> menyatakan dengan ini telah menerima pekerjaan <b>Pihak Kedua</b> yang
                        tertulis pada point 2 (dua) dengan baik
                    </li>

                    <li style="margin-bottom: 12px;">
                        Berita acara ini disetujui oleh <b>Pihak Pertama</b> dan <b>Pihak Kedua</b>
                    </li>
                </ol>
            </div>

            <div class="signature-section" style="margin-top: 30px;">
                <p style="margin-bottom: 5px; font-size: 14.5px;">Tanggal Pekerjaan : _________________</p>
                <table style="width: 100%; border: none; border-collapse: collapse; margin-top: 10px;">
                    <tr>
                        <td style="width: 50%; vertical-align: top; padding: 0;">
                            <p style="margin: 0; font-size: 14.5px;">
                                Pihak Pertama,</p>
                            <p style="margin: 2px 0 0 0; font-size: 14.5px; line-height: 1.2;">
                                {{ $data->name }}</p>
                        </td>
                        <td style="width: 50%; vertical-align: top; padding: 0;">
                            <p style="margin: 0; font-size: 14.5px;">
                                Pihak Kedua,</p>
                            <p style="margin: 2px 0 0 0; font-size: 14.5px; line-height: 1.2;">PT.
                                Mitra Asa Pratama</p>
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

            <div class="form-footer-code" style="margin-top: 40px;">
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

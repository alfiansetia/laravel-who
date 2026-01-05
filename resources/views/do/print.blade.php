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
            line-height: 1.2;
            /* padding: 10mm; */
            background: white;
            color: #000;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        /* Top Header */
        .top-header {
            display: flex;
            align-items: flex-start;
            margin-bottom: 5px;
        }

        .logo-section {
            display: flex;
            align-items: center;
            width: 33%;
        }

        .logo-img {
            height: 45px;
            margin-right: 10px;
        }

        .logo-text {
            border-left: 1px solid #000;
            padding-left: 10px;
            font-size: 10pt;
            font-weight: bold;
            line-height: 1;
        }

        /* Info Grid (Company, Ship To, DO Info) */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 5px;
            margin-bottom: 5px;
        }

        .info-box {
            border: 1px solid #000;
            padding: 5px;
            min-height: 100px;
        }

        .info-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 3px;
        }

        .do-header-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* Detail Table (Right side of header) */
        .do-info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .do-info-table th,
        .do-info-table td {
            border: 1px solid #000;
            padding: 2px 4px;
            text-align: center;
            font-size: 8pt;
        }

        .do-info-table th {
            font-weight: bold;
            background-color: #fff;
        }

        /* Middle Section (Bill To & Description) */
        .middle-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 5px;
            margin-bottom: 10px;
        }

        .middle-box {
            border: 1px solid #000;
            padding: 5px;
            min-height: 80px;
        }

        /* Main Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
            font-size: 8.5pt;
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

        /* Footer / Signatures */
        .footer-signatures {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            margin-top: 30px;
            text-align: center;
        }

        .signature-box {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 80px;
        }

        .signature-name {
            font-weight: normal;
        }

        .signature-line {
            width: 80%;
            margin: 0 auto;
            border-bottom: 1px solid #000;
        }

        .signature-date {
            margin-top: 2px;
            text-align: left;
            padding-left: 10%;
            font-size: 8pt;
        }

        .form-code {
            /* position: fixed; */
            /* bottom: 10mm; */
            /* right: 15mm; */
            margin-top: 8px;
            text-align: right;
            font-size: 8pt;
        }

        @media print {
            body {
                padding: 8mm;
            }

            .container {
                width: 100%;
            }

            @page {
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Top Logo Header -->
        <div class="top-header">
            <div class="logo-section">
                <img src="{{ asset('images/map_do.png') }}" alt="Logo" class="logo-img">
            </div>
        </div>

        <!-- Info Grid -->
        <div class="info-grid">
            <!-- Company Info -->
            <div class="info-box">
                <div class="info-title" style="text-decoration: none;">PT MITRA ASA PRATAMA</div>
                <div>
                    MTH Square Lt. 1 No. 6, Jl. Letjen M.T.<br>
                    Haryono No.Kav. 10, RT.6/RW.12, Kp.<br>
                    Melayu, Kecamatan Jatinegara<br>
                    Jakarta Timur JK 13330<br>
                    Indonesia
                </div>
            </div>

            <!-- Ship To -->
            <div class="info-box">
                <div class="info-title">Ship To</div>
                <div>
                    Humbang Hasundutan Kab, Dinkes<br>
                    Jl. Sisingamangaraja, Kompleks<br>
                    Perkantoran Tano Tubu KM 2.5,<br>
                    Doloksanggul Kode Pos 22457<br>
                    Humbang Hasundutan<br>
                    Indonesia<br>
                    UP : Ibu Neli Purba PPK 0813-7017-5245<br>
                    Tlp : - / -
                </div>
            </div>

            <!-- DO Details -->
            <div style="display: flex; flex-direction: column;">
                <div class="do-header-title">Delivery Order</div>
                <table class="do-info-table">
                    <tr>
                        <th style="width: 50%;">Delivery Date</th>
                        <th>DO No</th>
                    </tr>
                    <tr>
                        <td>05/01/2026</td>
                        <td>{{ $data['name'] ?? 'CENT/OUT/16061' }}</td>
                    </tr>
                    <tr>
                        <th colspan="2">PO No</th>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: center; font-size: 8pt;">
                            EP-01KCTZD9FMX5VTYHKTVA1R4CR8 PUSKESMAS S PAKKAT
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding: 0;">
                            <table style="width: 100%; border-collapse: collapse; border: none;">
                                <tr>
                                    <td style="border: none; border-right: 1px solid #000; width: 50%; padding: 2px;">
                                        <strong>Terms</strong><br>60 Days
                                    </td>
                                    <td style="border: none; padding: 2px;">
                                        <strong>ID Paket</strong><br>
                                        EP-01KCTZD9FMX5VTYHKTVA1R4CR8 PUSKESMAS PAKKAT
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2">Ship Via</th>
                    </tr>
                    <tr>
                        <td colspan="2">Tiki Regular</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Middle Grid -->
        <div class="middle-grid">
            <div class="middle-box">
                <div class="info-title">Bill To</div>
                <div>
                    Humbang Hasundutan Kab, Dinkes<br>
                    Jl. Sisingamangaraja, Kompleks Perkantoran<br>
                    Tano Tubu KM 2.5, Doloksanggul Kode Pos<br>
                    22457<br>
                    Humbang Hasundutan<br>
                    Indonesia
                </div>
            </div>
            <div class="middle-box" style="grid-column: span 2;">
                <div class="info-title">Description:</div>
                <div>
                    CBP<br>
                    ACC DSS 5/1/2026 : Kirim Semua.<br><br>
                    Packaging LABEL: BMHP JKN UPT<br>
                    PUSKESMAS PAKKAT<br>
                    Nilai kontrak Rp 23.035.053 Dateline kontrak 23/2/2026
                </div>
            </div>
        </div>

        <!-- Main Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Item</th>
                    <th style="width: 35%;">Item Description</th>
                    <th style="width: 15%;">AKL</th>
                    <th style="width: 10%;">LOT/SN</th>
                    <th style="width: 10%;">ED</th>
                    <th style="width: 8%;">QTY</th>
                    <th style="width: 7%;">Unit</th>
                </tr>
            </thead>
            <tbody>
                <!-- Dummy Data Rows -->
                <tr>
                    <td>PDL.NLC-U25</td>
                    <td>Nubion LifeCheck UA 1x25</td>
                    <td class="text-center">AKD 20101420145</td>
                    <td class="text-center">FE41</td>
                    <td class="text-center">31/07/2027</td>
                    <td class="text-center">0.0</td>
                    <td class="text-center">EA</td>
                </tr>
                <tr>
                    <td>PDL.NLC-G50</td>
                    <td>Nubion LifeCheck GLU 2x25</td>
                    <td class="text-center">AKD 20101324087</td>
                    <td class="text-center">F1b3.</td>
                    <td class="text-center">31/07/2027</td>
                    <td class="text-center">0.0</td>
                    <td class="text-center">EA</td>
                </tr>
                <tr>
                    <td>PDL.NLC-C10</td>
                    <td>Nubion LifeCheck TC 1x10</td>
                    <td class="text-center">AKD 20101420146</td>
                    <td class="text-center">22E2</td>
                    <td class="text-center">28/02/2027</td>
                    <td class="text-center">0.0</td>
                    <td class="text-center">EA</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer Signatures -->
        <div class="footer-signatures">
            <div class="signature-box">
                <div>Prepared By</div>
                <div>
                    <div class="signature-line"></div>
                    <div class="signature-date">Date</div>
                </div>
            </div>
            <div class="signature-box">
                <div>Approved By</div>
                <div>
                    <div class="signature-line"></div>
                    <div class="signature-date">Date</div>
                </div>
            </div>
            <div class="signature-box">
                <div>PJT</div>
                <div>
                    <div class="signature-line"></div>
                    <div class="signature-date">Date</div>
                </div>
            </div>
            <div class="signature-box">
                <div>Shipped By</div>
                <div>
                    <div class="signature-line"></div>
                    <div class="signature-date">Date</div>
                </div>
            </div>
            <div class="signature-box">
                <div>Received By</div>
                <div>
                    <div class="signature-line"></div>
                    <div class="signature-date">Date</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Code -->
    <div class="form-code">FORM/WH/013/20.1</div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>

</html>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="author" content="ASA" />
    <link rel="icon" type="image/x-icon" href="{{ asset('images/asa.png') }}" />
    <title>Alamat {{ $data->do ?? '-' }}</title>

</head>

<body style="margin: 0pt">
    <style>
        td.style22 {
            vertical-align: middle;
            text-align: left;
            padding-left: 2px;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 22pt;
            background-color: white
        }

        td.style21 {
            vertical-align: middle;
            text-align: left;
            padding-left: 2px;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 21pt;
            background-color: white
        }

        td.style20 {
            vertical-align: middle;
            text-align: left;
            padding-left: 2px;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 20pt;
            background-color: white
        }

        td.style18 {
            vertical-align: middle;
            text-align: left;
            padding-left: 2px;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 18pt;
            background-color: white
        }

        td.style12 {
            vertical-align: middle;
            text-align: left;
            padding-left: 2px;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 12pt;
            background-color: white
        }

        td.style14 {
            vertical-align: middle;
            text-align: left;
            padding-left: 2px;
            /* font-weight: bold; */
            color: #000000;
            font-family: 'Cambria';
            font-size: 14pt;
            background-color: white
        }

        td.style28 {
            vertical-align: middle;
            text-align: left;
            padding-left: 2px;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 28pt;
            background-color: white
        }

        td.style8 {
            vertical-align: middle;
            font-weight: bold;
            color: #FF0000;
            font-family: 'Cambria';
            font-size: 22pt;
            background-color: white
        }

        td.style70 {
            vertical-align: middle;
            font-weight: bold;
            color: #FF0000;
            font-family: 'Cambria';
            font-size: 65pt;
            background-color: white
        }

        td.kiri-dua {
            border-left: 2px solid black;
        }

        td.kanan-dua {
            border-right: 2px solid black;
        }

        td.atas-dua {
            border-top: 2px solid black;
        }

        td.bawah-dua {
            border-bottom: 2px solid black;
        }

        @media print {
            .print-page {
                padding-top: 10pt;
                padding-left: 10pt;
                padding-right: 10pt;
                page-break-after: always;
            }

            .print-page:last-child {
                page-break-after: auto;
            }

            .no-print {
                display: none;
            }
        }

        .btn-action {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .btn-split {
            background: #007bff;
            color: white;
        }

        .btn-unsplit {
            background: #dc3545;
            color: white;
        }

        .btn-action:hover {
            opacity: 0.9;
        }

        .btn-action {
            display: inline-block;
            padding: 8px 14px;
            margin: 4px;
            border-radius: 6px;
            text-decoration: none;
            border: none;
            font-weight: 600;
            cursor: pointer;
            color: white;
        }

        .btn-primary {
            background: #007bff;
        }

        .btn-warning {
            background: #f0ad4e;
        }

        .btn-success {
            background: #28a745;
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-action:hover {
            opacity: 0.9;
        }
    </style>
    <div class="no-print" style="text-align:center; margin:10px;">

        @if ($is_split)
            <a href="{{ route('alamat_baru.show', $data->id) }}" class="btn-action btn-warning">
                Unsplit Packing List
            </a>
        @else
            <a href="{{ route('alamat_baru.show', $data->id) }}?split=true" class="btn-action btn-primary">
                Split Packing List
            </a>
        @endif

        <button class="btn-action btn-success" onclick="window.location.reload()">
            Print Packing List
        </button>

        <button class="btn-action btn-danger" onclick="window.close()">
            Close
        </button>

    </div>

    @foreach ($kolis as $koli)
        @php
            $ctns = parseNumberList($koli->urutan);
        @endphp
        @foreach ($ctns as $ctn)
            <table style="width: 100%;" class="print-page">
                <tr>
                    <td class="style18" colspan="3">KEPADA YTH,</td>
                    <td class="style18 kiri-dua kanan-dua atas-dua" colspan="2" style="color: #FF0000;width: 30%">
                        {{ $data->ekspedisi }}
                    </td>
                </tr>
                <tr>
                    <td class="style20" rowspan="2" colspan="3">{{ strtoupper($data->tujuan) }}</td>
                    <td class="style18 kiri-dua kanan-dua bawah-dua" style="color: #FF0000">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; color: #FF0000;">
                            <span>{!! $data->total_koli < 1 ? '&nbsp;&nbsp;' : $data->total_koli !!} KOLI</span>
                            @if ($data->total_koli > 1)
                                <span style="color: black; font-size: 15pt;padding-right: 5pt">(Ctn No:
                                    {{ $ctn }})</span>
                            @endif
                        </div>
                    </td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                @foreach (explode("\n", $data->alamat) ?? [] as $key => $line)
                    @if ($key == 0)
                        <tr>
                            <td class="style18" colspan="{{ $koli->is_asuransi == 'yes' ? '3' : '4' }}">
                                {!! $line !!}
                            </td>
                            @if ($koli->is_asuransi == 'yes')
                                <td class="style18 kiri-dua kanan-dua bawah-dua atas-dua" colspan="1"
                                    style="color: #FF0000;">
                                    <b>ASURANSI</b>
                                </td>
                            @endif
                        </tr>
                    @else
                        <tr>
                            <td class="style18" colspan="4">{!! $line !!}</td>
                        </tr>
                    @endif
                @endforeach
                <tr>
                    <td colspan="4">
                        <table>
                            @if (!empty($data->up))
                                <tr>
                                    <td class="style18" style="width: 14%;vertical-align: baseline">UP</td>
                                    <td class="style18" style="width: 1px;vertical-align: baseline">:</td>
                                    <td class="style18" colspan="2">{{ $data->up }}</td>
                                </tr>
                            @endif
                            @if (!empty($data->tlp))
                                <tr>
                                    <td class="style18" style="width: 14%;vertical-align: baseline">Tlp</td>
                                    <td class="style18" style="width: 1px;vertical-align: baseline">:</td>
                                    <td class="style18" colspan="2">{{ $data->tlp }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="style18" style="width: 14%;vertical-align: baseline">No DO</td>
                                <td class="style18" style="width: 1px;vertical-align: baseline">:</td>
                                <td class="style18" colspan="2">{{ $data->do }} @if (!empty($data->epur))
                                        (<span style="font-size: 16pt">{{ $data->epur }}</span>)
                                    @endif
                                </td>
                            </tr>
                            @if (!empty($data->untuk))
                                <tr>
                                    <td class="style18" style="width: 14%;vertical-align: baseline">Untuk</td>
                                    <td class="style18" style="width: 1px;vertical-align: baseline">:</td>
                                    <td class="style18" colspan="2">{{ $data->untuk }}</td>
                                </tr>
                            @endif
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="4" style="border-bottom: 4px double black"></td>
                </tr>
                <tr>
                    <td class="style12" style="width: 14%">FROM :</td>
                    <td class="style12" colspan="3">PT MITRA ASA PRATAMA</td>
                </tr>
                <tr>
                    <td></td>
                    <td class="style12" colspan="3">MT.HARYONO SQUARE , JL. MT.HARYONO</td>
                </tr>
                <tr>
                    <td></td>
                    <td class="style12" colspan="3">KAV.10 NO: OF 01/06 BIDARA CINA, JATINEGARA - JAKARTA TIMUR</td>
                </tr>
                <tr>
                    <td></td>
                    <td class="style12" colspan="3">TELP. 021 - 89456598 / 021- 29067256 ~ 57 / FAX. 021 - 29067258
                    </td>
                </tr>
                <tr>
                    <td class="style28" colspan="4" style="text-align: center; color: #FF0000">HATI - HATI BARANG
                        MUDAH PENYOK</td>
                </tr>
                <tr>
                    <td class="style18 kiri-dua atas-dua kanan-dua bawah-dua" colspan="4"
                        style="text-align: left; font-size: 12pt">PACKING LIST :
                    </td>
                </tr>

                @php
                    $items = $koli->items;
                    $count1 = ceil($items->count() / 2);
                @endphp
                @if (!$is_split)
                    <tr>
                        <td colspan="4" class="kiri-dua kanan-dua atas-dua bawah-dua">
                            <table style="width:100%">
                                @foreach ($koli->items ?? [] as $item)
                                    <tr>
                                        <td colspan="1" style="width: 2px;vertical-align: baseline">~
                                        </td>
                                        <td class="style14" colspan="4" style="text-align: left">
                                            <b>{{ $item->product->code }}</b>
                                            @if (!empty($item->product->name))
                                                ({{ $item->product->name }})
                                            @endif
                                            @if (!empty($item->desc))
                                                ({{ $item->desc }})
                                            @endif
                                            = {{ $item->qty }}
                                        </td>
                                    </tr>
                                    @if (!empty($item->lot))
                                        <tr>
                                            <td style="width: 2px"></td>
                                            <td class="style14"
                                                style="text-align: right;vertical-align: top;width: 55px">
                                                Lot : </td>
                                            <td class="style14" colspan="3">{!! nl2br(e($item->lot)) !!}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </table>
                        </td>
                    </tr>
                @else
                    <tr>
                        <td colspan="4" class="kiri-dua kanan-dua atas-dua bawah-dua">
                            <table width="100%">
                                <tr>
                                    {{-- KOLOM KIRI --}}
                                    <td width="50%" style="vertical-align: top; border-right: 2px solid black">
                                        <table width="100%">
                                            @for ($i = 0; $i < $count1; $i++)
                                                <tr>
                                                    <td colspan="1" style="width: 2px;vertical-align: baseline">~
                                                    </td>
                                                    <td colspan="4" class="style14"
                                                        style="text-align:left;font-size:12pt">
                                                        <b>{{ $items[$i]->product->code }}</b>

                                                        @if (!empty($items[$i]->product->name))
                                                            ({{ $items[$i]->product->name }})
                                                        @endif

                                                        @if (!empty($items[$i]->desc))
                                                            ({{ $items[$i]->desc }})
                                                        @endif
                                                        <span style="white-space: nowrap;"> =
                                                            {{ $items[$i]->qty }}</span>
                                                    </td>
                                                </tr>

                                                @if (!empty($items[$i]->lot))
                                                    <tr>
                                                        <td style="width: 2px"></td>
                                                        <td class="style14"
                                                            style="font-size:12pt;text-align: left;vertical-align: baseline;width: 20px;">
                                                            Lot</td>
                                                        <td style="width: 2px;vertical-align: baseline">:</td>
                                                        <td class="style14" style="font-size:12pt" colspan="2">
                                                            {!! nl2br(e($items[$i]->lot)) !!}</td>
                                                    </tr>
                                                @endif
                                            @endfor
                                        </table>
                                    </td>
                                    {{-- KOLOM KANAN --}}
                                    <td width="50%" style="vertical-align: top">
                                        <table width="100%">
                                            @for ($i = $count1; $i < $items->count(); $i++)
                                                <tr>
                                                    <td colspan="1" style="width: 2px;vertical-align: baseline">~
                                                    </td>
                                                    <td colspan="4" class="style14"
                                                        style="text-align:left;font-size:12pt;">
                                                        <b>{{ $items[$i]->product->code }}</b>

                                                        @if (!empty($items[$i]->product->name))
                                                            ({{ $items[$i]->product->name }})
                                                        @endif

                                                        @if (!empty($items[$i]->desc))
                                                            ({{ $items[$i]->desc }})
                                                        @endif
                                                        <span style="white-space: nowrap;"> =
                                                            {{ $items[$i]->qty }}</span>
                                                    </td>
                                                </tr>

                                                @if (!empty($items[$i]->lot))
                                                    <tr>
                                                        <td style="width: 2px"></td>
                                                        <td class="style14"
                                                            style="font-size:12pt;text-align: left;vertical-align: baseline;width: 20px;">
                                                            Lot</td>
                                                        <td style="width: 2px;vertical-align: baseline">:</td>
                                                        <td class="style14" style="font-size:12pt" colspan="2">
                                                            {!! nl2br(e($items[$i]->lot)) !!}</td>
                                                    </tr>
                                                @endif
                                            @endfor
                                        </table>
                                    </td>

                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif

                @if (!empty($koli->nilai))
                    <tr>
                        <td class="style18 kiri-dua atas-dua kanan-dua bawah-dua" colspan="4"
                            style="text-align: left; font-size: 12pt">NILAI BARANG : Rp.
                            {{ number_format($koli->nilai, 0, ',', '.') }}
                        </td>
                    </tr>
                @endif

                <tr>
                    <td style="height: 2px"></td>
                </tr>
                @if ($koli->is_do == 'yes' || ($data->total_koli > 1 && $ctn == 1))
                    <tr>
                        <td class="style70 kanan-dua kiri-dua bawah-dua atas-dua" colspan="4"
                            style="text-align: center">SURAT JALAN/DO</td>
                    </tr>
                @endif
                @if ($koli->is_pk == 'yes')
                    <tr>
                        <td class="style70 kanan-dua kiri-dua bawah-dua atas-dua" colspan="4"
                            style="text-align: center;">PACKING KAYU</td>
                    </tr>
                @endif
                @if ($koli->is_banting == 'yes')
                    <tr>
                        <td class="style70" colspan="4" style="height: 10pt"></td>
                    </tr>
                    <tr>
                        <td class="style70 kanan-dua kiri-dua bawah-dua atas-dua" colspan="4"
                            style="text-align: center;font-size: 61pt">JANGAN DIBANTING</td>
                    </tr>
                @endif

                @if (!empty($data->note))
                    <tr>
                        <td class="style22 kanan-dua kiri-dua bawah-dua atas-dua" colspan="4"
                            style="text-align: center; color: #FF0000">
                            {{ $data->note }}</td>
                    </tr>
                @endif

            </table>
        @endforeach
    @endforeach





</body>

<script>
    // Auto print saat halaman dimuat
    window.print();

    // Auto close tab setelah print selesai atau dibatalkan
    // Method 1: Menggunakan afterprint event (untuk browser modern)
    window.addEventListener('afterprint', function() {
        // window.close();
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
                // window.close();
            }, 100);
        }
    };
</script>

</html>

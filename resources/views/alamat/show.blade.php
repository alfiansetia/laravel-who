@php
    $ctn = is_numeric($data->koli) ? intval($data->koli) : 1;
    $start = 1;
    // if ($data->is_last_koli == 'yes') {
    //     $start = $ctn;
    //     $end = $ctn;
    // } else {
    //     $start = 1;
    //     $end = $ctn - 1;
    // }
    // if ($ctn < 2) {
    //     $end = 1;
    // }
    if ($ctn <= 1) {
        $start = 1;
        $end = 1;
    } elseif ($data->is_last_koli == 'yes') {
        $start = $ctn;
        $end = $ctn;
    } else {
        $start = 1;
        $end = $ctn - 1;
    }
@endphp

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
            font-size: 68pt;
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
        }
    </style>


    @for ($ct = $start; $ct <= $end; $ct++)
        <table style="width: 100%;" class="print-page">
            <tr>
                <td class="style20" colspan="3">KEPADA YTH,</td>
                <td class="style20 kiri-dua kanan-dua atas-dua" colspan="2" style="color: #FF0000;width: 30%">
                    {{ $data->ekspedisi }}
                </td>
            </tr>
            <tr>
                <td class="style22" rowspan="2" colspan="3">{{ strtoupper($data->tujuan) }}</td>
                <td class="style20 kiri-dua kanan-dua bawah-dua" style="color: #FF0000">
                    <div style="display: flex; justify-content: space-between; align-items: center; color: #FF0000;">
                        <span>{!! $data->koli < 1 ? '&nbsp;&nbsp;' : $data->koli !!} KOLI</span>
                        @if ($ctn > 1)
                            <span style="color: black; font-size: 16pt;padding-right: 5pt">(Ctn No:
                                {{ $ct }})</span>
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
                        <td class="style18" colspan="{{ $data->is_asuransi == 'yes' ? '3' : '4' }}">
                            {!! $line !!}
                        </td>
                        @if ($data->is_asuransi == 'yes')
                            <td class="style21 kiri-dua kanan-dua bawah-dua atas-dua" colspan="1"
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
            {{-- <tr>
                <td class="style18" colspan="3">{!! nl2br(e($data->alamat)) !!}</td>
                <td class="style18" colspan="1" style="vertical-align: top">ASURANSI</td>
            </tr> --}}
            @if (!empty($data->up))
                <tr>
                    <td class="style18" style="width: 17%">UP</td>
                    <td class="style18" colspan="3">: {{ $data->up }}</td>
                </tr>
            @endif
            @if (!empty($data->tlp))
                <tr>
                    <td class="style18" style="width: 17%">Tlp</td>
                    <td class="style18" colspan="3">: {{ $data->tlp }}</td>
                </tr>
            @endif
            <tr>
                <td class="style18" style="width: 17%">No DO</td>
                <td class="style18" colspan="3">: {{ $data->do }} @if (!empty($data->epur))
                        (<span style="font-size: 16pt">{{ $data->epur }}</span>)
                    @endif
                </td>
            </tr>
            @if (!empty($data->untuk))
                <tr>
                    <td class="style18">Untuk</td>
                    <td class="style18" colspan="3">: {{ $data->untuk }}</td>
                </tr>
            @endif
            <tr>
                <td colspan="4" style="border-bottom: 4px double black"></td>
            </tr>
            <tr>
                <td class="style12">FROM :</td>
                <td class="style12" colspan="3">PT MITRA ASA PRATAMA</td>
            </tr>
            <tr>
                <td></td>
                <td class="style12" colspan="3">MT.HARYONO SQUARE , JL. MT.HARYONO</td>
            </tr>
            <tr>
                <td></td>
                <td class="style12" colspan="3">KAV.10 NO: OF 01/06 BIDARA CINA</td>
            </tr>
            <tr>
                <td></td>
                <td class="style12" colspan="3">JATINEGARA - JAKARTA TIMUR</td>
            </tr>
            <tr>
                <td></td>
                <td class="style12" colspan="3">TELP. 021 - 89456598 / 021- 29067256 ~ 57 / FAX. 021 - 29067258</td>
            </tr>
            <tr>
                <td class="style28" colspan="4" style="text-align: center; color: #FF0000">HATI - HATI BARANG MUDAH
                    PENYOK</td>
            </tr>
            <tr>
                <td class="style18 kiri-dua atas-dua kanan-dua" colspan="4" style="text-align: left">PACKING LIST :
                </td>
            </tr>
            @foreach ($data->detail as $item)
                <tr>
                    <td class="style14 kanan-dua kiri-dua" colspan="4" style="text-align: left">~
                        {{ $item->product->code }}
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
                        <td class="style14 kiri-dua" style="text-align: right;vertical-align: top">Lot : </td>
                        <td class="style14 kanan-dua" colspan="3">{!! nl2br(e($item->lot)) !!}</td>
                    </tr>
                @endif
            @endforeach
            <tr>
                <td class="style14 kanan-dua kiri-dua bawah-dua" colspan="4">
                    @if (!empty($data->nilai))
                        ( Nilai Barang ~ Rp. {{ number_format($data->nilai, 0, ',', '.') }} )
                    @endif

                </td>
            </tr>
            <tr>
                <td style="height: 2px"></td>
            </tr>
            @if ($data->is_do == 'yes' || ($data->koli > 1 && $ct == 1))
                <tr>
                    <td class="style70 kanan-dua kiri-dua bawah-dua atas-dua" colspan="4" style="text-align: center">
                        SURAT
                        JALAN/DO</td>
                </tr>
            @endif
            @if ($data->is_pk == 'yes')
                <tr>
                    <td class="style70 kanan-dua kiri-dua bawah-dua atas-dua" colspan="4"
                        style="text-align: center;">
                        PACKING KAYU</td>
                </tr>
            @endif
            {{-- @if ($data->is_asuransi == 'yes')
                <tr>
                    <td class="style70 kanan-dua kiri-dua bawah-dua atas-dua" colspan="4"
                        style="text-align: center;">
                        ASURANSI</td>
                </tr>
            @endif --}}
            @if ($data->is_banting == 'yes')
                <tr>
                    <td class="style70" colspan="4" style="height: 10pt"></td>
                </tr>
                <tr>
                    <td class="style70 kanan-dua kiri-dua bawah-dua atas-dua" colspan="4"
                        style="text-align: center;font-size: 61pt">
                        JANGAN DIBANTING</td>
                </tr>
            @endif

            @if (!empty($data->note))
                {{-- <tr>
                    <td class="style70" colspan="4" style="height: 10pt"></td>
                </tr> --}}
                <tr>
                    <td class="style22 kanan-dua kiri-dua bawah-dua atas-dua" colspan="4"
                        style="text-align: center; color: #FF0000">
                        {{ $data->note }}</td>
                </tr>
            @endif

        </table>

    @endfor


</body>

<script>
    window.print()
</script>

</html>

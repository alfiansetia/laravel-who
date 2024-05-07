<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="author" content="ASA" />

</head>

<body>
    <style>
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
    </style>

    <table style="width: 100%;">
        <tr>
            <td class="style20" colspan="3">KEPADA YTH,</td>
            <td class="style20 kiri-dua kanan-dua atas-dua" colspan="2" style="color: #FF0000;width: 30%">
                {{ $data->ekspedisi }}
            </td>
        </tr>
        <tr>
            <td class="style20" rowspan="2" colspan="3">{{ strtoupper($data->tujuan) }}</td>
            <td class="style20 kiri-dua kanan-dua bawah-dua" style="color: #FF0000">
                {!! $data->koli < 1 ? '&nbsp&nbsp' : $data->koli !!} KOLI</td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <tr>
            <td class="style18" colspan="4">{!! nl2br(e($data->alamat)) !!}</td>
        </tr>
        @if (!empty($data->up))
            <tr>
                <td class="style18" style="width: 17%">UP</td>
                <td class="style18" colspan="3">: {{ $data->up }}</td>
            </tr>
        @endif
        <tr>
            <td class="style18" style="width: 17%">Tlp</td>
            <td class="style18" colspan="3">: {{ $data->tlp }}</td>
        </tr>
        <tr>
            <td class="style18" style="width: 17%">No DO</td>
            <td class="style18" colspan="3">: {{ $data->do }} @if (!empty($data->epur))
                    ( {{ $data->epur }})
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
            <td class="style18 kiri-dua atas-dua kanan-dua" colspan="4" style="text-align: left">PACKING LIST :</td>
        </tr>
        @foreach ($data->detail as $item)
            <tr>
                <td class="style14 kanan-dua kiri-dua" colspan="4" style="text-align: left">~
                    {{ $item->product->code }}
                    ({{ $item->product->name }})
                    = {{ $item->qty }}</td>
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
                    ( Nilai Barang ~ Rp. {{ $data->nilai }} )
                @endif

            </td>
        </tr>
        <tr>
            <td style="height: 2px"></td>
        </tr>
        @if ($data->is_do == 'yes')
            <tr>
                <td class="style70 kanan-dua kiri-dua bawah-dua atas-dua" colspan="4" style="text-align: center">
                    SURAT
                    JALAN/DO</td>
            </tr>
        @endif
        @if ($data->is_pk == 'yes')
            <tr>
                <td class="style70 kanan-dua kiri-dua bawah-dua atas-dua" colspan="4" style="text-align: center">
                    PACKING KAYU</td>
            </tr>
        @endif

    </table>


</body>

<script>
    window.print()
</script>

</html>

    <table style="width: 500px; white-space: nowrap; border-collapse: collapse;">
        <tr>
            <td colspan="6" style="text-align: center"><strong>KARTU STOK</strong></td>
        </tr>
        <tr></tr>
        <tr>
            <td>Tahun</td>
            <td>2025</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="5">Nama Barang : {{ $data->name }}</td>
            <td>Satuan : {{ $data->satuan }}</td>
        </tr>
        <tr>
            <td rowspan="2" style="border: 2px solid black">Tgl</td>
            <td rowspan="2" style="border: 2px solid black">Nama</td>
            <td colspan="2" style="border: 2px solid black">Mutasi</td>
            <td rowspan="2" style="border: 2px solid black">Saldo</td>
            <td rowspan="2" style="border: 2px solid black">Keterangan</td>
        </tr>
        <tr>
            <td style="border: 2px solid black">IN</td>
            <td style="border: 2px solid black">OUT</td>
        </tr>
        @php
            $saldo = 0;
        @endphp
        @foreach ($data->transactions as $trx)
            @php
                if ($trx->type == 'in') {
                    $saldo = $saldo + $trx->qty;
                } else {
                    $saldo = $saldo - $trx->qty;
                }
            @endphp
            <tr>
                <td style="border: 1px solid black">{{ $trx->date }}</td>
                <td style="border: 1px solid black">{{ $trx->pic }}</td>
                <td style="border: 1px solid black">{{ $trx->type == 'in' ? $trx->qty : '' }}</td>
                <td style="border: 1px solid black">{{ $trx->type == 'out' ? $trx->qty : '' }}</td>
                <td style="border: 1px solid black">{{ $saldo }}</td>
                <td style="border: 1px solid black">{{ $trx->desc }}</td>
            </tr>
        @endforeach
        @for ($i = 0; $i < 10; $i++)
            <tr>
                <td style="border: 1px solid black;height: 15pt;"></td>
                <td style="border: 1px solid black"> </td>
                <td style="border: 1px solid black"> </td>
                <td style="border: 1px solid black"> </td>
                <td style="border: 1px solid black"> </td>
                <td style="border: 1px solid black"> </td>
            </tr>
        @endfor
    </table>

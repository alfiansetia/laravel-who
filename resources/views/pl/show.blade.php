<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <title>Packing List {{ $product->code }}</title>
    <style>
        .table-bordered td.no-border-cell {
            border: none !important;
        }
    </style>

</head>

<body onload="window.print()">

    @php
        $count = intval(request()->query('copy', 1));
    @endphp

    @for ($l = 0; $l < $count; $l++)
        <div class="container">
            <img src="{{ asset('images/map.png') }}" width="100" alt="">
            <table>
                <tr>
                    <td>Pabrikan</td>
                    <td>:</td>
                    <td>{{ $product->vendor }}</td>
                </tr>
                <tr>
                    <td>Produk</td>
                    <td>:</td>
                    <td>{{ $product->code }} ({{ $product->name }})</td>
                </tr>
            </table>
            <table class="table table-sm table-bordered border-dark mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 30px">No</th>
                        <th>Item</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-center">Check List</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 1;
                    @endphp
                    @foreach ($product->pls as $item)
                        <tr>
                            <td class="text-center">{{ $i }}</td>
                            <td>{!! $item->item !!}</td>
                            <td class="text-center" style="width: 15%">{!! $item->qty !!}</td>
                            <td class="text-center" style="width: 15%"></td>
                        </tr>
                        @php
                            $i = $i + 1;
                        @endphp
                    @endforeach
                </tbody>
            </table>
            <div class="text-right mt-0">FORM/WH/009/20.1</div>
        </div>
    @endfor

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous">
    </script>

</body>

</html>

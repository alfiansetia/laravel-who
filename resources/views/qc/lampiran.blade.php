<table class="table table-sm table-bordered mb-4">
    <thead class="bg-light">
        <tr class="small font-weight-bold text-center">
            <th style="width: 50px;"><i class="fas fa-cog"></i></th>
            <th>KODE PRODUK</th>
            <th>NAMA PRODUK</th>
        </tr>
    </thead>
    <tbody class="small text-center">
        <tr>
            <td>
                <button class="btn btn-xs btn-outline-info btn-sm" id="a_copy" title="Copy Row">
                    <i class="fas fa-copy"></i>
                </button>
            </td>
            <td id="a_code" class="font-weight-bold"></td>
            <td id="a_name"></td>
        </tr>
    </tbody>
</table>

<table class="table table-sm table-bordered">
    <thead class="bg-light">
        <tr>
            <th colspan="5" class="py-2 px-3">
                <div class="d-flex align-items-center small font-weight-bold">
                    <button class="btn btn-xs btn-info btn-sm mr-3" id="l_copy">
                        <i class="fas fa-copy mr-1"></i>Copy Header & Data
                    </button>
                    <div id="l_code" class="mr-4"><i class="fas fa-barcode mr-1"></i> Kode Barang : </div>
                    <div id="l_name"><i class="fas fa-microscope mr-1"></i> Nama Barang : </div>
                </div>
            </th>
        </tr>
        <tr class="text-center small font-weight-bold">
            <th style="width: 50px;">NO</th>
            <th>SERIAL NUMBER / LOT</th>
            <th style="width: 80px;">OK</th>
            <th style="width: 80px;">ERROR</th>
            <th>KETERANGAN</th>
        </tr>
    </thead>
    <tbody class="small text-center">
        <tr>
            <td id="l_no">1</td>
            <td id="l_sn" class="font-weight-bold text-primary"></td>
            <td id="l_y"></td>
            <td id="l_n"></td>
            <td id="l_desc" class="text-left"></td>
        </tr>
    </tbody>
</table>

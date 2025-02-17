<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<button id="downloadExcel">Download Excel</button>
<table id="atkTable" border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Stok</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Pulpen</td>
            <td>50</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Kertas A4</td>
            <td>100</td>
        </tr>
    </tbody>
</table>
<script>
    document.getElementById('downloadExcel').addEventListener('click', function() {
        let table = document.getElementById('atkTable'); // Ambil tabel
        let workbook = XLSX.utils.table_to_book(table, {
            sheet: "Opname ATK"
        }); // Konversi ke workbook
        XLSX.writeFile(workbook, 'Opname_ATK.xlsx'); // Simpan sebagai file Excel
    });
</script>

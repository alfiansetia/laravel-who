# Component Catalog (Wajib Dipakai Ulang)

> Prinsip: kalau kebutuhanmu ada di tabel ini, pakai yang ini. Jangan bikin tandingan.

## A. Layout & Navigasi

| Komponen | Ganti Blade apa | Pakai di |
|---|---|---|
| `AppLayout.vue` | `template.blade.php` + `components/nav` + `components/breadcumb` + `#notif` | Semua `Pages/*` |
| `PageHeader.vue` | `.card-header.d-flex` + breadcrumb | Judul + aksi kanan (Tambah/Export/Filter) |
| `FilterBar.vue` | `.input-group` search + select status di header card | Opsional, untuk filter cepat |

## B. Data (inti anti-duplikasi)

| Komponen | Spesifikasi |
|---|---|
| `DataTable.vue` | Wrapper `@tanstack/vue-table` dengan dua mode. Props: `mode ('server'\\|'client')`, `query (useTableQuery)` untuk server / `table (useClientTable)` untuk client, `columns`, `rowKey`, `selectable`, `actions`. Fitur baku dua mode: skeleton loading, `EmptyState`, pagination SIMPLE (`Sebelumnya` + `Halaman X dari Y` + `Berikutnya`, tanpa nomor ellipsis), per-page `[10,25,50,100]`, info `Menampilkan A–B dari C`, seleksi massal (pengganti `multiCheck()` + `chk-parent`), slot `cell-*`. Dilarang pagination custom bernomor di `Pages/`. |
| `TablePagination.vue` | Dipakai di dalam `DataTable`, dilarang dipakai langsung di `Pages/`.
| `useClientTable.js` | Pasangan `useTableQuery` untuk mode client (pengganti `serverSide: false` + `table.search().draw()`). Fetch sekali per konteks filter (mis. lokasi stock), sisanya paging/search/sort lokal. |
| `SearchableSelect.vue` | Pengganti Select2. Wajib searchable + async (`useSearchable`). Lihat `frontend-standards.md §4`. |
| `FormField.vue` | Label + slot input + error. Props: `label`, `error`, `hint`, `required`. Semua field form wajib dibungkus ini. |
| `StatusBadge.vue` | Pengganti badge status acak. Props: `status`. Map warna + ikon Lucide terpusat. Tambah status baru = edit file ini, bukan di page. |
| `EmptyState.vue` | Props: `icon`, `title`, `message`, `action`. Dipakai tabel kosong, hasil search nol, error fetch. |
| `DatePicker.vue` | Satu picker project-wide (pengganti flatpickr mentah). Format tampil `d M Y`, emit `Y-m-d`. |

## C. Interaksi

| Komponen / Composable | Ganti fungsi Blade | Aturan |
|---|---|---|
| `AppModal.vue` | `vendor/modal.blade.php` dkk + `.modal-content` | Ukuran `sm/md/lg/xl`, fokus trap, tutup via Esc/X, footer baku Batal + Simpan. |
| `ConfirmDialog.vue` + `useConfirm()` | `confirmation()` iziToast | `await confirm({...})`, tone destructive untuk hapus. |
| `useToast()` (`vue-sonner`) | `show_message()`, `danger()`, `success()` | Satu-satunya cara toast. Jangan panggil `iziToast` langsung. |
| `useBlock()` | `bloc()/unbloc()`, `$.blockUI` | Overlay + skeleton untuk import/sync/download. |
| `useTableQuery.js` | `loadData()` + `renderPagination()` manual (`alamat`, `do`) | Mode server: state page/per_page/search/sort/filter, cancel request basi, sinkron URL. |
| `lib/export.js` | Tombol `copy`/`csv` DataTables + `btn-copy-row`/`btn_copy_lot` | `copyRows(rows)` (clipboard TSV) + `downloadCsv(filename, rows)`. Hanya mode client. Mode server lewat endpoint backend. |
| `useSearchable.js` | `$.ajax` di dalam Select2 | Debounce 300ms, minimum 1 huruf untuk async besar. |
| `lib/axios.js` | `$.ajaxSetup` + `ajaxSend/ajaxComplete` | Instance tunggal, error mapping ke toast + form. |
| `lib/format.js` | Helper tanggal/angka di Blade | `formatDate`, `formatNumber`, `formatBerat`, `truncate`. |

## D. shadcn-vue (`components/ui/`, hasil CLI)

`button`, `input`, `label`, `badge`, `dialog`, `alert-dialog`, `popover`, `command`, `select`, `calendar`, `table`, `pagination`, `dropdown-menu`, `tooltip`, `sonner`, `skeleton`, `checkbox`, `tabs`. Dilarang edit manual; kustomisasi via `design-system.md` + `StatusBadge`/`FormField`.

## E. Kapan Boleh Bikin Baru

1. Tidak ada di katalog + dipakai ≥2 page → buat di `components/` + daftarkan di file ini.
2. Dipakai 1 page saja → taruh di `Pages/<Modul>/partials/`, bukan `components/`.
3. Setiap komponen baru wajib: props terdokumentasi, contoh pakai, state loading + kosong + error.

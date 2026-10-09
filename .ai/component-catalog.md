# Component Catalog (Wajib Dipakai Ulang)

> Prinsip: kalau kebutuhanmu ada di tabel ini, pakai yang ini. Jangan bikin tandingan.

## A. Layout & Navigasi

| Komponen | Ganti Blade apa | Pakai di |
|---|---|---|
| `AppLayout.vue` | `template.blade.php` + `components/nav` + `components/breadcumb` + `#notif` | Semua `Pages/*` |
| `PageHeader.vue` | `.card-header.d-flex` + breadcrumb | Judul + aksi kanan (Tambah/Export/Filter) |
| `FilterPanel.vue` | `div.mb-3.grid` blok filter mentah per page | Wajib untuk semua filter page: Card TERTUTUP secara default (hemat tempat, klik untuk membuka) + badge jumlah filter aktif + tombol Reset bawaan. Isi hanya `FormField` + aksi `Terapkan`. Dilarang blok filter mentah di `Pages/`. |
| `FilterBar.vue` | `.input-group` search + select status di header card | Deprecated untuk page baru — pakai `FilterPanel`. Dipertahankan hanya untuk kompatibilitas. |
| `lib/menuIcons.js` | `data-lucide` + `lucide.createIcons()` di Blade | Registry ikon dinamis (string → komponen). Tambah ikon baru = named import di file ini, bukan `icons` map. |

## B. Data (inti anti-duplikasi)

| Komponen | Spesifikasi |
|---|---|
| `DataTable.vue` | Wrapper `@tanstack/vue-table` dengan dua mode. Props: `mode ('server'\\|'client')`, `query (useTableQuery)` untuk server / `table (useClientTable)` untuk client, `columns`, `rowKey`, `selectable`, `actions`. Fitur baku dua mode: skeleton loading, `EmptyState`, pagination SIMPLE (`Sebelumnya` + `Halaman X dari Y` + `Berikutnya`, tanpa nomor ellipsis), per-page `[10,25,50,100]`, info `Menampilkan A–B dari C`, seleksi massal (pengganti `multiCheck()` + `chk-parent`), slot `cell-*`. Dilarang pagination custom bernomor di `Pages/`. |
| `TablePagination.vue` | Dipakai di dalam `DataTable`, dilarang dipakai langsung di `Pages/`.
| `useClientTable.js` | Pasangan `useTableQuery` untuk mode client (pengganti `serverSide: false` + `table.search().draw()`). Fetch sekali per konteks filter (mis. lokasi stock), sisanya paging/search/sort lokal. |
| `SearchableSelect.vue` | Pengganti Select2. Wajib searchable + async (`useSearchable`). Lihat `frontend-standards.md §4`. |
| `MultiSelect.vue` | Pengganti `<select multiple>` (mis. filter lokasi Stock). Props: `modelValue[]`, `options`, `placeholder`. Fitur: searchable lokal, checkbox, badge `N dipilih`, Pilih semua/Hapus/Tutup. |
| `FormField.vue` | Label + slot input + error. Props: `label`, `error`, `hint`, `required`. Semua field form DAN filter wajib dibungkus ini. |
| `StatusBadge.vue` | Pengganti badge status acak. Props: `status`. Map warna + ikon Lucide terpusat. Tambah status baru = edit file ini, bukan di page. |
| `EmptyState.vue` | Props: `icon`, `title`, `message`, `action`. Dipakai tabel kosong, hasil search nol, error fetch. |
| `DatePicker.vue` | Satu picker project-wide (pengganti flatpickr mentah). Format tampil `d M Y`, emit `Y-m-d`. |

## C. Interaksi

| Komponen / Composable | Ganti fungsi Blade | Aturan |
|---|---|---|
| `AppModal.vue` | `vendor/modal.blade.php` dkk + `.modal-content` | Ukuran `sm/md/lg/xl`, fokus trap, tutup via Esc/X, footer baku Batal + Simpan. |
| `AuthModal.vue` | `components/auth.blade.php` + `#authModal` | Dibuka dari tombol Login di `AppLayout` + otomatis saat API 401 (interceptor `lib/axios.js` via `useAuthModal`). Sukses → `router.reload()`. |
| `NotifModal.vue` | `components/notif-modal.blade.php` + `#notifModal` | Dibuka dari tombol lonceng. Status via `StatusBadge`, aksi: Aktifkan/Tes Lokal/Tes FCM/Broadcast (broadcast hanya bila login). Hasil inline di modal, bukan toast. |
| `useAuthModal()` | `$('#authModal').modal('show')` global | Singleton `isOpen` — satu-satunya cara membuka modal login dari mana saja (termasuk interceptor 401). |
| `ConfirmDialog.vue` + `useConfirm()` | `confirmation()` iziToast | `await confirm({...})`, tone destructive untuk hapus. |
| `useToast()` (`vue-sonner`) | `show_message()`, `danger()`, `success()` | Satu-satunya cara toast. Jangan panggil `iziToast` langsung. `vue-sonner` hanya boleh diimpor di 2 file: `composables/useToast.js` (bungkus `success/error/warning/info` + `duration 4000` + `closeButton`) dan `AppLayout.vue` (`Toaster`). `lib/axios.js`, `lib/export.js`, dan semua `Pages/` WAJIB lewat `useToast()`. Dilarang `window.alert`, `toast` langsung dari `vue-sonner` di luar 2 file itu. |
| `useBlock()` + `BlockOverlay` | `bloc()/unbloc()`, `$.blockUI` | Overlay global (mount sekali di `AppLayout`, dilarang per-page). Axios: `block: true` per request user tanpa indikator inline; non-axios: `withBlock()`. Jangan block autocomplete/polling. |
| `useTableQuery.js` | `loadData()` + `renderPagination()` manual (`alamat`, `do`) | Mode server: state page/per_page/search/sort/filter, cancel request basi, sinkron URL. |
| `lib/export.js` | Tombol `copy`/`csv` DataTables + `btn-copy-row`/`btn_copy_lot` | `copyRows(rows)` (clipboard TSV) + `copyText(text)` (string mentah, mis. `code\tname` per baris atau summary textarea) + `downloadCsv(filename, rows)`. Hanya mode client. Mode server lewat endpoint backend. |
| `useSearchable.js` | `$.ajax` di dalam Select2 | Debounce 300ms, minimum 1 huruf untuk async besar. |
| `lib/axios.js` | `$.ajaxSetup` + `ajaxSend/ajaxComplete` | Instance tunggal, error mapping ke toast + form. 401 → buka `AuthModal` (kecuali request `auth/verify` sendiri). |
| `lib/fcm.js` | `components/notif.blade.php` (init + `refreshFcmToken` + `test_notif`) | Init FCM sekali (`initFcm(config)` dari shared props `firebase`), tanpa emoji. Config dishare backend, bukan hardcode. |
| `lib/format.js` | Helper tanggal/angka di Blade | `formatDate`, `formatNumber`, `formatBerat`, `truncate`. |
| `lib/odoo.js` | Render kolom khas Odoo (`_app.blade.php` 9 modul) | `odooName`, `truncate`, `formatQtyUS`/`formatQtyID`, `formatOdooDate`, `formatExpDate`, `getCode`, `getDesc`, `isPrinted`. Satu-satunya sumber — lihat `frontend-standards.md §7`. |

## D. shadcn-vue (`components/ui/`, hasil CLI)

`button`, `input`, `textarea`, `label`, `badge`, `dialog`, `alert-dialog`, `popover`, `command`, `select`, `calendar`, `table`, `pagination`, `dropdown-menu`, `tooltip`, `sonner`, `skeleton`, `checkbox`, `tabs`. Dilarang edit manual; kustomisasi via `design-system.md` + `StatusBadge`/`FormField`.

## E. Kapan Boleh Bikin Baru

1. Tidak ada di katalog + dipakai ≥2 page → buat di `components/` + daftarkan di file ini.
2. Dipakai 1 page saja → taruh di `Pages/<Modul>/partials/`, bukan `components/`.
3. Setiap komponen baru wajib: props terdokumentasi, contoh pakai, state loading + kosong + error.

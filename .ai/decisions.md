# Decisions (ADR)

## ADR-001: shadcn-vue sebagai satu-satunya UI kit
- Dipilih: `shadcn-vue` (New York, slate, radius 0.5).
- Ditolak: Bootstrap-Vue, Vuetify, ElementPlus, Tailwind mentah tanpa komponen.
- Alasan: copy-paste antar page hilang (button/input/table/modal satu varian), tabel gampang di-override via TanStack, hasil tidak terlihat "AI slop" karena token konsisten. Risiko: setup awal Tailwind + CLI 1–2 hari. Mitigasi: Fase 0 buktikan di pilot Vendor.

## ADR-002: vue-sonner (useToast) pengganti iziToast
- Dipilih: `vue-sonner` dibungkus `useToast()` dengan API `success/error/warning/info` + posisi `top-right` mengambang ala SweetAlert (`rich-colors`, `close-button`).
- Ditolak: panggil `iziToast` langsung dari Vue, `vue-toastification` (API beda jauh dari `show_message`), posisi `top-center` (menumpuk header sticky).
- Alasan: mapping 1:1 dari `show_message()/danger()/success()` Blade, flash Laravel otomatis jadi toast, `confirmation()` pindah ke AlertDialog (bukan toast question).
- Audit: import `vue-sonner` langsung di `lib/axios.js` dan import mati di `useConfirm.js` sudah dibersihkan — kini hanya `useToast.js` + `Toaster` di `AppLayout` yang boleh impor paket itu.

## ADR-003: TanStack Table dibungkus DataTable (dua mode, pagination simple)
- Dipilih: `@tanstack/vue-table` + `DataTable.vue` sendiri.
- Ditolak: DataTables jQuery dipertahankan, pakai tabel shadcn mentah per page.
- Alasan: server-side search/sort/page + seleksi massal (`multiCheck`) + skeleton/empty state terpusat. Blade DataTables buttons (Excel/PDF/Print) diganti dropdown export ke endpoint yang sudah ada.
- Revisi: satu komponen, dua mode (cerminan Blade — tabel utama `alamat`/DO pakai paging manual serverside `page/per_page/search`; stock/products/lot + sub-tabel modal product/lot pakai `serverSide: false` clientside + tombol export copy). Mode server = `useTableQuery` (request per halaman); mode client = `useClientTable` (fetch sekali, paging lokal, export Salin/CSV dari baris termuat via `lib/export.js`). Pagination dikunci SIMPLE (`Sebelumnya` + `Halaman X dari Y` + `Berikutnya`, tanpa nomor ellipsis ala `getPaginationPages()`); export mode server wajib lewat endpoint backend, dilarang ekspor halaman aktif seolah data penuh. Matriks mode per modul ada di `migration-plan.md` kolom Tabel.

## ADR-004: SearchableSelect wajib (pengganti Select2)
- Dipilih: Popover + Command shadcn + `useSearchable` (debounce 300ms, async).
- Ditolak: `<select>` native, pertahankan Select2 via wrapper jQuery.
- Alasan: Select2 tidak hidup di VDOM Vue; data besar (produk/alamat/koli) wajib async agar tidak preload ribuan option.

## ADR-005: Lucide, tanpa emoticon
- Dipilih: `@lucide/vue` + `StatusBadge` terpusat.
- Ditolak: FontAwesome di code baru, emoji sebagai status/ikon.
- Alasan: FontAwesome 5 via CDN tidak tree-shakeable; emoji tidak konsisten antar OS dan terlihat seperti AI slop. Map ikon modul mengikuti nav Blade lama.

## ADR-007: Upgrade Laravel 12 → 13 + Laravel Boost
- Dilakukan: `laravel/framework ^12.0 → ^13.0`, `php ^8.2 → ^8.3`, `laravel/tinker ^2.9 → ^3.0`, `phpunit/phpunit ^11 → ^12`, `barryvdh/laravel-debugbar ^3.16 → ^4.4` (v3 tidak dukung illuminate 13; v4 ganti namespace ke `Fruitcake\`, tidak ada referensi di `app/`, aman).
- Hasil: `v13.35.0`, `composer update` bersih, `php artisan route:list` OK, `php artisan test` 2 passed.
- Analisis dampak panduan upgrade resmi: tidak perlu perubahan code — tanpa referensi `VerifyCsrfToken`, `upsert()` sudah pakai `uniqueBy` non-kosong, prefix cache eksplisit di `config/cache.php`, cache hanya simpan string token, `config/session.php` tanpa key `serialization` (perilaku lama dipertahankan, user tidak logout massal).
- Boost: `laravel/boost ^2.10` + `boost:install` (guidelines, skills, MCP untuk OpenCode/Codex). Dokumen `.ai/` didaftarkan via `.ai/guidelines/00-index.md` agar otomatis terbaca agent.

## ADR-006: Hybrid Blade + Inertia, print tetap Blade
- Dipilih: migrasi per modul, `api.php` dibekukan, print/`firebase-messaging-sw`/`pwa` tetap Blade.
- Ditolak: big-bang rewrite, pindahkan print ke Vue-PDF.
- Alasan: risiko regresi 30+ modul terlalu besar; print butuh piksel presisi `window.print()` yang sudah jalan.

## ADR-008: FilterPanel + MultiSelect sebagai pola filter tunggal
- Dipilih: `FilterPanel.vue` (Card collapsible + badge `activeCount` + Reset bawaan) + `MultiSelect.vue` (pengganti `<select multiple>`) + `SearchableSelect` untuk select tunggal di filter.
- Ditolak: blok `div.mb-3.grid` filter mentah per page, `<select>` native di `Pages/`, `FilterBar` untuk page baru (deprecated, hanya kompatibilitas).
- Alasan: 9 modul Odoo duplikasi pola filter yang sama; panel collapsible menghemat tempat, badge memberi sinyal filter aktif, reset terpusat. Diterapkan ke Stock, Lot, ProductOdoo, Po, Ri, So, Do, It, Vendor.
- Revisi: default TERTUTUP (`defaultOpen: false`); debounce search/filter teks 1000ms (`useTableQuery.setSearch/setFilters(v, ms)`, `useClientTable.setSearch`), input pakai ref lokal agar ketikan tidak tertimpa; select instan. Gagal buka detail wajib toast error (jangan `catch` diam).

## ADR-010: Vendor dibaca live dari Odoo res.partner, tanpa migrasi/tabel lokal
- Dipilih: modul Vendor (`VendorOdooServices` + `GET /api/vendor-odoo`) baca langsung `res.partner` Odoo (`search_read` + `read`); list/detail di `Pages/Vendor/Index.vue`.
- Dilarang: migrasi baru untuk vendor (kolom snapshot, ubah FK `packs.vendor_id`, dsb.) dan baca/tulis tabel `vendors` lokal untuk kebutuhan vendor Odoo.
- Alasan: vendor adalah data master Odoo; snapshot lokal bikin divergensi (nama berubah di Odoo tidak ikut berubah) dan migrasi berisiko ke modul Pack yang masih pakai relasi `vendors`. Bug detail kosong kemarin murni parsing respons (`$response[0]` → `result.0`), bukan skema DB.
- Pengecualian sadar: tabel `vendors` + `Api\VendorController` tetap ada untuk kompatibilitas Blade Pack lama sampai modul Pack dimigrasi; jangan dijadikan sumber kebenaran baru.

## ADR-009: Parity render + aksi Blade di SPA Odoo (audit per kolom)
- Audit: tiap kolom tabel Blade (`partials/_table` + `scripts/_app`) dipetakan ke SPA — field, truncate, format, badge, aksi.
- Temuan & fix: Lot pakai field salah (`lot_id/qty_done` → `name/product_qty1/product_id`, tanpa kolom Expired); ProductOdoo kurang kolom AKL To; PO/RI/SO/DO/IT baca field salah (`vendor/user` → `partner_id[1]/user_id[1]`, RI `picking_count`→ kolom RI, SO kurang kolom DO, IT kurang SC + Note WH + Note IT); status RI/DO/IT dikembalikan ke teks polos (bukan badge); SO/DO badge `sistem`, format tanggal +7 jam, truncate customer 30; aksi SO 3 tombol dengan disabled `PRINT OK` + konfirmasi; gudang IT jadi ID numerik; modal detail jadi tab Product/Product Lot + filter kode + `aklMap` + ringkasan on-hand per lokasi; Vendor desc jadi textarea `maxlength=200`; Stock kode bold + badge qty-nol + badge AKL.
- Aturan: semua helper di `lib/odoo.js` + `frontend-standards.md §7`. Pengecualian sadar: export Excel/PDF/Print DataTables Blade tidak dibawa (mode client hanya Salin/CSV dari baris termuat, sisanya via endpoint backend sesuai ADR-003); tombol Move ProductOdoo (TODO di Blade) tampilkan modal move; tombol Detail Lot (dead path di Blade) diaktifkan karena API tersedia.

# Decisions (ADR)

## ADR-001: shadcn-vue sebagai satu-satunya UI kit
- Dipilih: `shadcn-vue` (New York, slate, radius 0.5).
- Ditolak: Bootstrap-Vue, Vuetify, ElementPlus, Tailwind mentah tanpa komponen.
- Alasan: copy-paste antar page hilang (button/input/table/modal satu varian), tabel gampang di-override via TanStack, hasil tidak terlihat "AI slop" karena token konsisten. Risiko: setup awal Tailwind + CLI 1–2 hari. Mitigasi: Fase 0 buktikan di pilot Vendor.

## ADR-002: vue-sonner (useToast) pengganti iziToast
- Dipilih: `vue-sonner` dibungkus `useToast()` dengan API `success/error/warning/info` + posisi `top-center`.
- Ditolak: panggil `iziToast` langsung dari Vue, `vue-toastification` (API beda jauh dari `show_message`).
- Alasan: mapping 1:1 dari `show_message()/danger()/success()` Blade, flash Laravel otomatis jadi toast, `confirmation()` pindah ke AlertDialog (bukan toast question).

## ADR-003: TanStack Table dibungkus DataTable
- Dipilih: `@tanstack/vue-table` + `DataTable.vue` sendiri.
- Ditolak: DataTables jQuery dipertahankan, pakai tabel shadcn mentah per page.
- Alasan: server-side search/sort/page + seleksi massal (`multiCheck`) + skeleton/empty state terpusat. Blade DataTables buttons (Excel/PDF/Print) diganti dropdown export ke endpoint yang sudah ada.

## ADR-004: SearchableSelect wajib (pengganti Select2)
- Dipilih: Popover + Command shadcn + `useSearchable` (debounce 300ms, async).
- Ditolak: `<select>` native, pertahankan Select2 via wrapper jQuery.
- Alasan: Select2 tidak hidup di VDOM Vue; data besar (produk/alamat/koli) wajib async agar tidak preload ribuan option.

## ADR-005: Lucide, tanpa emoticon
- Dipilih: `lucide-vue-next` + `StatusBadge` terpusat.
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

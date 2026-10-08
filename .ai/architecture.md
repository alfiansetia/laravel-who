# Arsitektur: Blade → Vue SPA dengan Inertia

## 1. Kondisi Saat Ini (Baseline)

- Laravel 12, PHP 8.2, Vite 5 (`vite.config.js`).
- UI lama: Bootstrap 4.6 + jQuery 3.7 + DataTables + Select2 (Bootstrap4 theme) + flatpickr + iziToast + FontAwesome 5. Layout: `resources/views/template.blade.php`.
- Pola lama per page: `index.blade.php` + `$.ajax` ke `routes/api.php` + `modal.blade.php` + helper global `show_message()`, `confirmation()`, `bloc()/unbloc()`, `multiCheck()`.
- Backend sudah terbelah: `app/Http/Controllers/Web/*` (return `view()`) dan `app/Http/Controllers/Api/*` (return JSON, dipakai juga oleh mobile/PWA). `routes/web.php` ~177 baris, `routes/api.php` ~344 baris.
- Print (`bast/print/*`, `so/print`, `do/print`, `sops/print`, `packs/print`) tetap server-rendered untuk `window.print()` / PDF.

## 2. Target

```
Browser
  └─ Vue 3 SPA (Vite) — dirender via Inertia
       ├─ Inertia::render() dari Web Controller (HTML pertama + JSON props)
       ├─ Navigasi SPA tanpa reload (Inertia visit, preserved state/scroll)
       └─ Data tabel/form via API JSON yang SUDAH ADA (tidak ditulis ulang)
Laravel
  ├─ Web Controller → tipis, hanya Inertia response + otorisasi + validasi
  ├─ Api Controller → tetap, sumber data tabel/modal (dipakai Blade lama + Vue baru)
  └─ Print routes → tetap Blade, jangan dimigrasi
```

## 3. Stack Baru (terkunci)

| Lapisan | Pilihan | Keterangan |
|---|---|---|
| Bridge | `inertiajs/inertia-laravel` + `@inertiajs/vue3` | Hybrid, `HandleInertiaRequests` middleware |
| UI | `shadcn-vue` (style New York) + Tailwind CSS | Satu-satunya sumber button/input/table/modal/badge |
| Tabel | `@tanstack/vue-table` dibungkus `DataTable.vue` | Ganti DataTables jQuery |
| Form select | `SearchableSelect.vue` (native shadcn Popover + Command) | Ganti Select2, wajib searchable + async |
| Toast/alert | `vue-sonner` dibungkus `useToast()` | Pengganti `show_message()` iziToast |
| Confirm | `AlertDialog` shadcn dibungkus `useConfirm()` | Pengganti `confirmation()` iziToast |
| Blocking | `useBlock()` (overlay shadcn) | Pengganti `bloc()/unbloc()` blockUI |
| Ikon | `lucide-vue-next` | Pengganti FontAwesome di code baru; tanpa emoticon |
| Util | `vueuse`, `axios` instance, `ziggy-js` | `route()` di Vue sama seperti Blade |
| Tanggal | `flatpickr` dibungkus ATAU `Calendar` shadcn | Satu saja per project, jangan campur |

Versi awal: Vue 3.4, Tailwind 3, shadcn-vue terbaru yang kompatibel. Jangan tambah UI kit lain (dilarang: ElementPlus, Quasar, Vuetify, Bootstrap-Vue).

## 4. Struktur Folder Baru

```
resources/js/
  app.js                  # entry: Vue + Inertia + Sonner + axios + ziggy
  lib/
    axios.js              # instance axios (base /api, CSRF, block, error → toast)
    format.js             # formatter tanggal/angka/berat (pengganti helper Blade)
  composables/
    useTableQuery.js      # state page/search/sort/filter → query API (dipakai DataTable)
    useSearchable.js      # debounce async options untuk SearchableSelect
    useToast.js           # success/error/warning/info (wajib dipakai)
    useConfirm.js         # dialog konfirmasi promise-based
    useBlock.js           # blocking overlay global
  components/
    ui/                   # hasil shadcn-vue CLI (button, input, dialog, badge, dll) — JANGAN edit manual
    AppLayout.vue         # pengganti template.blade.php + nav + breadcrumb
    PageHeader.vue        # judul + breadcrumb + aksi kanan
    DataTable.vue         # tabel standar (lihat component-catalog.md)
    SearchableSelect.vue  # select searchable standar
    FormField.vue         # label + error + slot input
    AppModal.vue          # modal form standar
    ConfirmDialog.vue     # dipakai useConfirm
    EmptyState.vue        # state kosong/error tabel
    StatusBadge.vue       # badge status dengan map warna + ikon
  Pages/
    Vendor/Index.vue      # 1 folder per modul, cerminan nama route web
    ...
```

Aturan: `Pages/` hanya komposisi + wiring. Logika tabel/select/toast/confirm tinggal di komponen/composable.

## 5. Alur Request

1. Visit awal: `GET /vendors` → `VendorController@index` → `Inertia::render('Vendor/Index', [...page props minimal...])`.
2. Interaksi tabel: `DataTable` → `useTableQuery` → `GET /api/vendors?page=&search=&sort=` → render rows. Tanpa reload Inertia.
3. Form: `AppModal` + `FormField` + `SearchableSelect` → `POST/PUT /api/...` via `lib/axios.js` → `useToast` → `router.reload({ only })` atau refetch query.
4. Flash Laravel (`success/error/message`) diteruskan sebagai shared props → otomatis jadi toast di `AppLayout`, bukan alert Blade.
5. Print: link biasa (`target=_blank`) ke route Blade print, di luar Inertia.

## 6. Strategi Hybrid (wajib)

- Blade lama dan Inertia hidup berdampingan. Migrasi per modul, bukan big-bang.
- `HandleInertiaRequests::share()` hanya menambah props, tidak mengubah response Blade.
- Route web modul yang sudah migrasi: controller ganti `view()` → `Inertia::render()`. Route yang belum: biarkan Blade.
- `api.php` JANGAN diubah strukturnya selama migrasi kecuali bug. Vue memakai kontrak yang sama dengan jQuery lama.

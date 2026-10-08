# Frontend Standards (Reusable Style + Script)

## 1. Aturan Emas Reusable

1. **Cek katalog dulu.** Butuh tabel/select/modal/toast/confirm/badge → ambil dari `component-catalog.md`. Bikin baru hanya jika tidak ada + tulis ADR.
2. **Page = komposisi.** `Pages/X/Index.vue` hanya: `AppLayout` + `PageHeader` + `FilterPanel` + `DataTable` + `AppModal`. Logika fetch/format/toast di `composables`/`lib`.
3. **Filter = satu pola.** Semua filter page WAJIB `FilterPanel` (default TERTUTUP, collapsible + badge `activeCount` + Reset) berisi `FormField` per field + tombol `Terapkan` di slot `actions`. Select tunggal pakai `SearchableSelect`, multi pakai `MultiSelect`. Dilarang blok `div.mb-3.grid` filter mentah dan `<select>` native di `Pages/`.
3. **Batas duplikasi: 2x.** Kode yang muncul di 2 page (kolom tabel, fetch options, format berat, validasi) wajib naik ke komponen/composable dalam PR yang sama.
4. **Tanpa style per-page.** Dilarang `<style scoped>` untuk button/input/table/modal di `Pages/`. Yang boleh: layout spacing page (`space-y-4`) + class Tailwind standar.
5. **Tanpa script mentah.** Dilarang `fetch()` langsung, `$.ajax`, `axios.get` tanpa instance, `alert/confirm`, `iziToast` langsung, `<select>` native, `<table>` mentah di `Pages/`.

## 2. Standar Script (Vue 3 Composition API)

- `<script setup>` + Composition API saja. Tidak ada Options API di code baru.
- Fetch: hanya via `lib/axios.js` (baseURL `/api`, header `X-Requested-With`, CSRF, interceptor error → `useToast`, hormati `isBlocking:false` ala ajax lama).
- Debounce search 1000ms (Odoo mahal, jangan per keystroke): `useTableQuery.setSearch(v)` dan `useClientTable.setSearch(v)` debounce default 1000ms; filter teks `setFilters(v, 1000)`. Select/dropdown tetap instan (`setFilters(v)` tanpa delay). Input teks WAJIB ref lokal (`searchBox`/`noteBox`) + handler, JANGAN bind langsung `:model-value="query.search.value"` (menimpa ketikan saat debounce berjalan).
- Gagal fetch detail WAJIB toast error (`toast.error(...)` di `catch`), jangan `catch {}` diam — agar penyebab "data tidak tampil" terlihat.
- State tabel, dua mode (detail di `design-system.md §3`):
  - `useTableQuery(apiFn)` = mode server (alamat, DO/PO/SO/RI/IT, BAST, pack, problem): state `page/per_page/search/sort/filter` → query API per halaman, cancel request basi.
  - `useClientTable(fetcher)` = mode client (stock, products, lot, sub-tabel modal product/lot): fetch sekali per konteks filter, paging/search/sort lokal via TanStack.
  - Dilarang akses `table.getState()` / fetch manual di `Pages/` untuk kebutuhan yang sudah dicover kedua composable ini.
- Inertia: navigasi via `router.visit` / `<Link>`, pertahankan state tabel (`preserveState`, `preserveScroll`, `only`). URL adalah sumber kebenaran filter.
- Validasi: error Laravel (422) dipetakan ke `FormField` per-field; error global → toast. Jangan tampilkan JSON mentah.

Contoh pola page (ringkas):

```vue
<script setup>
import { DataTable, PageHeader, AppModal, SearchableSelect } from '@/components'
import { useTableQuery, useToast } from '@/composables'
const query = useTableQuery((p) => axios.get('/vendors', { params: p }))
</script>
<template>
  <AppLayout><PageHeader title="Vendor" />
    <DataTable :query="query" :columns="columns" />
  </AppLayout>
</template>
```

## 3. Standar Style

- Hanya class Tailwind + varian shadcn (`variant="outline|destructive|ghost"`, `size="sm|icon"`). Jangan hardcode heksadesimal di `Pages/`; pakai token (`primary`, `muted`, `destructive`, `border`).
- Spacing page baku: wrapper `space-y-4`, toolbar `flex flex-wrap gap-2`, grid form `grid gap-4 sm:grid-cols-2`.
- Responsif wajib: tabel scroll horizontal di mobile + kolom penting di kiri; toolbar wrap (ganti media query 500px ala `template.blade.php` dengan flex-wrap Tailwind).

## 4. SearchableSelect (spesifikasi terkunci)

Props minimal: `modelValue`, `options | fetcher`, `placeholder`, `searchPlaceholder`, `loading`, `clearable`, `disabled`, `error`. Event: `update:modelValue`, `search`.
Perilaku: buka via Popover shadcn, ketik di Command input, Enter memilih, Esc menutup, pilihan menampilkan label + sublabel (mis. kode produk), empty text spesifik ("Produk tidak ditemukan").
Mode async: `fetcher(keyword, page)` + `useSearchable`, tampilkan skeleton 3 baris saat loading, jangan fetch saat popover tertutup.

## 5. Toast / Confirm / Block (pengganti iziToast & blockUI)

- `useToast()`: `toast.success|error|warning|info(message, title?)`. Judul default: Success/Caution/Hello/Error (samakan `show_message()` lama). Tampil mengambang di pojok kanan atas ala SweetAlert (`Toaster position="top-right" rich-colors close-button` di `AppLayout`).
- Flash Inertia (`success/error/message`) di-render otomatis jadi toast di `AppLayout`. Page tidak perlu handle manual.
- `useConfirm()`: `const ok = await confirm({ title:'Hapus vendor?', message:'...', confirmText:'Ya, hapus', tone:'destructive' })`. Ganti semua `confirmation(msg, cb)`.
- `useBlock()`: `withBlock(async () => ...)` untuk operasi berat (import/sync/download). Ganti `bloc()/unbloc()` + auto-unblock 60 dtk.
- Dilarang: `window.alert`, `window.confirm`, `iziToast.*` langsung di `Pages/`, alert HTML `#notif`, impor `vue-sonner` di luar `useToast.js` + `AppLayout.vue`.
- Audit jalur alert (wajib hijau): `grep -rin "vue-sonner" resources/js` hanya 2 hasil (`useToast.js`, `AppLayout.vue`); `grep -rin "window.alert\|window.confirm\|iziToast" resources/js/Pages resources/js/lib` nol hasil. Konfirm hapus pakai `useConfirm()` + `ConfirmDialog`, operasi berat pakai `useBlock()` + `BlockOverlay`.
- Anti-numpuk: toast copy (`copyRows`/`copyText`) pakai `id: 'copy'` + durasi 2 detik sehingga klik berulang menimpa toast yang sama; `Toaster expand=false` agar tumpukan menciut.

## 6. Ikon & Bahasa

- Ikon hanya komponen Lucide (`import { Truck } from '@lucide/vue'`). Dilarang emoji di template, string toast, maupun komentar. Ikon dinamis (nama string dari backend) wajib lewat registry `lib/menuIcons.js` (named import selektif, bukan `icons` map — agar tree-shakeable).
- Bahasa UI: Indonesia (ikuti Blade: "Tambah", "Simpan", "Batal", "Cari", "Hapus", "Ya, lanjutkan"). Jangan campur EN/ID dalam satu dialog.

## 7. Render Kolom Odoo (parity Blade — hasil audit `_app.blade.php`)

- Satu-satunya sumber helper: `lib/odoo.js`. Dilarang duplikasi `getCode/getDesc`, format tanggal, atau truncate di `Pages/`.
- Relasi Odoo `[id, "label"]` → `odooName()` (ambil `[1]`, fallback `-`). Berlaku: `partner_id`, `user_id`, `product_id`, `akl_id`, `location_id`, `lot_id`, `location_dest_id`.
- Truncate (tanpa tooltip, ikuti Blade): PO notes 40, RI `note_to_wh` 50, SO/DO `note_to_wh` 40, IT `note_to_wh`/`note_itr` 40, SO customer `substring(0,30)` tanpa `...`. Vendor NAME/DESC tanpa truncate. Lihat `truncate()` + pengecualian SO customer.
- Tanggal: `date_order`/`force_date` → `formatOdooDate()` (`DD/MM/YYYY HH:mm:ss`, +7 jam); `expired_date`/`itds_expired` → `formatExpDate()` (`YYYY.MM.DD`, kosong bila `False`/null); `x_studio_valid_to_akl` tampil mentah.
- Angka: Lot/ProductOdoo qty → `formatQtyUS()` (`en-US`, cerminan `hrg()`); Stock qty → `formatQtyID()`.
- Label `[KODE] Nama` → `getCode()`/`getDesc()` (regex kurung siku). Harga SO (`unit_price1`) tampil mentah, tanpa format ribuan.
- Status `state` (RI/DO/IT) = teks polos, BUKAN `StatusBadge` (Blade render teks). Badge `sistem` hanya SO/DO (`badge-sistem` uppercase, sembunyikan bila `-`/kosong).
- Kolom hitung di modal: `Qty Sisa = total - done` (PO: `product_qty - qty_received`; RI/DO/IT: `product_uom_qty - quantity_done`).
- `PRINT OK`: `isPrinted(note)` → tombol Mark Print disabled bila sudah print, Mark Unprint disabled bila belum (SO).
- Modal bertab (RI/DO/IT/ProductOdoo): tab Product + Product Lot, filter kode produk exact-match + Reset, AKL tab lot di-inject dari `aklMap` (API lot tak bawa AKL), footer Origin/PO + Notes (`whitespace-pre-wrap`).
- Opsi gudang IT = ID lokasi numerik (`5` CENTER, `760` BADSTOCK, `990` KARANTINA, `1310` CIBUBUR), default `5`. Jangan kirim string nama (API `intval()`).

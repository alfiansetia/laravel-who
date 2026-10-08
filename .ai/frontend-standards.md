# Frontend Standards (Reusable Style + Script)

## 1. Aturan Emas Reusable

1. **Cek katalog dulu.** Butuh tabel/select/modal/toast/confirm/badge → ambil dari `component-catalog.md`. Bikin baru hanya jika tidak ada + tulis ADR.
2. **Page = komposisi.** `Pages/X/Index.vue` hanya: `AppLayout` + `PageHeader` + `FilterBar?` + `DataTable` + `AppModal`. Logika fetch/format/toast di `composables`/`lib`.
3. **Batas duplikasi: 2x.** Kode yang muncul di 2 page (kolom tabel, fetch options, format berat, validasi) wajib naik ke komponen/composable dalam PR yang sama.
4. **Tanpa style per-page.** Dilarang `<style scoped>` untuk button/input/table/modal di `Pages/`. Yang boleh: layout spacing page (`space-y-4`) + class Tailwind standar.
5. **Tanpa script mentah.** Dilarang `fetch()` langsung, `$.ajax`, `axios.get` tanpa instance, `alert/confirm`, `iziToast` langsung, `<select>` native, `<table>` mentah di `Pages/`.

## 2. Standar Script (Vue 3 Composition API)

- `<script setup>` + Composition API saja. Tidak ada Options API di code baru.
- Fetch: hanya via `lib/axios.js` (baseURL `/api`, header `X-Requested-With`, CSRF, interceptor error → `useToast`, hormati `isBlocking:false` ala ajax lama).
- State server: `useTableQuery(apiFn)` untuk page/search/sort/filter + `useSearchable(fetcher)` untuk select async (debounce 300ms, cancel request basi).
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

- `useToast()`: `toast.success|error|warning|info(message, title?)`. Judul default: Success/Caution/Hello/Error (samakan `show_message()` lama). Posisi `top-center`.
- Flash Inertia (`success/error/message`) di-render otomatis jadi toast di `AppLayout`. Page tidak perlu handle manual.
- `useConfirm()`: `const ok = await confirm({ title:'Hapus vendor?', message:'...', confirmText:'Ya, hapus', tone:'destructive' })`. Ganti semua `confirmation(msg, cb)`.
- `useBlock()`: `withBlock(async () => ...)` untuk operasi berat (import/sync/download). Ganti `bloc()/unbloc()` + auto-unblock 60 dtk.
- Dilarang: `window.alert`, `window.confirm`, `iziToast.*` langsung di `Pages/`, alert HTML `#notif`.

## 6. Ikon & Bahasa

- Ikon hanya komponen Lucide (`import { Truck } from 'lucide-vue-next'`). Dilarang emoji di template, string toast, maupun komentar.
- Bahasa UI: Indonesia (ikuti Blade: "Tambah", "Simpan", "Batal", "Cari", "Hapus", "Ya, lanjutkan"). Jangan campur EN/ID dalam satu dialog.

# .ai — Dokumen Kerja Migrasi Blade → Vue SPA (Inertia)

Folder ini adalah sumber kebenaran untuk migrasi. Semua code baru WAJIB mengikuti dokumen di sini.

## Daftar Dokumen

| File | Isi |
|------|-----|
| `architecture.md` | Stack, struktur folder, alur request, strategi hybrid Blade + Inertia |
| `design-system.md` | Token visual shadcn, aturan anti-AI-slop, tabel, ikon (Lucide, tanpa emoticon) |
| `frontend-standards.md` | Aturan reusable style + script, larangan, cara pakai composable |
| `component-catalog.md` | Daftar komponen & composable reusable yang WAJIB dipakai tiap page |
| `backend-conventions.md` | Cara ubah Web Controller ke Inertia, shared props, validasi, print tetap Blade |
| `migration-plan.md` | Inventaris 30+ modul, batch prioritas, pilot, Definition of Done |
| `decisions.md` | ADR: kenapa shadcn-vue, Sonner, TanStack Table, Lucide |

## Cara Pakai

1. Sebelum garap modul baru, baca `migration-plan.md` → ambil batch berikutnya.
2. Baca `component-catalog.md` → cek komponen yang sudah ada. **Dilarang bikin komponen baru kalau yang dibutuhkan sudah ada.**
3. Ikuti `frontend-standards.md` + `design-system.md` saat coding.
4. Backend ikuti `backend-conventions.md`.
5. Kalau mau menyimpang, tulis dulu di `decisions.md` sebagai ADR baru.

## Prinsip Keras (non-negotiable)

1. **Reusable dulu.** Style maupun script tidak boleh diduplikasi antar page. Duplikasi 2x = refaktor ke `resources/js/components/` atau `resources/js/composables/`.
2. **UI = shadcn-vue.** Tidak ada custom CSS per-page untuk button/input/table/modal. Pakai token + varian yang sudah ada.
3. **Tabel = satu komponen.** Semua tabel pakai `DataTable` (TanStack Table). Tidak ada `<table>` mentah di page.
4. **Select = searchable.** Semua select pakai `SearchableSelect`. Tidak ada `<select>` native di form.
5. **Alert/toast = satu jalur.** Pengganti `show_message()`, `confirmation()`, `bloc()` Blade adalah `useToast()` + `useConfirm()` + `useBlock()` (basis `vue-sonner`). Tidak ada `alert()`, tidak ada iziToast langsung di page.
6. **Ikon = Lucide.** Tidak ada emoticon/emoji di UI maupun di code (❌ ✅ 🚀 dsb). Status pakai `StatusBadge` + ikon Lucide.
7. **Export = Salin + CSV.** Mode client hanya `copyRows` + `downloadCsv` dari `lib/export.js`. Dilarang membawa tombol Excel/PDF/Print DataTables Blade; butuh format lain wajib lewat endpoint backend.

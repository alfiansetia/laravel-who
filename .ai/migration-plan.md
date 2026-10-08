# Migration Plan (Batch per Modul)

## 1. Inventaris Modul Web (`routes/web.php`)

| # | Modul | Route | Kompleksitas | Catatan migrasi |
|---|---|---|---|---|
| 1 | Home / Monitor DO | `/`, `/home`, `monitor.do` | Rendah | Pilot kedua, bagus untuk `AppLayout` + `StatusBadge` |
| 2 | Vendor | `vendors.*` | Rendah | **PILOT #1** — CRUD + `modal.blade.php` paling bersih |
| 3 | SOP | `sops.*` (+print) | Rendah | Print tetap Blade |
| 4 | Kargan | `kargans.*` | Rendah | Form + download |
| 5 | ATK | `atk.*`, `atk-import` | Rendah | Tabel + import, pola untuk modul import lain |
| 6 | QC Lot | `qc_lots.*` | Rendah | Tabel + import |
| 7 | Izin Edar | `izin_edars.*` | Rendah | Sync progress → `useBlock` + polling |
| 8 | AKL + Items | `akls.*` | Sedang | `items.blade.php` + cek/apply produk |
| 9 | Kontak | `kontaks.*` | Rendah | Kecil, bisa gabung batch Vendor |
| 10 | Product Image | `product_images.*` | Sedang | Upload + collage/download tetap Blade |
| 11 | File Search | `tools.file_search.*` | Sedang | Upload + polling, pola untuk OCR/spreadsheet |
| 12 | Tools kecil | `stt, kalkulator, laporan-pengiriman, print-resi, sn, scoreboard, ocr, spreadsheet` | Rendah–Sedang | Batch gabungan, `laporan_luarkota` paling berat (tracking modal) |
| 13 | Shipping Estimate | `shipping_estimate.*` | Sedang | CRUD standar |
| 14 | Pack/PL | `packs.*` (+print) | Tinggi | Hitung koli, change, export/download |
| 15 | BAST | `basts.*` (+print) | Tinggi | Relasi detail + sync + download-zip |
| 16 | Alamat | `alamats.*` | Tinggi | Duplicate/sync + inputmask |
| 17 | Alamat Baru | `alamat-baru.*` | Sangat tinggi | `edit.blade.php` 1000+ baris, 15+ `$.ajax` — pecah jadi partials |
| 18 | DO / SO / IT | `do, so, it` (+print) | Tinggi | `_app.blade.php` + trace/monitor, pola Odoo |
| 19 | Stock / Lot / Product Odoo | `stock, lots, product-odoo` | Tinggi | On-hand/move/trace |
| 20 | PO / RI | `po, ri` | Tinggi | Order-line detail |
| 21 | Problem | `problems.*` | Tinggi | Next-number, duplicate, status, log |
| 22 | QC Form | `qc.*` | Sedang | SN/rekap/lampiran |
| 23 | Settings | `settings.*` | Rendah | Terakhir (berisiko, auth + FCM test) |

## 2. Urutan Batch

- **Fase 0 (3 hari):** Setup Inertia + shadcn-vue + `AppLayout/PageHeader/DataTable/SearchableSelect/FormField/AppModal/useToast/useConfirm/useBlock` + `Vendor/Index` sebagai pilot.
- **Fase 1 (2 minggu):** Vendor, Kontak, SOP, Kargan, ATK, QC Lot. Target: buktikan pola CRUD + tabel + modal + import.
- **Fase 2 (2–3 minggu):** AKL, Izin Edar, Shipping Estimate, Product Image, File Search, Tools kecil.
- **Fase 3 (3–4 minggu):** Pack, BAST, Alamat, DO/SO/IT, Stock/Lot/Product-Odoo, PO/RI, Problem, QC. Alamat Baru paling akhir di fase ini.
- **Fase 4 (1 minggu):** Settings, Home/Monitor, hapus jQuery per modul yang sudah migrasi, regresi + hapus Blade mati.

## 3. Definition of Done per Modul

1. Web route return `Inertia::render`, tanpa `view()` tersisa untuk modul itu (kecuali print).
2. Page hanya pakai komponen katalog (tanpa `<table>`/`<select>` mentah, tanpa `<style>` per-page, tanpa emoji).
3. Tabel server-side jalan (search/sort/page), ada skeleton + `EmptyState`, seleksi massal bila ada di Blade.
4. Form + `SearchableSelect` jalan (async bila data besar), validasi 422 tampil per-field + toast.
5. Toast/confirm/block via `useToast/useConfirm/useBlock` (tidak ada `alert/confirm/iziToast` langsung).
6. Responsif 390px + 1366px di-screenshot, ikon Lucide semua.
7. API lama tetap hijau (Blade lama/PWA tidak rusak bila modul masih hybrid).

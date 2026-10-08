# Design System (shadcn-vue, Anti-AI-Slop)

Tujuan: semua page terlihat seperti satu produk, bukan tempelan AI. Satu-satunya sumber styling adalah token shadcn + Tailwind. Custom CSS per-page dilarang kecuali tercantum di sini.

## 1. Token Dasar

- Base: `slate`, radius `0.5rem` (shadcn New York default). Jangan ubah radius per komponen.
- Font: Inter (body) + inherit system mono untuk kode/SN. Ukuran judul page: `text-xl font-semibold tracking-tight`, bukan `text-3xl gradient`.
- Warna brand ASA: pakai sebagai `primary` shadcn (nav active, button primary). Jangan pakai gradient `radial-gradient(circle at top right, #e0e7ff...)` ala `template.blade.php` di code baru.
- Mode: light-only dulu (ikuti Blade lama). Dark mode ditunda sampai semua modul migrasi.

## 2. Aturan Anti-AI-Slop (wajib lolos review)

DILARANG di code baru:
1. Gradient ungu-biru (`from-purple-* to-blue-*`, `bg-gradient-to-*`) untuk card/header/button.
2. Card ganda bertumpuk + `shadow-2xl rounded-2xl` di setiap section. Satu page = satu `Card` konten + `PageHeader` di luar.
3. Emoji/emoticon sebagai ikon atau status (`✅ ❌ 🚀 📦`). Pakai Lucide via `StatusBadge` (lihat bawah).
4. Placeholder generik ("Lorem ipsum", "John Doe", "Test 123") di UI final. `EmptyState` wajib kalimat spesifik modul.
5. Tabel mentah (`<table class="table table-bordered">`) hasil salinan Blade. Semua tabel via `DataTable`.
6. Inline `<style>` di `Pages/`. Styling hanya via class Tailwind + varian shadcn + `lib/format.js`.

Direview tiap PR: screenshot light 1366px + 390px wajib dilampirkan.

## 3. Tabel (DataTable — dua mode, satu komponen)

`DataTable` punya dua mode. Mode dipilih per modul (lihat `migration-plan.md` kolom Mode), bukan per selera. Tampilan, pagination, dan toolbar kedua mode WAJIB sama.

- **Mode `server`** (default) — data live per halaman. Dipakai: alamat, DO, PO, SO, RI, IT, BAST, pack, problem, dan semua tabel utama yang `loadData()`-nya kirim `page/per_page/search` (pola `alamat/index`, `do/scripts/_app`). Data diambil via `useTableQuery` → `GET /api/...?page=&per_page=&search=&sort=`. Kontrak respons mengikuti API lama: `{ data, page, total_pages, total }`.
- **Mode `client`** — dataset dimuat sekali per konteks filter, paging/search/sort di browser. Dipakai: stock, products/product-odoo, lot, dan sub-tabel modal (product picker, lot detail — pengganti `serverSide: false` + tombol export DataTables). Data diambil sekali via `useClientTable(fetcher)` lalu TanStack melakukan paginasi/sort/filter lokal. Alasan: dataset adalah snapshot yang perlu disalin/diexport utuh (copy/SN/lot), bukan live per halaman.

DILARANG mencampur logika mode di `Pages/`: page hanya deklarasi `mode=\"server|client\"`, sumber data, dan kolom.

### Pagination simple (wajib dua mode)

Dilarang pagination bernomor dengan ellipsis ala `getPaginationPages()` di `alamat/index.blade.php`. Satu-satunya pola pagination:

- Tombol `Sebelumnya` (`ChevronLeft`) + indikator `Halaman X dari Y` + tombol `Berikutnya` (`ChevronRight`). Di mobile icon-only, di desktop dengan label.
- Select per-halaman `[10, 25, 50, 100]` + info `Menampilkan A–B dari C data` (format `id-ID`, samakan `sInfo` Blade: `Menampilkan _START_ - _END_ dari _TOTAL_ data`).
- Mode server: pindah halaman = request API baru + `preserveScroll`; pertahankan `search/sort/page` di query Inertia. Mode client: pindah halaman tanpa request.
- Sembunyikan kontrol pagination bila total ≤ 1 halaman (ikuti `renderPagination` lama: kosongkan bila `totalPages <= 1`).

### Export / salin (beda mode, beda jalur)

- Mode `client` → dropdown `Download` mengekspor dari **baris yang sudah dimuat**: `Salin` (clipboard TSV, pengganti tombol `copy` DataTables + `btn-copy-row`) dan `CSV` (blob download). Butuh Excel/PDF → panggil endpoint backend seperti mode server.
- Mode `server` → dropdown `Download` memanggil **endpoint export/download backend yang sudah ada** (pengganti `trigger('exportExcel')`/route download). DILARANG mengekspor halaman aktif seolah-olah data penuh — toast warning bila backend belum sediakan export penuh.

## 4. Form & SearchableSelect

- Field: `FormField` (label `text-sm font-medium` + pesan error `text-xs text-destructive`). Input: `Input` shadcn. Jangan ubah `border-radius`/border per field.
- Select WAJIB searchable: ketik → filter lokal atau async (`useSearchable`, debounce 300ms), keyboard navigable, clear button, loading state. Nilai besar (produk, alamat, koli) selalu async via API, tidak preload ribuan option.
- Tanggal: satu komponen date picker project-wide. Format tampil `d M Y` (id), kirim `Y-m-d`.

## 5. Toast / Alert / Confirm

- Pengganti 1:1 Blade: `show_message(msg,'success')` → `toast.success(msg)`, `type error/warning/info` sama.
- `danger()/success()` alert HTML di `#notif` DILARANG di Vue. Semua jadi toast `vue-sonner` posisi `top-center` (samakan iziToast lama).
- `confirmation(msg, cb)` → `await confirm({ title, message })` (AlertDialog shadcn). Tombol: `Batal` (ghost) + `Ya, lanjutkan` (destructive/primary). Tidak ada `confirm()` native.
- Blocking `bloc()/unbloc()` → `useBlock()` overlay + skeleton. Auto-timeout 60 dtk dipertahankan.

## 6. Ikon (Lucide, Bukan Emoticon)

- Library tunggal: `@lucide/vue` (pengganti `lucide-vue-next` yang deprecated). Nama ikon PascalCase (`Truck`, `ClipboardCheck`, `FileText`, `Bell`, `Search`, `Printer`). Wajib nama kanonis — paket baru menghapus alias lama: pakai `Send` (bukan `PaperPlane`), `Megaphone` (bukan `Bullhorn`), `CircleCheck` (bukan `CheckCircle2`), `CircleX` (bukan `XCircle`). Verifikasi dengan `node -e "import('@lucide/vue').then(m => ...)"` bila ragu; build Vite gagal bila nama salah.
- Map modul mengikuti nav lama: Product=`Cube→Package`, Stock=`Boxes`, QC=`ClipboardCheck`, Alamat Baru=`Truck`, BAST=`FileText`, PL=`ListOrdered`, SOP=`Layers`, Odoo dropdown=`Database`, PO=`FileText`, RI=`Receipt`, SO=`ShoppingCart`, DO=`Truck`, IT=`ArrowLeftRight`.
- Status: `StatusBadge` (contoh: draft=slate, proses=amber, siap=blue, terkirim=green, batal=red) + ikon (`Clock`, `Loader`, `CheckCircle2`, `XCircle`). Tidak ada warna hardcode di page.

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

## 3. Tabel (DataTable)

- Tampilan: header `text-xs uppercase tracking-wide text-muted-foreground`, row `h-11`, hover `muted/50`, border `border-border`, density tunggal.
- Kolom aksi di kanan, icon-only button (`Pencil`, `Trash2`, `Eye`, `Printer`, `Download`) dengan `title`/tooltip. Tidak ada tombol teks berderet.
- Wajib: loading skeleton (bukan spinner fullscreen), `EmptyState` saat kosong, pagination + info "Menampilkan X–Y dari Z", pertahankan `search/sort/page` di query Inertia.
- Export (Excel/PDF/Print ala DataTables buttons) diganti 1 dropdown `Download` di `PageHeader`, memanggil endpoint export yang sudah ada.

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

- Library tunggal: `lucide-vue-next`. Nama ikon PascalCase (`Truck`, `ClipboardCheck`, `FileText`, `Bell`, `Search`, `Printer`).
- Map modul mengikuti nav lama: Product=`Cube→Package`, Stock=`Boxes`, QC=`ClipboardCheck`, Alamat Baru=`Truck`, BAST=`FileText`, PL=`ListOrdered`, SOP=`Layers`, Odoo dropdown=`Database`, PO=`FileText`, RI=`Receipt`, SO=`ShoppingCart`, DO=`Truck`, IT=`ArrowLeftRight`.
- Status: `StatusBadge` (contoh: draft=slate, proses=amber, siap=blue, terkirim=green, batal=red) + ikon (`Clock`, `Loader`, `CheckCircle2`, `XCircle`). Tidak ada warna hardcode di page.

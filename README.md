# Laravel WHO - Internal Management System

![Laravel](https://img.shields.io/badge/laravel-%23FF2D20.svg?style=for-the-badge&logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/mysql-%2300f.svg?style=for-the-badge&logo=mysql&logoColor=white)
![PWA](https://img.shields.io/badge/PWA-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)

Sebuah sistem manajemen internal berbasis Laravel yang dirancang untuk mengelola berbagai aspek operasional, termasuk produk, stok, quality control, dan integrasi dengan Odoo.

## 🚀 Fitur Utama

- **📦 Manajemen Produk & Stok**: Pelacakan inventaris secara real-time.
- **✅ Quality Control (QC)**:
    - Form QC & SOP QC.
    - **QC Lot**: Pengelolaan lot QC dengan fitur import Excel, deteksi duplikat otomatis (highlighting), dan inline editing.
- **🚚 Logistik & Alamat**:
    - Manajemen **Alamat Baru** untuk pengiriman.
    - Pembuatan **Packing List (PL)** dan **Berita Acara Serah Terima (BAST)**.
    - Cetak **Delivery Order (DO)** dan **Sales Order (SO)** dengan layout khusus.
- **🖇️ Integrasi Odoo**: Pengambilan data DO, SO, RI, dan PO langsung dari sistem Odoo.
- **🖊️ Manajemen ATK**: Pengelolaan Alat Tulis Kantor dengan sistem transaksi In/Out dan import data.
- **📱 PWA Support**: Dapat diinstal di perangkat mobile untuk akses cepat.

## �️ Console Monitoring

Proyek ini dilengkapi dengan sistem monitoring otomatis yang berjalan di background via Artisan Command untuk memantau data dari Odoo:

- **Monitor Delivery Order**:
  Menjalankan pengecekan DO baru secara real-time dari Odoo dan mengirimkan notifikasi ke Telegram & Firebase.
  ```bash
  php artisan app:monitor-do
  ```
  *Saran: Jalankan menggunakan Task Scheduler (Cron Job) setiap 1-5 menit.*

## �🛠️ Teknologi yang Digunakan

- **Backend**: Laravel 10/11+
- **Frontend**: Blade Template, Bootstrap 4, DataTables, Select2
- **Database**: MySQL / MariaDB
- **Library Tambahan**:
    - **SheetJS & XLSX**: Untuk proses data Excel di sisi client.
    - **Moment.js**: Untuk manajemen format tanggal.
    - **Daterangepicker**: Untuk input tanggal yang interaktif.

## ⚙️ Instalasi

1. Clone repositori:
   ```bash
   git clone https://github.com/alfiansetia/laravel-who.git
   ```
2. Instal dependencies:
   ```bash
   composer install
   npm install
   ```
3. Salin file `.env.example` ke `.env` dan sesuaikan konfigurasi database serta kredensial Odoo:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Jalankan migrasi:
   ```bash
   php artisan migrate
   ```
5. Jalankan server lokal:
   ```bash
   php artisan serve
   ```

## 📝 Catatan Pengembangan Terbaru
- Implementasi pemilihan sheet Excel sebelum proses import (ATK & QC Lot).
- Fitur highlight otomatis untuk Lot duplikat pada tabel QC Lot.
- Perbaikan layout cetak DO untuk mendukung format Lot.

---
Dikembangkan oleh **Alfian Setia**

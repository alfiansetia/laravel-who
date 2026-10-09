---
paths:
  - 'app/Http/Controllers/Alamat*.php, resources/js/Pages/Alamat*/**, resources/views/alamat*/**'
---

# Views

## Alamat vs AlamatBaru adalah modul berbeda
Alamat (Alamats, DetailAlamat, tanpa koli/BAST) dan AlamatBaru (AlamatBaru, Koli, BAST) adalah dua modul terpisah: controller, model, route, dan folder Pages/Vue berbeda. Show keduanya adalah template print mandiri (window.print) dan tetap Blade. Jangan menggabung atau menyamakan keduanya saat migrasi.

# Project Conventions — Laravel WHO (dibaca Boost)

Dokumen kerja migrasi Blade → Vue SPA (Inertia) ada di folder `.ai/` dan WAJIB diikuti:

- `.ai/README.md` — index + 6 prinsip keras (reusable, shadcn-vue, tabel tunggal, select searchable, toast tunggal, ikon Lucide tanpa emoticon)
- `.ai/architecture.md` — stack + struktur `resources/js/` + strategi hybrid Blade + Inertia
- `.ai/design-system.md` — token shadcn + aturan anti-AI-slop
- `.ai/frontend-standards.md` — aturan reusable style + script, spesifikasi `SearchableSelect`, pengganti iziToast/blockUI
- `.ai/component-catalog.md` — cek katalog ini SEBELUM bikin komponen baru
- `.ai/backend-conventions.md` — pola `view()` → `Inertia::render()`, print tetap Blade, `api.php` dibekukan
- `.ai/migration-plan.md` — urutan batch modul + Definition of Done
- `.ai/decisions.md` — ADR yang sudah disepakati

Aturan tambahan: scope ini Laravel 13 (PHP 8.3+). Jangan menyarankan API Laravel ≤12 tanpa memeriksa versi terpasang.

# Backend Conventions (Inertia)

## 1. Prinsip

Web Controller tetap tipis. Logika data tetap di Api Controller / Service / Model yang sudah ada. Migrasi = ganti `return view()` jadi `return Inertia::render()` + bagi props.

## 2. Pola Controller

```php
// SEBELUM (Blade)
public function index() {
    return view('vendor.index', ['title' => 'Vendor']);
}

// SESUDAH (Inertia)
use Inertia\Inertia;
public function index(Request $request) {
    return Inertia::render('Vendor/Index', [
        'title' => 'Vendor',
        // Jangan preload tabel di sini; tabel diambil via /api/vendors oleh useTableQuery.
        'filters' => $request->only(['search', 'page', 'sort']),
    ]);
}
```

- Nama page = nama modul PascalCase: `Vendor/Index`, `AlamatBaru/Edit`, `Do/Index`. Cerminkan `routes/web.php`.
- Form create/edit: kirim `record` + `options` kecil saja. Options besar (produk/alamat) diambil async via API oleh `SearchableSelect`, jangan dilempar semua ke props.

## 3. Shared Props (`HandleInertiaRequests`)

Wajib share: `auth` (user + flag `EnvAuth::check()` pengganti `btnEnvLogin/btnServerConfig`), `flash` (`success/error/message` → otomatis toast), `ziggy` (untuk `route()` di Vue), `firebaseConfig` (pengganti endpoint `api/firebase-config` bila perlu).

## 4. Validasi & Error

- Pakai `FormRequest` yang sudah ada. Error 422 otomatis ke `page.props.errors` → petakan ke `FormField`.
- Flash: `redirect()->back()->with('success', 'Vendor disimpan.')`. Jangan return JSON dari Web Controller.
- 403/404/419/500: pertahankan view `errors/*` Blade untuk request biasa; untuk Inertia, share `errorPage` agar `AppLayout` tampilkan `EmptyState` + tombol kembali.

## 5. Yang TIDAK Dimigrasi

- Print: `so.print`, `do.print`, `it.print`, `packs.print`, `sops.print`, `basts.print`, `product_images.collage/download` → tetap Blade + `target=_blank`.
- `firebase-messaging-sw.js`, `pwa/*`, `components/auth`, `components/notif*` → tetap Blade sampai PWA Vue siap; jangan duplikasi logika FCM.
- `api.php` → kontrak bekukan selama migrasi. Perubahan response harus backward-compatible dengan Blade lama + PWA.

## 6. Auth & Middleware

- `EnvAuth::check()` tetap di server. Jangan pindahkan cek login ke localStorage. `AppLayout` baca dari shared `auth`.
- Route web yang sudah Inertia + route Blade yang belum: tetap dalam middleware yang sama. Tidak ada auth ganda.

<!-- PWA: tombol install + registrasi satu-satunya service worker (/sw.js). -->
<button id="pwa-install-btn"
    style="display:none; position: fixed; bottom: 20px; right: 20px; padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 8px; z-index: 1000;"
    hidden>
    <i data-lucide="download" class="mr-1"></i>Install App
</button>

<script src="{{ asset('pwa-install.js') }}"></script>
<script>
    // Naikkan setiap mengubah resources/views/sw.blade.php agar klien update.
    const SW_VERSION = '2';
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register(`/sw.js?v=${SW_VERSION}`).then(
            () => console.log('Service worker PWA terdaftar'),
            (error) => console.error(`Service worker PWA gagal: ${error}`),
        );
    } else {
        console.error('Service workers tidak didukung.');
    }
</script>

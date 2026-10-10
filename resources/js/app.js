import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { createApp, h } from 'vue';
import '../css/app.css';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'MAP WHO';

createInertiaApp({
    title: (title) => (title ? `${title} — MAP WHO` : 'MAP WHO'),
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: 'hsl(221.2 83.2% 53.3%)',
    },
});

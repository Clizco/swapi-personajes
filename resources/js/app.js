import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

const nombreApp = import.meta.env.VITE_APP_NAME || 'Personajes Star Wars';

createInertiaApp({
    title: (titulo) => (titulo ? `${titulo} · ${nombreApp}` : nombreApp),
    resolve: (nombre) => {
        const paginas = import.meta.glob('./pages/**/*.vue', { eager: true });
        return paginas[`./pages/${nombre}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#f59e0b',
    },
});

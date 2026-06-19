import './bootstrap';
import '../css/app.css';
import 'sweetalert2/dist/sweetalert2.min.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { formatoFecha, fechaInput, fechaHoraInput, edadDesde } from './fechas';
import { alertaExito, alertaError, confirmar, alertaValidacion } from './alertas';

const appName = import.meta.env.VITE_APP_NAME || 'Veterinaria Aprendizaje';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Paginas/${name}.vue`, import.meta.glob('./Paginas/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);
        app.config.globalProperties.$formatoFecha = formatoFecha;
        app.config.globalProperties.$fechaInput = fechaInput;
        app.config.globalProperties.$fechaHoraInput = fechaHoraInput;
        app.config.globalProperties.$edadDesde = edadDesde;
        app.config.globalProperties.$alertaExito = alertaExito;
        app.config.globalProperties.$alertaError = alertaError;
        app.config.globalProperties.$confirmar = confirmar;
        app.config.globalProperties.$alertaValidacion = alertaValidacion;
        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

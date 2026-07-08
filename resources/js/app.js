import './bootstrap';
import '../css/app.css';
import 'sweetalert2/dist/sweetalert2.min.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { formatoFecha, fechaInput, fechaHoraInput, edadDesde } from './fechas';
import { alertaExito, alertaError, confirmar, alertaValidacion } from './alertas';
import TieneRol from './Componentes/TieneRol.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Veterinaria Aprendizaje';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Paginas/${name}.vue`, import.meta.glob('./Paginas/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);
        
        // Registrar componentes globales
        app.component('TieneRol', TieneRol);

        app.config.globalProperties.$formatoFecha = formatoFecha;
        app.config.globalProperties.$fechaInput = fechaInput;
        app.config.globalProperties.$fechaHoraInput = fechaHoraInput;
        app.config.globalProperties.$edadDesde = edadDesde;
        app.config.globalProperties.$alertaExito = alertaExito;
        app.config.globalProperties.$alertaError = alertaError;
        app.config.globalProperties.$confirmar = confirmar;
        app.config.globalProperties.$alertaValidacion = alertaValidacion;

        // Helpers de Autorización Reactivos
        app.config.globalProperties.$hasRole = function (roles) {
            const user = this.$page?.props?.auth?.user;
            if (!user || !user.rol) return false;
            const internalName = user.rol.nombre_interno;
            return Array.isArray(roles) ? roles.includes(internalName) : internalName === roles;
        };
        app.config.globalProperties.$isAdmin = function () {
            return this.$hasRole('admin');
        };
        app.config.globalProperties.$isVeterinario = function () {
            return this.$hasRole('veterinario');
        };
        app.config.globalProperties.$isCliente = function () {
            return this.$hasRole('cliente');
        };
        app.config.globalProperties.$isSecretaria = function () {
            return this.$hasRole('secretaria');
        };
        app.config.globalProperties.$isStaff = function () {
            return this.$hasRole(['admin', 'veterinario', 'secretaria']);
        };

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

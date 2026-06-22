import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],

    // -------------------------------------------------------
    // Configuración del servidor para Docker
    // -------------------------------------------------------
    server: {
        // Escuchar en todas las interfaces del contenedor
        host: '0.0.0.0',
        port: 5173,

        // HMR: decirle al NAVEGADOR que se conecte a localhost
        // (no a la IP interna del contenedor, que el browser no alcanza)
        hmr: {
            host: 'localhost',
            port: 5173,
        },

        // Observar cambios en archivos montados via volumen Docker
        watch: {
            usePolling: true,   // necesario en Linux con bind mounts
            interval: 300,      // ms entre comprobaciones
        },
    },
});

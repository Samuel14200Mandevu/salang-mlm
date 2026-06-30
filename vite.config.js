import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',  // Permet l'accès depuis le réseau
        port: 5173,
        cors: true,
        hmr: {
            host: '192.168.43.60', // Votre IP locale
        },
    },
});
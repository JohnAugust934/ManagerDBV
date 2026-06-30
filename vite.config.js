import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    // Fixa o dev server em localhost (IPv4). Sem isto o Vite publica o hot em
    // http://[::1]:5173 (loopback IPv6), que o navegador em http://localhost:8000
    // nao alcanca — resultando em paginas sem CSS/JS no dev.
    server: {
        host: 'localhost',
    },
});

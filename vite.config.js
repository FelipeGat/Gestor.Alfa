import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',

                // ATENDIMENTOS
                'resources/css/atendimentos/index.css',

                // ORCAMENTOS
                'resources/css/orcamentos/index.css',
                'resources/css/orcamentos/create.css',

                'resources/js/app.js',
                'resources/js/orcamento.js',
            ],
            refresh: true,
        }),
    ],
});

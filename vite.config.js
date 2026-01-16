import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',

                // ATENDIMENTOS
                'resources/css/atendimentos/index.css',
                'resources/css/atendimentos/create.css',
                'resources/css/atendimentos/edit.css',

                // ORCAMENTOS
                'resources/css/orcamentos/index.css',
                'resources/css/orcamentos/create.css',
                'resources/css/orcamentos/edit.css',

                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});

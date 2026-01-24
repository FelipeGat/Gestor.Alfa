import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',

                // ATENDIMENTOS
                'resources/css/atendimentos/index.css',
                'resources/css/atendimentos/edit.css',
                'resources/css/atendimentos/create.css',
                'resources/css/login/login.css',

                // ORCAMENTOS
                'resources/css/orcamentos/index.css',
                'resources/css/orcamentos/create.css',
                'resources/css/orcamentos/edit.css',

                // DASHBOARD
                'resources/css/dashboard/dashboard.css',

                'resources/js/app.js',
                'resources/js/orcamento.js',
                'resources/js/dashboard.js',
                'resources/js/cliente-busca.js',
            ],
            refresh: true,
        }),
    ],
});

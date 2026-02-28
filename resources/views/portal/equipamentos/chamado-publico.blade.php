<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abrir Chamado - {{ $equipamento->nome }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-green-50 min-h-screen">
    <div class="max-w-2xl mx-auto py-12 px-4">
        <!-- Card Principal -->
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Abrir Chamado</h1>
                <p class="text-gray-600 mt-2">Equipamento: <span class="font-semibold">{{ $equipamento->nome }}</span></p>
            </div>

            <!-- Info Equipamento -->
            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Setor</p>
                        <p class="font-medium">{{ $equipamento->setor->nome ?? 'Não informado' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Responsável</p>
                        <p class="font-medium">{{ $equipamento->responsavel->nome ?? 'Não informado' }}</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            @auth
                <form action="{{ route('portal.equipamento.chamado.store', $equipamento->qrcode_token) }}" method="POST">
                    @csrf
                    <input type="hidden" name="equipamento_id" value="{{ $equipamento->id }}">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição do Problema</label>
                            <textarea name="descricao" rows="4" required
                                class="w-full rounded-lg border-gray-300 border px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Descreva o problema que está ocorrendo com o equipamento..."></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prioridade</label>
                            <select name="prioridade" required
                                class="w-full rounded-lg border-gray-300 border px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="baixa">Baixa</option>
                                <option value="media" selected>Média</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>

                        <button type="submit"
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-all">
                            Abrir Chamado
                        </button>
                    </div>
                </form>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-600 mb-4">Você precisa estar logado para abrir um chamado.</p>
                    <a href="{{ route('login') }}" 
                        class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Fazer Login
                    </a>
                </div>
            @endauth
        </div>

        <!-- Footer -->
        <p class="text-center text-sm text-gray-500 mt-8">
            &copy; {{ date('Y') }} Gestor Alfa - Sistema de Gestão
        </p>
    </div>
</body>
</html>

<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    <style>
        .form-section {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
        }
        input[type="text"],
        input[type="email"],
        select {
            font-family: Inter, sans-serif !important;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        select:focus {
            border-color: #3f9cae !important;
            box-shadow: 0 0 0 1px rgba(63, 156, 174, 0.2) !important;
        }
        .pagination-link {
            border-radius: 9999px !important;
            min-width: 40px;
            text-align: center;
        }
        table thead th {
            color: rgb(17, 24, 39) !important;
        }
        table tbody td {
            font-weight: 400 !important;
        }
        table tbody td.font-medium {
            font-weight: 400 !important;
        }
        table tbody td:nth-child(2) {
            font-family: 'Inter', sans-serif !important;
            font-weight: 500 !important;
            color: rgb(17, 24, 39) !important;
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Usuários']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ================= FILTROS ================= --}}
            <form method="GET" class="form-section" style="padding: 1.5rem;">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">

                    <div class="flex flex-col lg:col-span-6">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Pesquisar Usuários
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome ou E-mail"
                            class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2 text-sm">
                    </div>

                    <div class="flex flex-col lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select name="status" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2 text-sm">
                            <option value="">Todos</option>
                            <option value="ativo" @selected(request('status')=='ativo' )>Ativo</option>
                            <option value="inativo" @selected(request('status')=='inativo' )>Inativo</option>
                            <option value="primeiro acesso" @selected(request('status')=='primeiro_acesso' )>Primeiro
                                Acesso</option>
                        </select>
                    </div>

                    <div class="flex items-end lg:col-span-2">
                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #3f9cae; border-radius: 9999px;">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                    clip-rule="evenodd" />
                            </svg>
                            Filtrar
                        </button>
                    </div>

                </div>
            </form>

            {{-- ================= RESUMO ================= --}}
            <style>
            @media (min-width: 1024px) {
                .resumo-grid {
                    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                }
            }
            </style>

            <div class="resumo-grid gap-4 mb-6" style="
                display: grid !important;
                grid-template-columns: repeat(1, minmax(0, 1fr));">
                <div class="bg-white p-6 rounded-lg border-l-4 border-blue-600 w-full max-w-none" style="border-top: 1px solid #2563eb; border-right: 1px solid #2563eb; border-bottom: 1px solid #2563eb; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total de Usuarios</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ $totalUsuarios }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-lg border-l-4 border-green-600 w-full max-w-none" style="border-top: 1px solid #16a34a; border-right: 1px solid #16a34a; border-bottom: 1px solid #16a34a; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Ativos</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $usuariosAtivos }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-lg border-l-4 border-red-600 w-full max-w-none" style="border-top: 1px solid #dc2626; border-right: 1px solid #dc2626; border-bottom: 1px solid #dc2626; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Primeiro Acesso</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">
                        {{ $usuariosInativos }}
                    </p>
                </div>
            </div>

            @if(auth()->user()->canPermissao('clientes', 'incluir'))
            <div class="flex justify-start" style="margin-bottom: -1rem;">
                <a href="{{ route('usuarios.create') }}" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px;">
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Adicionar
                </a>
            </div>
            @endif

            {{-- TABELA --}}
            <div class="bg-white rounded-lg overflow-hidden" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead style="background-color: rgba(63, 156, 174, 0.05); border-bottom: 1px solid #3f9cae;">
                            <tr>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Nome</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Email</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Tipo</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Status</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Ações</th>
                            </tr>

                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @forelse($usuarios as $usuario)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $usuario->name }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    {{ $usuario->email }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    {{ ucfirst($usuario->tipo) }}
                                </td>

                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    @if(!$usuario->primeiro_acesso)
                                        <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-semibold" style="width: 130px; justify-content: center; background-color: #dcfce7; color: #166534;">
                                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            {{ strtoupper('Ativo') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-semibold" style="width: 130px; justify-content: center; background-color: #fef3c7; color: #92400e;">
                                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                            {{ strtoupper('Primeiro acesso') }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    <div class="table-actions">
                                        <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-sm btn-edit"
                                            style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;">
                                            <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                <path
                                                    d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center">
                                    <h3 class="text-lg font-medium text-gray-900">Nenhum usuário encontrado</h3>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ================= PAGINAÇÃO ================= --}}
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div class="pagination-info">
                    Mostrando
                    <strong>{{ $usuarios->firstItem() ?? 0 }}</strong>
                    –
                    <strong>{{ $usuarios->lastItem() ?? 0 }}</strong>
                    de
                    <strong>{{ $usuarios->total() }}</strong>
                    usuários
                </div>

                <div class="pagination-links">
                    @if($usuarios->onFirstPage())
                    <span class="pagination-link disabled">← Anterior</span>
                    @else
                    <a href="{{ $usuarios->previousPageUrl() }}" class="pagination-link">← Anterior</a>
                    @endif

                    @foreach($usuarios->getUrlRange(1, $usuarios->lastPage()) as $page => $url)
                    @if($page == $usuarios->currentPage())
                    <span class="pagination-link active">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                    @endif
                    @endforeach

                    @if($usuarios->hasMorePages())
                    <a href="{{ $usuarios->nextPageUrl() }}" class="pagination-link">Próximo →</a>
                    @else
                    <span class="pagination-link disabled">Próximo →</span>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

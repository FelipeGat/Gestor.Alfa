<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    <style>
        input[name="search"] {
            border-color: #d1d5db !important;
        }
        input[name="search"]:focus {
            border-color: #3f9cae !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #3f9cae !important;
        }
        select[name="status"] {
            border-color: #d1d5db !important;
        }
        select[name="status"]:focus {
            border-color: #3f9cae !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #3f9cae !important;
        }
        .tabela-funcionarios thead th {
            color: rgb(17, 24, 39) !important;
        }
        .tabela-funcionarios tbody td {
            font-weight: 400 !important;
        }
        .tabela-funcionarios tbody td.font-medium {
            font-weight: 400 !important;
        }
        .tabela-funcionarios tbody td:nth-child(3) {
            font-family: 'Inter', sans-serif !important;
            font-weight: 500 !important;
            color: rgb(17, 24, 39) !important;
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Funcionários']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ================= FILTROS ================= --}}
            <form method="GET" class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">

                    <div class="flex flex-col lg:col-span-6">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Pesquisar Funcionário
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Nome do funcionário" class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:border-[#3f9cae]">
                    </div>

                    <div class="flex flex-col lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae]">
                            <option value="">Todos</option>
                            <option value="ativo" @selected(request('status')=='ativo' )>Ativo</option>
                            <option value="inativo" @selected(request('status')=='inativo' )>Inativo</option>
                        </select>
                    </div>

                    <div class="flex items-end lg:col-span-3 gap-2">
                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; width: 130px; justify-content: center; background: #3f9cae; border-radius: 9999px;">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                    clip-rule="evenodd" />
                            </svg>
                            Filtrar
                        </button>
                        <a href="{{ route('funcionarios.index') }}" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; width: 130px; justify-content: center; background: #9ca3af; border-radius: 9999px; box-shadow: 0 2px 4px rgba(156, 163, 175, 0.3); text-decoration: none;" onmouseover="this.style.boxShadow='0 4px 6px rgba(156, 163, 175, 0.4)'" onmouseout="this.style.boxShadow='0 2px 4px rgba(156, 163, 175, 0.3)'">
                            <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            Limpar
                        </a>
                    </div>

                </div>

            </form>

            {{-- ================= RESUMO ================= --}}
            <style>
            @media (min-width: 1024px) {
                .resumo-funcionarios {
                    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                }
            }
            </style>

            <div class="resumo-funcionarios gap-4" style="
                display: grid !important;
                grid-template-columns: repeat(1, minmax(0, 1fr));">

                <div class="bg-white p-6 rounded-lg border-l-4 border-blue-600 w-full" style="border-top: 1px solid #2563eb; border-right: 1px solid #2563eb; border-bottom: 1px solid #2563eb; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">
                        Total de Funcionários
                    </p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ $totais['total'] }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-lg border-l-4 border-green-600 w-full" style="border-top: 1px solid #16a34a; border-right: 1px solid #16a34a; border-bottom: 1px solid #16a34a; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">
                        Ativos
                    </p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $totais['ativos'] }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-lg border-l-4 border-red-600 w-full" style="border-top: 1px solid #dc2626; border-right: 1px solid #dc2626; border-bottom: 1px solid #dc2626; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">
                        Inativos
                    </p>
                    <p class="text-3xl font-bold text-red-600 mt-2">
                        {{ $totais['inativos'] }}
                    </p>
                </div>

            </div>

            @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('funcionarios','incluir'))
            <div class="flex justify-start" style="margin-bottom: -1rem;">
                <a href="{{ route('funcionarios.create') }}" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px;">
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Adicionar
                </a>
            </div>
            @endif

            {{-- ================= TABELA ================= --}}
            <div class="bg-white rounded-lg overflow-hidden" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto tabela-funcionarios">
                        <thead style="background-color: rgba(63, 156, 174, 0.05); border-bottom: 1px solid #3f9cae;">
                            <tr>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">ID</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Nome</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Email</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Status</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Ações</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @forelse($funcionarios as $funcionario)
                            <tr class="hover:bg-gray-50 transition">

                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $funcionario->id }}
                                </td>

                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $funcionario->nome }}
                                </td>

                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $funcionario->user->email ?? '—' }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    <x-status-badge :ativo="$funcionario->ativo" />
                                </td>

                                <td class="px-4 py-3">
                                    <div class="table-actions">

                                        @if(auth()->user()->isAdminPanel() ||
                                        auth()->user()->canPermissao('funcionarios','editar'))
                                        <a href="{{ route('funcionarios.edit', $funcionario) }}" class="btn btn-sm btn-edit"
                                            style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;">
                                            <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                <path
                                                    d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>
                                        @endif

                                        @if(auth()->user()->isAdminPanel() ||
                                        auth()->user()->canPermissao('funcionarios','excluir'))
                                        <form action="{{ route('funcionarios.destroy', $funcionario) }}" method="POST"
                                            onsubmit="return confirm('Deseja excluir este Funcionário?')"
                                            style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-delete"
                                                style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;">
                                                <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                    <path fill-rule="evenodd"
                                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                        @endif

                                    </div>
                                </td>

                            </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                        Nenhum funcionário cadastrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($funcionarios->hasPages())
            <style>
                .pagination-wrapper {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 1.5rem;
                    background: white;
                    border-radius: 0.75rem;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                    margin-bottom: 2rem;
                    flex-wrap: wrap;
                    gap: 1rem;
                }
                .pagination-info {
                    font-size: 0.875rem;
                    color: #6b7280;
                }
                .pagination-links {
                    display: flex;
                    gap: 0.5rem;
                    flex-wrap: wrap;
                }
                .pagination-link {
                    padding: 0.5rem 0.75rem;
                    border: 1px solid #d1d5db;
                    border-radius: 0.375rem;
                    font-size: 0.875rem;
                    font-weight: 600;
                    color: #374151;
                    background: white;
                    cursor: pointer;
                    transition: all 0.2s;
                    text-decoration: none;
                }
                .pagination-link:hover {
                    border-color: #3f9cae;
                    color: #3f9cae;
                    background: #f0f9ff;
                }
                .pagination-link.active {
                    background: #3f9cae;
                    color: white;
                    border-color: #3f9cae;
                }
                .pagination-link.disabled {
                    opacity: 0.5;
                    cursor: not-allowed;
                    pointer-events: none;
                }
            </style>
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Mostrando <strong>{{ $funcionarios->count() }}</strong> de
                    <strong>{{ $funcionarios->total() }}</strong>
                    funcionários
                </div>
                <div class="pagination-links">
                    @if($funcionarios->onFirstPage())
                    <span class="pagination-link disabled">← Anterior</span>
                    @else
                    <a href="{{ $funcionarios->previousPageUrl() }}" class="pagination-link">← Anterior</a>
                    @endif
                    @foreach($funcionarios->getUrlRange(1, $funcionarios->lastPage()) as $page => $url)
                    @if($page == $funcionarios->currentPage())
                    <span class="pagination-link active">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                    @endif
                    @endforeach
                    @if($funcionarios->hasMorePages())
                    <a href="{{ $funcionarios->nextPageUrl() }}" class="pagination-link">Próximo →</a>
                    @else
                    <span class="pagination-link disabled">Próximo →</span>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>

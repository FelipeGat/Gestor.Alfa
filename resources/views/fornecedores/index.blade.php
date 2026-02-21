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
        select[name="ativo"] {
            border-color: #d1d5db !important;
        }
        select[name="ativo"]:focus {
            border-color: #3f9cae !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #3f9cae !important;
        }
        .tabela-fornecedores thead th {
            color: rgb(17, 24, 39) !important;
        }
        .tabela-fornecedores tbody td {
            font-weight: 400 !important;
        }
        .tabela-fornecedores tbody td.font-medium {
            font-weight: 400 !important;
        }
        .tabela-fornecedores tbody td:nth-child(2),
        .tabela-fornecedores tbody td:nth-child(3) {
            font-family: 'Inter', sans-serif !important;
            font-weight: 500 !important;
            color: rgb(17, 24, 39) !important;
        }
        .pagination-link {
            border-radius: 9999px !important;
            min-width: 40px;
            text-align: center;
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Fornecedores']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ================= FILTROS ================= --}}
            <form method="GET" class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">

                    <div class="flex flex-col lg:col-span-6">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Pesquisar Fornecedor
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Razão Social, Nome Fantasia ou CNPJ/CPF" class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:border-[#3f9cae]">
                    </div>

                    <div class="flex flex-col lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select name="ativo" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae]">
                            <option value="">Todos</option>
                            <option value="1" @selected(request('ativo')=='1' )>Ativo</option>
                            <option value="0" @selected(request('ativo')=='0' )>Inativo</option>
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
                        <a href="{{ route('fornecedores.index') }}" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; width: 130px; justify-content: center; background: #9ca3af; border-radius: 9999px; box-shadow: 0 2px 4px rgba(156, 163, 175, 0.3); text-decoration: none;" onmouseover="this.style.boxShadow='0 4px 6px rgba(156, 163, 175, 0.4)'" onmouseout="this.style.boxShadow='0 2px 4px rgba(156, 163, 175, 0.3)'">
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
                .resumo-grid {
                    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                }
            }
            </style>

            <div class="resumo-grid gap-4 mb-6" style="
                display: grid !important;
                grid-template-columns: repeat(1, minmax(0, 1fr));">
                <div class="bg-white p-6 rounded-lg border-l-4 border-blue-600 w-full max-w-none" style="border-top: 1px solid #2563eb; border-right: 1px solid #2563eb; border-bottom: 1px solid #2563eb; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total de Fornecedores</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ $fornecedores->total() }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-lg border-l-4 border-green-600 w-full max-w-none" style="border-top: 1px solid #16a34a; border-right: 1px solid #16a34a; border-bottom: 1px solid #16a34a; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Ativos</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $fornecedores->where('ativo', 1)->count() }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-lg border-l-4 border-red-600 w-full max-w-none" style="border-top: 1px solid #dc2626; border-right: 1px solid #dc2626; border-bottom: 1px solid #dc2626; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Inativos</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">
                        {{ $fornecedores->where('ativo', 0)->count() }}
                    </p>
                </div>
            </div>

            <div class="flex justify-start" style="margin-bottom: -1rem;">
                <a href="{{ route('fornecedores.create') }}" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px; box-shadow: 0 2px 4px rgba(34, 197, 94, 0.3);" onmouseover="this.style.boxShadow='0 4px 6px rgba(34, 197, 94, 0.4)'" onmouseout="this.style.boxShadow='0 2px 4px rgba(34, 197, 94, 0.3)'">
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Adicionar
                </a>
            </div>

            {{-- ================= TABELA ================= --}}
            @if($fornecedores->count())
            <div class="bg-white rounded-lg overflow-hidden" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto tabela-fornecedores">
                        <thead style="background-color: rgba(63, 156, 174, 0.05); border-bottom: 1px solid #3f9cae;">
                            <tr>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">CNPJ/CPF</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Razão Social</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Nome Fantasia</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Cidade/UF</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Status</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Ações</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @foreach($fornecedores as $fornecedor)
                            <tr class="hover:bg-gray-50 transition">

                                {{-- CNPJ / CPF --}}
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap min-w-[160px]">
                                    {{ \App\Helpers\FormatHelper::cpfCnpj($fornecedor->cpf_cnpj) }}
                                </td>

                                {{-- Razão Social --}}
                                <td class="px-4 py-3 text-sm text-gray-900 min-w-[200px] max-w-[280px] truncate">
                                    {{ $fornecedor->razao_social }}
                                </td>

                                {{-- Nome Fantasia --}}
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $fornecedor->nome_fantasia ?? '—' }}
                                </td>

                                {{-- Cidade/UF --}}
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                    {{ $fornecedor->cidade }}/{{ $fornecedor->estado }}
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    <x-status-badge :ativo="$fornecedor->ativo" />
                                </td>

                                {{-- Ações --}}
                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    <div class="flex gap-1 items-center">

                                        @if(auth()->user()->tipo === 'admin' || auth()->user()->canPermissao('fornecedores', 'editar'))
                                        <a href="{{ route('fornecedores.edit', $fornecedor) }}" class="btn btn-sm btn-edit"
                                            style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;">
                                            <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>
                                        @endif

                                        @if(auth()->user()->tipo === 'admin' || auth()->user()->canPermissao('fornecedores', 'excluir'))
                                        <form action="{{ route('fornecedores.destroy', $fornecedor) }}" method="POST"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este fornecedor?')"
                                            style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-delete"
                                                style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;">
                                                <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                        @endif

                                    </div>
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ================= PAGINAÇÃO ================= --}}
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-top: 1rem;">
                <div class="pagination-info">
                    Mostrando <strong>{{ $fornecedores->count() }}</strong> de
                    <strong>{{ $fornecedores->total() }}</strong>
                    fornecedores
                </div>

                <div class="pagination-links">
                    {{-- Link Anterior --}}
                    @if($fornecedores->onFirstPage())
                    <span class="pagination-link disabled">← Anterior</span>
                    @else
                    <a href="{{ $fornecedores->previousPageUrl() }}" class="pagination-link">← Anterior</a>
                    @endif

                    {{-- Links de Página --}}
                    @foreach($fornecedores->getUrlRange(1, $fornecedores->lastPage()) as $page => $url)
                    @if($page == $fornecedores->currentPage())
                    <span class="pagination-link active">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                    @endif
                    @endforeach

                    {{-- Link Próximo --}}
                    @if($fornecedores->hasMorePages())
                    <a href="{{ $fornecedores->nextPageUrl() }}" class="pagination-link">Próximo →</a>
                    @else
                    <span class="pagination-link disabled">Próximo →</span>
                    @endif
                </div>
            </div>

            @else
            <div class="bg-white rounded-lg p-12 text-center" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <h3 class="text-lg font-medium text-gray-900">Nenhum fornecedor encontrado</h3>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>

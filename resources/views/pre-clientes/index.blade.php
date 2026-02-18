
<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <nav class="flex items-center gap-2 text-base font-semibold leading-tight rounded-full py-2">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-800 font-medium">Pré-Clientes</span>
        </nav>
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ================= FILTROS ================= --}}
            <form method="GET" class="bg-white shadow rounded-lg p-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
                    <div class="flex flex-col lg:col-span-6">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            🔍 Pesquisar Pré-Cliente
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="CPF/CNPJ, Razão Social ou Nome Fantasia" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex flex-col lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos</option>
                            <option value="convertido" @selected(request('status')=='convertido')>Convertido</option>
                            <option value="pendente" @selected(request('status')=='pendente')>Pendente</option>
                        </select>
                    </div>
                    <br>
                    <div class="flex gap-3 items-end flex-col lg:flex-row lg:col-span-3 justify-end">
                        <button type="submit" class="btn btn-primary">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                            Filtrar
                        </button>
                        <a href="{{ route('pre-clientes.create') }}" class="btn btn-success">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Pré-Cliente
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
            <div class="resumo-grid gap-4 mb-6" style="display: grid !important; grid-template-columns: repeat(1, minmax(0, 1fr));">
                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-blue-600 w-full max-w-none">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total de Pré-Clientes</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ $preClientes->count() }}
                    </p>
                </div>
                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-green-600 w-full max-w-none">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Convertidos</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $preClientes->where('convertido_em_cliente', true)->count() }}
                    </p>
                </div>
                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-yellow-500 w-full max-w-none">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Pendentes</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">
                        {{ $preClientes->where('convertido_em_cliente', false)->count() }}
                    </p>
                </div>
            </div>

            {{-- ================= TABELA ================= --}}
            @if($preClientes->count())
            <div class="bg-white shadow rounded-lg overflow-x-auto">
                <table class="w-full table-auto min-w-[1100px]">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase w-[60px]">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase w-[180px]">CPF/CNPJ</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase w-[220px]">Nome</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase w-[180px]">E-mail</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase w-[160px]">Telefone</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase w-[100px]">Origem</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase w-[100px]">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase w-[90px]">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($preClientes as $preCliente)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-semibold">
                                    {{ $preCliente->id }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 font-mono whitespace-nowrap">
                                {{ \App\Helpers\FormatHelper::cpfCnpj($preCliente->cpf_cnpj) }}
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 truncate">
                                {{ $preCliente->nome_fantasia ?? $preCliente->razao_social ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 truncate">
                                {{ $preCliente->email ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                {{ $preCliente->telefone ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $preCliente->origem === 'orcamento' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($preCliente->origem) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $preCliente->convertido_em_cliente ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $preCliente->convertido_em_cliente ? 'Convertido' : 'Pendente' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm whitespace-nowrap">
                                <div class="flex gap-1 items-center">
                                    <a href="{{ route('pre-clientes.edit', $preCliente) }}" class="btn btn-sm btn-edit" title="Editar">
                                        <svg fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('pre-clientes.destroy', $preCliente) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este pré-cliente?')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-delete" title="Excluir">
                                            <svg fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- PAGINAÇÃO --}}
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Mostrando <strong>{{ $preClientes->count() }}</strong> de <strong>{{ $preClientes->total() }}</strong> pré-clientes
                </div>
                <div class="pagination-links">
                    @if($preClientes->onFirstPage())
                    <span class="pagination-link disabled">← Anterior</span>
                    @else
                    <a href="{{ $preClientes->previousPageUrl() }}" class="pagination-link">← Anterior</a>
                    @endif
                    @foreach($preClientes->getUrlRange(1, $preClientes->lastPage()) as $page => $url)
                    @if($page == $preClientes->currentPage())
                    <span class="pagination-link active">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                    @endif
                    @endforeach
                    @if($preClientes->hasMorePages())
                    <a href="{{ $preClientes->nextPageUrl() }}" class="pagination-link">Próximo →</a>
                    @else
                    <span class="pagination-link disabled">Próximo →</span>
                    @endif
                </div>
            </div>
            @else
            <div class="bg-white shadow rounded-lg p-12 text-center">
                <h3 class="text-lg font-medium text-gray-900">Nenhum pré-cliente encontrado</h3>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
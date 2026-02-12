<x-app-layout>
    @push('styles')
    @vite('resources/css/financeiro/index.css')
    <style>
        @media print {
            .print\:hidden { display: none !important; }
        }
    </style>
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-100 rounded-lg text-emerald-600 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3v2a3 3 0 006 0v-2c0-1.657-1.343-3-3-3zm0 0V6a2 2 0 10-4 0" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 20h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Relatorio - Contas a Receber e a Pagar
                </h2>
            </div>

            <a href="{{ route('relatorios.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-emerald-600 transition-all shadow-sm group print:hidden"
                title="Voltar para Relatorios">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Voltar</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="section-card filters-card mb-6 print:hidden">
                <form method="GET" class="filter-form">
                    <div class="filter-grid mb-4">
                        <div class="filter-group">
                            <label class="filter-label">Empresa</label>
                            <select name="empresa_id" class="filter-select">
                                <option value="">Todas as Empresas</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                        {{ $empresa->nome_fantasia ?? $empresa->razao_social ?? $empresa->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Centro de Custo</label>
                            <select name="centro_custo_id" class="filter-select">
                                <option value="">Todos os Centros</option>
                                @foreach($centrosCusto as $centro)
                                    <option value="{{ $centro->id }}" {{ request('centro_custo_id') == $centro->id ? 'selected' : '' }}>
                                        {{ $centro->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Cliente</label>
                            <select name="cliente_id" class="filter-select">
                                <option value="">Todos os Clientes</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nome_fantasia ?? $cliente->razao_social ?? $cliente->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Fornecedor</label>
                            <select name="fornecedor_id" class="filter-select">
                                <option value="">Todos os Fornecedores</option>
                                @foreach($fornecedores as $fornecedor)
                                    <option value="{{ $fornecedor->id }}" {{ request('fornecedor_id') == $fornecedor->id ? 'selected' : '' }}>
                                        {{ $fornecedor->nome_fantasia ?? $fornecedor->razao_social ?? $fornecedor->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Inicio</label>
                            <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="filter-input" />
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Fim</label>
                            <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="filter-input" />
                        </div>
                    </div>

                    <div class="filter-actions justify-end">
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filtrar Relatorio
                        </button>
                    </div>
                </form>
            </div>

            <div class="section-card mb-6">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                    <div class="w-2 h-6 bg-emerald-500 rounded-full"></div>
                    <h3 class="font-bold text-gray-800 uppercase text-sm tracking-wider">Contas a Receber</h3>
                </div>

                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="text-left">Empresa</th>
                                <th class="text-left">Centro</th>
                                <th class="text-left">Cliente</th>
                                <th class="text-left">Vencimento</th>
                                <th class="text-right">Valor</th>
                                <th class="text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contasReceber as $conta)
                                <tr>
                                    <td class="text-left">
                                        {{ $conta->empresaRelacionada?->nome_fantasia ?? $conta->empresaRelacionada?->razao_social ?? '-' }}
                                    </td>
                                    <td class="text-left">
                                        {{ $conta->orcamento?->centroCusto?->nome ?? '-' }}
                                    </td>
                                    <td class="text-left">
                                        {{ $conta->cliente?->nome_fantasia ?? $conta->cliente?->razao_social ?? $conta->cliente?->nome ?? '-' }}
                                    </td>
                                    <td class="text-left">
                                        {{ $conta->data_vencimento?->format('d/m/Y') ?? '-' }}
                                    </td>
                                    <td class="text-right font-black text-emerald-700">
                                        R$ {{ number_format($conta->valor, 2, ',', '.') }}
                                    </td>
                                    <td class="text-left">
                                        {{ $conta->status ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-sm text-gray-500 py-6">
                                        Nenhuma conta a receber encontrada com os filtros selecionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-right text-xs font-black text-gray-500 uppercase tracking-widest">Total a Receber</td>
                                <td class="px-4 py-4 text-right text-base font-black text-emerald-700">
                                    R$ {{ number_format($totalReceber, 2, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="pagination-container p-4">
                    {{ $contasReceber->links() }}
                </div>
            </div>

            <div class="section-card">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                    <div class="w-2 h-6 bg-red-500 rounded-full"></div>
                    <h3 class="font-bold text-gray-800 uppercase text-sm tracking-wider">Contas a Pagar</h3>
                </div>

                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="text-left">Empresa</th>
                                <th class="text-left">Centro</th>
                                <th class="text-left">Fornecedor</th>
                                <th class="text-left">Vencimento</th>
                                <th class="text-right">Valor</th>
                                <th class="text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contasPagar as $conta)
                                <tr>
                                    <td class="text-left">
                                        {{ $conta->empresaRelacionada?->nome_fantasia ?? $conta->empresaRelacionada?->razao_social ?? '-' }}
                                    </td>
                                    <td class="text-left">
                                        {{ $conta->centroCusto?->nome ?? '-' }}
                                    </td>
                                    <td class="text-left">
                                        {{ $conta->fornecedor?->nome_fantasia ?? $conta->fornecedor?->razao_social ?? '-' }}
                                    </td>
                                    <td class="text-left">
                                        {{ $conta->data_vencimento?->format('d/m/Y') ?? '-' }}
                                    </td>
                                    <td class="text-right font-black text-red-600">
                                        R$ {{ number_format($conta->valor, 2, ',', '.') }}
                                    </td>
                                    <td class="text-left">
                                        {{ $conta->status ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-sm text-gray-500 py-6">
                                        Nenhuma conta a pagar encontrada com os filtros selecionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-right text-xs font-black text-gray-500 uppercase tracking-widest">Total a Pagar</td>
                                <td class="px-4 py-4 text-right text-base font-black text-red-600">
                                    R$ {{ number_format($totalPagar, 2, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-right text-xs font-black text-gray-500 uppercase tracking-widest">Resultado (Receber - Pagar)</td>
                                <td class="px-4 py-4 text-right text-base font-black {{ $resultado >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                                    R$ {{ number_format($resultado, 2, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="pagination-container p-4">
                    {{ $contasPagar->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

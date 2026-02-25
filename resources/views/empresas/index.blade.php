<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Empresas']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- TESTE: sem x-filter --}}
            {{-- ================= FILTROS ================= --}}
            <form method="GET" class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px;">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
                    <div class="flex flex-col lg:col-span-6">
                        <label class="text-sm font-medium text-gray-700 mb-2">Pesquisar Empresa</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Razão social ou nome fantasia" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae]">
                    </div>
                    <div class="flex flex-col lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae]">
                            <option value="">Todas</option>
                            <option value="ativo" @selected(request('status')=='ativo')>Ativa</option>
                            <option value="inativo" @selected(request('status')=='inativo')>Inativa</option>
                        </select>
                    </div>
                    <div class="flex items-end lg:col-span-3 gap-2">
                        <button type="submit" class="px-4 py-2 text-white rounded-full" style="background: #3f9cae;">Filtrar</button>
                        <a href="{{ route('empresas.index') }}" class="px-4 py-2 text-white rounded-full" style="background: #9ca3af;">Limpar</a>
                    </div>
                </div>
            </form>

            {{-- ================= RESUMO (KPIs) ================= --}}
            {{-- TESTE: sem x-kpi-card --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-6 rounded-lg border-l-4 border-blue-600" style="border: 1px solid #3f9cae; border-top: 1px solid #3f9cae;">
                    <p class="text-xs text-gray-600 uppercase">Total de Empresas</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totais['total'] }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg border-l-4 border-green-600" style="border: 1px solid #16a34a;">
                    <p class="text-xs text-gray-600 uppercase">Empresas Ativas</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $totais['ativos'] }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg border-l-4 border-red-600" style="border: 1px solid #dc2626;">
                    <p class="text-xs text-gray-600 uppercase">Empresas Inativas</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">{{ $totais['inativos'] }}</p>
                </div>
            </div>

            @if(auth()->user()->canPermissao('clientes', 'incluir'))
            <div class="flex justify-start">
                <a href="{{ route('empresas.create') }}" class="px-4 py-2 text-white rounded-full" style="background: #22c55e;">+ Adicionar</a>
            </div>
            @endif

            {{-- ================= TABELA ================= --}}
            {{-- TESTE: sem x-table --}}
            <div class="bg-white rounded-lg overflow-hidden" style="border: 1px solid #3f9cae; border-top-width: 4px;">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead style="background-color: rgba(63, 156, 174, 0.05); border-bottom: 1px solid #3f9cae;">
                            <tr>
                                <th class="px-4 py-3 text-left uppercase text-sm font-semibold">ID</th>
                                <th class="px-4 py-3 text-left uppercase text-sm font-semibold">Empresa</th>
                                <th class="px-4 py-3 text-left uppercase text-sm font-semibold">CNPJ</th>
                                <th class="px-4 py-3 text-left uppercase text-sm font-semibold">Status</th>
                                <th class="px-4 py-3 text-left uppercase text-sm font-semibold">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($empresas as $empresa)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">{{ $empresa->id }}</td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $empresa->razao_social }}
                                    @if($empresa->nome_fantasia)
                                    <div class="text-xs text-gray-500">{{ $empresa->nome_fantasia }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $empresa->cnpj }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $empresa->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $empresa->ativo ? 'Ativa' : 'Inativa' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('empresas.edit', $empresa) }}" class="text-blue-600 hover:text-blue-800 mr-2">Editar</a>
                                    <form action="{{ route('empresas.destroy', $empresa) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Excluir?')">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">Nenhuma empresa cadastrada</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TESTE: sem x-pagination --}}
            @if($empresas->hasPages())
            <div class="flex justify-between items-center p-4 bg-white rounded-lg">
                <div class="text-sm text-gray-500">
                    Mostrando {{ $empresas->count() }} de {{ $empresas->total() }} empresas
                </div>
                <div class="flex gap-2">
                    @if($empresas->onFirstPage())
                        <span class="px-3 py-1 text-gray-400">Anterior</span>
                    @else
                        <a href="{{ $empresas->previousPageUrl() }}" class="px-3 py-1 text-[#3f9cae]">Anterior</a>
                    @endif
                    @if($empresas->hasMorePages())
                        <a href="{{ $empresas->nextPageUrl() }}" class="px-3 py-1 text-[#3f9cae]">Próximo</a>
                    @else
                        <span class="px-3 py-1 text-gray-400">Próximo</span>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>

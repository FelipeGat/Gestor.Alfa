<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Empresas
        </h2>
    </x-slot>
    <br>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ================= FILTROS ================= --}}
            <form method="GET" class="bg-white shadow rounded-lg p-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">

                    <div class="flex flex-col lg:col-span-6">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Pesquisar Empresa
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Razão social ou nome fantasia" class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="flex flex-col lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todas</option>
                            <option value="ativo" @selected(request('status')=='ativo' )>Ativa</option>
                            <option value="inativo" @selected(request('status')=='inativo' )>Inativa</option>
                        </select>
                    </div>

                    <div class="flex items-end lg:col-span-2">
                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #3b82f6; border-radius: 9999px;">
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

            {{-- ================= RESUMO (GRID EXPLÍCITO) ================= --}}
            <style>
            @media (min-width: 1024px) {
                .resumo-empresas {
                    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                }
            }
            </style>

            <div class="resumo-empresas gap-4" style="
                display: grid !important;
                grid-template-columns: repeat(1, minmax(0, 1fr));
            ">

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-blue-600 w-full">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">
                        Total de Empresas
                    </p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ $empresas->count() }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-green-600 w-full">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">
                        Empresas Ativas
                    </p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $empresas->where('ativo', true)->count() }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-red-600 w-full">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">
                        Empresas Inativas
                    </p>
                    <p class="text-3xl font-bold text-red-600 mt-2">
                        {{ $empresas->where('ativo', false)->count() }}
                    </p>
                </div>

            </div>

            @if(auth()->user()->canPermissao('clientes', 'incluir'))
            <div class="flex justify-start" style="margin-bottom: -1rem;">
                <a href="{{ route('empresas.create') }}" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px;">
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
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Empresa</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">CNPJ</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Ações</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @forelse($empresas as $empresa)
                            <tr class="hover:bg-gray-50 transition">

                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    {{ $empresa->id }}
                                </td>

                                <td class="px-4 py-3 text-sm text-gray-900 min-w-[220px]">
                                    <div class="font-medium">
                                        {{ $empresa->razao_social }}
                                    </div>

                                    @if($empresa->nome_fantasia)
                                    <div class="text-xs text-gray-500">
                                        {{ $empresa->nome_fantasia }}
                                    </div>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-sm text-gray-600 font-mono">
                                    {{ $empresa->cnpj }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    <span
                                        class="inline-flex items-center px-3 rounded-full text-xs font-semibold
                                            {{ $empresa->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}"
                                        style="padding-top: 0.5rem; padding-bottom: 0.5rem; min-width: 90px; justify-content: center;">
                                        {{ $empresa->ativo ? '✓ Ativa' : '✗ Inativa' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="table-actions">
                                        <a href="{{ route('empresas.edit', $empresa) }}" class="btn btn-sm btn-edit"
                                            style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;">
                                            <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                <path
                                                    d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>

                                        <form action="{{ route('empresas.edit', $empresa) }}" method="POST"
                                            onsubmit="return confirm('Deseja excluir esta Empresa?')"
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
                                    </div>
                                </td>

                            </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                        Nenhuma empresa cadastrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
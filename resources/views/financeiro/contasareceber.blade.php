<x-app-layout>

    @push('styles')
    @vite('resources/css/financeiro/contasareceber.css')
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🧾 Contas a Receber
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ================= FILTROS ================= --}}
            <form method="GET" class="bg-white shadow rounded-lg p-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">

                    <div class="flex flex-col lg:col-span-6">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            🔍 Pesquisar Cobranças
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cliente ou descrição"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>

                    <div class="flex flex-col lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            📌 Status
                        </label>
                        <select name="status[]" multiple
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm h-32">
                            <option value="pendente">Pendente</option>
                            <option value="pago">Pago</option>
                            <option value="vencido">Vencido</option>
                        </select>
                    </div>

                    <div class="flex gap-3 lg:col-span-3 justify-end">
                        <button class="btn btn-primary">🔍 Filtrar</button>
                        <a href="{{ route('financeiro.contasareceber') }}"
                            class="btn btn-secondary">🧹 Limpar</a>
                    </div>
                </div>
            </form>

            {{-- ================= TABELA ================= --}}
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Categoria</th>
                                <th>Cliente</th>
                                <th>Telefone</th>
                                <th>Email</th>
                                <th>Valor</th>
                                <th>Descrição</th>
                                <th>Conta</th>
                                <th>Forma Pgto</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cobrancas as $cobranca)
                            <tr>
                                <td>{{ $cobranca->data_vencimento->format('d/m/Y') }}</td>

                                <td>
                                    @if($cobranca->orcamento)
                                    Orçamento {{ $cobranca->orcamento->numero_orcamento }}
                                    @else
                                    Contrato
                                    @endif
                                </td>

                                <td>{{ $cobranca->cliente->nome ?? '—'}}</td>

                                <td>@if($cobranca->cliente)
                                    {{ optional($cobranca->cliente->telefones->first())->valor ?? '-' }}
                                    @else
                                    {{ $pessoa->telefone ?? '-' }}
                                    @endif
                                </td>

                                <td>@if($cobranca->cliente)
                                    {{ optional($cobranca->cliente->emails->first())->valor ?? '-' }}
                                    @else
                                    {{ $pessoa->email ?? '-' }}
                                    @endif
                                </td>

                                <td class="text-right">
                                    R$ {{ number_format($cobranca->valor, 2, ',', '.') }}
                                </td>

                                <td>
                                    {{ $cobranca->orcamento->descricao ?? $cobranca->descricao }}
                                </td>

                                <td class="text-center">
                                    <span class="badge badge-warning">Pendente</span>
                                </td>

                                <td class="text-center">
                                    {{ $cobranca->orcamento->forma_pagamento ?? '—' }}
                                </td>

                                <td>
                                    <div class="table-actions">
                                        <form method="POST"
                                            action="{{ route('financeiro.cobrancas.destroy', $cobranca) }}"
                                            onsubmit="return confirm('Deseja excluir esta cobrança?')"
                                            style="display: flex; align-items: center; gap: 5px;">
                                            @csrf
                                            @method('DELETE')

                                            {{-- BOTÃO IMPRIMIR --}}
                                            <a href="#" target="_blank" style="text-decoration: none;">
                                                <button type="button" class="btn btn-sm btn-edit">🖨️ Imprimir</button>
                                            </a>

                                            {{-- BOTÃO EXCLUIR --}}
                                            {{-- Este mantém o type="submit" (padrão) para enviar o form --}}
                                            <button type="submit" class="btn btn-delete btn-sm">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
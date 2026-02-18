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
            <span class="text-gray-800 font-medium">Cobranças</span>
        </nav>
    </x-slot>

    <div class="flex justify-center min-h-screen bg-gray-100">
        <div class="w-3/4 py-12 space-y-6">

            {{-- FILTROS --}}
            <form method="GET" class="bg-white shadow rounded-lg p-6">
                <div class="flex flex-wrap gap-4 items-end">

                    <div class="flex flex-col flex-1 min-w-[150px]">
                        <label class="text-sm font-medium text-gray-700 mb-2">Cliente</label>
                        <select name="cliente_id" class="border rounded px-3 py-2 text-sm">
                            <option value="">Todos</option>
                            @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" @selected(request('cliente_id')==$cliente->id)>
                                {{ $cliente->nome }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col min-w-[120px]">
                        <label class="text-sm font-medium text-gray-700 mb-2">Mês</label>
                        <select name="mes" class="border rounded px-3 py-2 text-sm">
                            <option value="">Todos</option>
                            @for($m=1;$m<=12;$m++) <option value="{{ $m }}" @selected(request('mes')==$m)>
                                {{ str_pad($m,2,'0',STR_PAD_LEFT) }}
                                </option>
                                @endfor
                        </select>
                    </div>

                    <div class="flex flex-col min-w-[120px]">
                        <label class="text-sm font-medium text-gray-700 mb-2">Ano</label>
                        <input type="number" name="ano" value="{{ request('ano') }}"
                            class="border rounded px-3 py-2 text-sm" placeholder="YYYY">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                clip-rule="evenodd" />
                        </svg>
                        Filtrar
                    </button>
                </div>
            </form>

            {{-- TABELA AGRUPADA --}}
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">Cliente</th>
                            <th class="px-4 py-3 text-center">Qtd</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-center">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($cobrancas as $clienteNome => $lista)
                        @php
                        $rowId = md5($clienteNome);

                        $inadimplente = $lista->contains(fn($c) => $c->status === 'vencido');

                        $boletosNaoBaixados = $lista->filter(fn($c) =>
                        $c->boleto && is_null($c->boleto->baixado_em)
                        )->count();

                        $total = $lista->sum('valor');
                        @endphp

                        {{-- LINHA CLIENTE --}}
                        <tr class="bg-white {{ $inadimplente ? 'bg-red-50' : '' }}">
                            <td class="px-4 py-3 font-medium">
                                {{ $clienteNome }}

                                @if($inadimplente)
                                <span class="ml-2 px-2 py-0.5 text-xs rounded bg-red-100 text-red-700">
                                    Inadimplente
                                </span>
                                @endif

                                @if($boletosNaoBaixados > 0)
                                <span class="ml-2 px-2 py-0.5 text-xs rounded bg-yellow-100 text-yellow-800">
                                    {{ $boletosNaoBaixados }} não baixado{{ $boletosNaoBaixados > 1 ? 's' : '' }}
                                </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-center">
                                {{ $lista->count() }}
                            </td>

                            <td class="px-4 py-3 text-right font-semibold">
                                R$ {{ number_format($total,2,',','.') }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                <button onclick="toggleCliente('{{ $rowId }}', this)"
                                    class="inline-flex items-center justify-center px-4 py-2
                                        bg-blue-600 hover:bg-blue-700 text-green-600 text-xs font-medium rounded-md shadow">

                                    <span class="icon-plus">+</span>

                                    <span class="icon-minus hidden">−</span>
                                </button>
                            </td>
                        </tr>

                        {{-- COBRANÇAS DO CLIENTE --}}
                        <tr id="cliente-{{ $rowId }}" class="hidden bg-gray-50">
                            <td colspan="4" class="p-4 animate-expand">
                                <table class="w-full text-xs bg-white border">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Vencimento</th>
                                            <th class="px-3 py-2 text-right">Valor</th>
                                            <th class="px-3 py-2 text-center">Status</th>
                                            <th class="px-3 py-2 text-center">Ações</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($lista as $cobranca)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="px-3 py-2">
                                                {{ $cobranca->data_vencimento->format('d/m/Y') }}
                                            </td>

                                            <td class="px-3 py-2 text-right">
                                                R$ {{ number_format($cobranca->valor,2,',','.') }}
                                            </td>

                                            <td class="px-3 py-2 text-center">
                                                @if($cobranca->status === 'pago')
                                                <span class="text-green-700">Pago</span>
                                                @elseif($cobranca->status === 'pendente')
                                                <span class="text-yellow-700">Pendente</span>
                                                @else
                                                <span class="text-red-700">Vencido</span>
                                                @endif
                                            </td>

                                            <td class="px-3 py-2 text-center">
                                                @if($cobranca->status !== 'pago')
                                                <form method="POST" action="{{ route('cobrancas.pagar',$cobranca) }}"
                                                    class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="text-green-600 text-xs">
                                                        Pagar
                                                    </button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    {{-- JS --}}
    <script>
    function toggleCliente(id, btn) {
        const row = document.getElementById('cliente-' + id);
        const plus = btn.querySelector('.icon-plus');
        const minus = btn.querySelector('.icon-minus');

        row.classList.toggle('hidden');
        plus.classList.toggle('hidden');
        minus.classList.toggle('hidden');
    }
    </script>

    {{-- Animação --}}
    <style>
    @keyframes expand {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-expand {
        animation: expand 0.25s ease-out;
    }
    </style>

</x-app-layout>
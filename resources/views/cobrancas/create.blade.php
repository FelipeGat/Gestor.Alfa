<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nova Cobrança
        </h2>
    </x-slot>

    <div class="py-12">
        <form action="{{ route('cobrancas.store') }}" method="POST" class="bg-white p-6 rounded shadow">
            @csrf

            {{-- Cliente --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Cliente</label>
                <select name="cliente_id" class="w-full border rounded">
                    @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}">{{ $cliente->nome }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Valor --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Valor</label>
                <input type="number" step="0.01" name="valor" class="w-full border rounded">
            </div>

            {{-- Data de Vencimento --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Data de Vencimento</label>
                <input type="date" name="data_vencimento" class="w-full border rounded">
            </div>

            {{-- Descrição --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Descrição</label>
                <input type="text" name="descricao" class="w-full border rounded">
            </div>

            {{-- Botões --}}
            <div class="flex justify-end gap-2">
                <a href="{{ route('cobrancas.index') }}" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                    Voltar
                </a>

                <button type="submit" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                    Salvar
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
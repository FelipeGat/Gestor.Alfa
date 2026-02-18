<x-app-layout>
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
            <a href="{{ route('cobrancas.index') }}" class="text-gray-500 hover:text-gray-700 transition">Cobranças</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-800 font-medium">Nova</span>
        </nav>
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
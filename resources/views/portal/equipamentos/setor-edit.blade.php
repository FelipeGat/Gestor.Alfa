<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <div class="portal-wrapper">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">Editar Setor</h2>
                <p class="text-sm text-gray-600 mt-1">{{ $cliente->nome_exibicao }}</p>
            </div>
            <a href="{{ route('portal.equipamentos.setores') }}" class="inline-flex w-full sm:w-auto justify-center items-center gap-2 px-4 py-2 bg-[#3f9cae] hover:bg-[#2d7a8a] text-white text-sm font-semibold rounded-lg transition-all">
                Voltar para Setores
            </a>
        </div>

        <div class="portal-table-card p-6 max-w-2xl">
            <form action="{{ route('portal.equipamentos.setores.update', $setor) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Setor *</label>
                    <input type="text" name="nome" required value="{{ old('nome', $setor->nome) }}" class="w-full rounded border-gray-300">
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('portal.equipamentos.setores') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg">Cancelar</a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#3f9cae] hover:bg-[#2d7a8a] text-white text-sm font-semibold rounded-lg">
                        Salvar Setor
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

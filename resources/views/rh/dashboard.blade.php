<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'RH']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #3b82f6; border-top: 1px solid #3b82f6; border-right: 1px solid #3b82f6; border-bottom: 1px solid #3b82f6; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Funcionários Ativos</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $funcionariosAtivos }}</p>
                </div>

                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #f59e0b; border-top: 1px solid #f59e0b; border-right: 1px solid #f59e0b; border-bottom: 1px solid #f59e0b; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Documentos Vencendo</p>
                    <p class="text-3xl font-bold text-amber-600 mt-2">{{ $documentosVencendo }}</p>
                </div>

                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #8b5cf6; border-top: 1px solid #8b5cf6; border-right: 1px solid #8b5cf6; border-bottom: 1px solid #8b5cf6; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">EPIs Vencendo</p>
                    <p class="text-3xl font-bold text-violet-600 mt-2">{{ $episVencendo }}</p>
                </div>

                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #ef4444; border-top: 1px solid #ef4444; border-right: 1px solid #ef4444; border-bottom: 1px solid #ef4444; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Férias Vencidas</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">{{ $feriasVencidas }}</p>
                </div>

                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #22c55e; border-top: 1px solid #22c55e; border-right: 1px solid #22c55e; border-bottom: 1px solid #22c55e; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Banco de Horas</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ gmdate('H:i', max(0, $bancoHorasSegundos)) }}</p>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Ações RH</h2>
                <div class="flex flex-wrap gap-3">
                    <x-button href="{{ route('rh.funcionarios.index') }}" variant="primary" size="sm">Funcionários</x-button>
                    <x-button href="{{ route('rh.ponto-jornada.index') }}" variant="secondary" size="sm">Ponto & Jornada</x-button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

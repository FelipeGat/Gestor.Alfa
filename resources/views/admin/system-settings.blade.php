<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Configurações do Sistema') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if ($maintenanceMode === '1')
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="font-bold">ATENÇÃO: Sistema em Modo de Manutenção</p>
                        <p class="text-sm">Os usuários comuns não conseguem acessar o sistema enquanto esta opção estiver ativa.</p>
                    </div>
                </div>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="bg-orange-50 border-l-4 border-orange-500 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-orange-700">
                                    Configure o modo de manutenção do sistema. Quando ativado, apenas administradores terão acesso ao sistema.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.system-settings.maintenance') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-6">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="hidden" name="maintenance_mode" value="{{ $maintenanceMode === '1' ? '1' : '0' }}" id="maintenance_mode_input">
                                <input type="checkbox" 
                                       {{ $maintenanceMode === '1' ? 'checked' : '' }}
                                       class="sr-only peer"
                                       onchange="document.getElementById('maintenance_mode_input').value = this.checked ? '1' : '0'">
                                <div class="relative w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all {{ $maintenanceMode === '1' ? 'peer-checked:bg-red-600' : 'peer-checked:bg-green-600' }}" 
                                       onclick="document.getElementById('maintenance_mode_input').value = this.checked ? '1' : '0'">
                                </div>
                                <span class="ms-3 text-sm font-medium {{ $maintenanceMode === '1' ? 'text-red-600' : 'text-gray-900' }}">
                                    Modo de Manutenção {{ $maintenanceMode === '1' ? '(ATIVADO)' : '' }}
                                </span>
                            </label>
                        </div>

                        <div class="mb-6">
                            <label for="maintenance_message" class="block mb-2 text-sm font-medium text-gray-900">
                                Mensagem de Manutenção
                            </label>
                            <textarea id="maintenance_message" 
                                      name="maintenance_message" 
                                      rows="3"
                                      class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                      placeholder="O sistema está passando por uma atualização programada...">{{ old('maintenance_message', $maintenanceMessage) }}</textarea>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit" 
                                    class="text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 focus:ring-4 focus:ring-orange-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 focus:outline-none">
                                Salvar Configurações
                            </button>
                            
                            @if ($maintenanceMode === '1')
                                <a href="{{ route('admin.system-settings') }}" 
                                   class="text-gray-900 bg-white border border-gray-300 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 hover:bg-gray-50">
                                    Atualizar Página
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

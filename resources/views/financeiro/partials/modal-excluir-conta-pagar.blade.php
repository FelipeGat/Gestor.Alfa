{{-- MODAL: EXCLUIR CONTA A PAGAR --}}
<div x-data="{
    open: false,
    contaId: null,
    tipo: 'avulsa',
    contaFixaId: null,
    deleteFuture: 'only'
}" @excluir-conta-pagar.window="
    open = true;
    contaId = $event.detail.contaId;
    tipo = $event.detail.tipo || 'avulsa';
    contaFixaId = $event.detail.contaFixaId || null;
    deleteFuture = 'only';
" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="open = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div x-show="open" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

            <form :action="'/financeiro/contas-a-pagar/' + contaId + (tipo === 'fixa' && deleteFuture === 'all' ? '?delete_future=all' : '')" method="POST">
                @csrf
                @method('DELETE')

                <div class="bg-red-50 px-6 py-4 border-b border-red-200">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h3 class="ml-3 text-lg font-bold text-gray-900">Confirmar Exclusão</h3>
                    </div>
                </div>

                <div class="bg-white px-6 py-5">
                    <p class="text-sm text-gray-600" x-show="tipo === 'avulsa'">
                        Tem certeza que deseja excluir esta conta a pagar?
                    </p>

                    <div x-show="tipo === 'fixa'" class="space-y-3">
                        <p class="text-sm text-gray-600 font-semibold">
                            Esta é uma despesa fixa recorrente. O que deseja excluir?
                        </p>

                        <div class="space-y-2">
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50" :class="deleteFuture === 'only' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'">
                                <input type="radio" x-model="deleteFuture" value="only" class="mr-3">
                                <span class="text-sm">
                                    <strong>Apenas esta parcela</strong><br>
                                    <span class="text-gray-500">As próximas parcelas serão mantidas</span>
                                </span>
                            </label>

                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50" :class="deleteFuture === 'all' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'">
                                <input type="radio" x-model="deleteFuture" value="all" class="mr-3">
                                <span class="text-sm">
                                    <strong>Esta e todas as próximas</strong><br>
                                    <span class="text-gray-500">Parcelas já pagas não serão afetadas</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <p class="text-sm text-red-600 font-semibold mt-3">
                        ⚠️ Esta ação não pode ser desfeita!
                    </p>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                    <button type="button" @click="open = false"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition">
                        Sim, Excluir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
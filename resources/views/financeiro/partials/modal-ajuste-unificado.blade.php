<!-- Modal Ajuste Unificado Modernizado -->
<div id="modalAjusteUnificado" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50 backdrop-blur-sm transition-opacity duration-300">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden" style="border: 1px solid #3f9cae; border-top-width: 4px;">
        
        {{-- Cabeçalho do Modal --}}
        <div class="px-6 py-4 flex items-center justify-between" style="background-color: rgba(63, 156, 174, 0.05); border-bottom: 1px solid #3f9cae;">
            <div class="flex items-center gap-3">
                <h3 class="text-lg font-bold text-gray-800">Ajustar Saldo - <span id="ajusteUnificadoContaNome" style="color: rgb(17, 24, 39);"></span></h3>
            </div>
            <button type="button" onclick="fecharModal('modalAjusteUnificado')" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="formAjusteUnificado" method="POST" action="{{ route('financeiro.contas-financeiras.ajuste-manual') }}" class="p-6">
            @csrf
            <input type="hidden" name="conta_id" id="ajusteUnificadoContaId">
            
            <div class="space-y-5">
                {{-- Data do Lançamento --}}
                <div class="group">
                    <label class="filter-label">Data do Lançamento <span class="text-red-500">*</span></label>
                    <input type="date" name="data" required class="filter-select w-full">
                </div>

                {{-- Saldo Final --}}
                <div class="group">
                    <label class="filter-label">Saldo Final da Conta <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold text-sm" style="z-index: 10;">R$</span>
                        <input type="text" name="valor" step="0.01" required placeholder="0,00" 
                            class="filter-select w-full" 
                            style="padding-left: 3rem; font-size: 1rem;">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Informe o valor que a conta deve ter. O sistema ajustará automaticamente.</p>
                </div>

                {{-- Observação --}}
                <div class="group">
                    <label class="filter-label">Observação / Motivo</label>
                    <textarea name="observacao" rows="2" placeholder="Ex: Correção de saldo, ajuste de tarifas..." class="filter-select w-full"></textarea>
                </div>
            </div>

            {{-- Rodapé do Modal --}}
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="fecharModal('modalAjusteUnificado')" 
                    class="btn btn-cancelar inline-flex items-center justify-center px-6 py-2" 
                    style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; background: #ef4444; color: white; border: none; border-radius: 9999px; min-width: 130px; justify-content: center; width: 130px; box-shadow: none;" 
                    onmouseover="this.style.boxShadow='0 4px 6px rgba(239, 68, 68, 0.4)'" 
                    onmouseout="this.style.boxShadow='none'">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Cancelar
                </button>
                <button type="submit" 
                    class="btn btn-primary inline-flex items-center justify-center px-6 py-2" 
                    style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; background: #3f9cae; border-radius: 9999px; box-shadow: 0 2px 4px rgba(63, 156, 174, 0.3); transition: all 0.2s; min-width: 130px; width: 130px;" 
                    onmouseover="this.style.background='#358a96'; this.style.boxShadow='0 4px 6px rgba(63, 156, 174, 0.4)'" 
                    onmouseout="this.style.background='#3f9cae'; this.style.boxShadow='0 2px 4px rgba(63, 156, 174, 0.3)'">
                    <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Confirmar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalAjusteUnificado(id, nome) {
    document.getElementById('ajusteUnificadoContaId').value = id;
    document.getElementById('ajusteUnificadoContaNome').innerText = nome;
    document.getElementById('modalAjusteUnificado').classList.remove('hidden');
}
function fecharModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}
</script>

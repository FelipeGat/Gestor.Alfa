<!-- Modal Ajuste Unificado Modernizado -->
<div id="modalAjusteUnificado" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50 backdrop-blur-sm transition-opacity duration-300">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden" style="border: 1px solid #3f9cae; border-top-width: 4px;">
        
        {{-- Cabeçalho do Modal --}}
        <div class="px-6 py-4 flex items-center justify-between" style="background-color: rgba(63, 156, 174, 0.05); border-bottom: 1px solid #3f9cae;">
            <div class="flex items-center gap-3">
                <h3 class="text-lg font-bold text-gray-800">Ajuste Manual - <span id="ajusteUnificadoContaNome" style="color: rgb(17, 24, 39);"></span></h3>
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

                {{-- Tipo de Operação --}}
                <div class="group">
                    <label class="filter-label">O que deseja fazer?</label>
                    <select name="tipo_operacao" id="tipoOperacao" required onchange="atualizarCamposAjuste()" class="filter-select w-full">
                        <option value="ajuste">Ajustar Saldo (Entrada/Saída)</option>
                        <option value="transferencia">Transferir para outra Conta</option>
                        <option value="injecao">Injeção de Receita (Aporte)</option>
                    </select>
                </div>

                {{-- Campos Dinâmicos: Ajuste Manual --}}
                <div id="camposAjusteManual" class="p-4 rounded-xl border space-y-3 transition-all" style="background-color: rgba(63, 156, 174, 0.05); border-color: #3f9cae;">
                    <label class="filter-label" style="color: #3f9cae;">Tipo de Ajuste</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex items-center justify-center px-4 py-2 bg-white rounded-full cursor-pointer transition-all" style="border: 1px solid #d1d5db;" onmouseover="this.style.borderColor='#3f9cae'; this.style.boxShadow='0 2px 4px rgba(63, 156, 174, 0.3)'" onmouseout="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">
                            <input type="radio" name="tipo_ajuste" value="ajuste_entrada" checked class="sr-only peer">
                            <div class="peer-checked:text-emerald-600 peer-checked:font-semibold text-gray-600 text-sm flex items-center gap-2" style="font-weight: 600;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                                Entrada
                            </div>
                        </label>
                        <label class="relative flex items-center justify-center px-4 py-2 bg-white rounded-full cursor-pointer transition-all" style="border: 1px solid #d1d5db;" onmouseover="this.style.borderColor='#3f9cae'; this.style.boxShadow='0 2px 4px rgba(63, 156, 174, 0.3)'" onmouseout="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">
                            <input type="radio" name="tipo_ajuste" value="ajuste_saida" class="sr-only peer">
                            <div class="peer-checked:text-red-600 peer-checked:font-semibold text-red-600 text-sm flex items-center gap-2" style="font-weight: 600;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                                Saída
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Campos Dinâmicos: Transferência --}}
                <div id="camposTransferencia" style="display:none;" class="p-4 bg-amber-50 rounded-xl border border-amber-100 space-y-3 transition-all">
                    <label class="filter-label" style="color: #b45309;">Conta de Destino</label>
                    <select name="conta_destino_id" id="contaDestinoSelect" class="filter-select w-full">
                        <option value="">Selecione a conta destino...</option>
                        @foreach($contas as $conta)
                            <option value="{{ $conta->id }}">{{ $conta->nome }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Valor --}}
                <div class="group">
                    <label class="filter-label">Valor da Operação <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold text-sm" style="z-index: 10;">R$</span>
                        <input type="text" name="valor" step="0.01" min="0.01" required placeholder="0,00" 
                            class="filter-select w-full" 
                            style="padding-left: 3rem; font-size: 1rem;">
                    </div>
                </div>

                {{-- Observação --}}
                <div class="group">
                    <label class="filter-label">Observação / Motivo</label>
                    <textarea name="observacao" rows="2" placeholder="Ex: Ajuste de tarifa bancária ou correção de saldo..." class="filter-select w-full"></textarea>
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
function atualizarCamposAjuste() {
    const operacao = document.getElementById('tipoOperacao').value;
    const divAjuste = document.getElementById('camposAjusteManual');
    const divTransf = document.getElementById('camposTransferencia');
    
    divAjuste.style.display = 'none';
    divTransf.style.display = 'none';
    
    if (operacao === 'ajuste') {
        divAjuste.style.display = 'block';
    } else if (operacao === 'transferencia') {
        divTransf.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    atualizarCamposAjuste();
});
</script>
<script>
function abrirModalAjusteUnificado(id, nome) {
    document.getElementById('ajusteUnificadoContaId').value = id;
    document.getElementById('ajusteUnificadoContaNome').innerText = nome;
    document.getElementById('tipoOperacao').value = 'ajuste';
    atualizarCamposAjuste();
    document.getElementById('modalAjusteUnificado').classList.remove('hidden');
}
function fecharModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}
</script>

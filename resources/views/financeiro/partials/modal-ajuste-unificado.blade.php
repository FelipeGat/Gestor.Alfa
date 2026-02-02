<!-- Modal Ajuste Unificado Modernizado -->
<div id="modalAjusteUnificado" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50 backdrop-blur-sm transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden transform transition-all scale-95 duration-300">
        
        {{-- Cabeçalho do Modal --}}
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Ajuste Manual - <span id="ajusteUnificadoContaNome" class="text-indigo-600"></span></h3>
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
                    <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-1">Data do Lançamento <span class="text-red-500">*</span></label>
                    <input type="date" name="data" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm font-medium">
                    <p class="mt-1 text-[10px] text-gray-400 italic">Informe a data em que a movimentação ocorreu efetivamente.</p>
                </div>

                {{-- Tipo de Operação --}}
                <div class="group">
                    <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-1">O que deseja fazer?</label>
                    <select name="tipo_operacao" id="tipoOperacao" required onchange="atualizarCamposAjuste()" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm font-bold text-gray-700">
                        <option value="ajuste">Ajustar Saldo (Entrada/Saída)</option>
                        <option value="transferencia">Transferir para outra Conta</option>
                        <option value="injecao">Injeção de Receita (Aporte)</option>
                    </select>
                    <p class="mt-1 text-[10px] text-gray-400 italic">Escolha o tipo de movimentação financeira que será realizada.</p>
                </div>

                {{-- Campos Dinâmicos: Ajuste Manual --}}
                <div id="camposAjusteManual" class="p-4 bg-indigo-50 rounded-2xl border border-indigo-100 space-y-3 transition-all">
                    <label class="block text-xs font-black text-indigo-600 uppercase tracking-widest">Tipo de Ajuste</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex items-center justify-center p-3 border-2 border-white bg-white rounded-xl cursor-pointer hover:border-indigo-200 transition-all">
                            <input type="radio" name="tipo_ajuste" value="ajuste_entrada" checked class="sr-only peer">
                            <div class="peer-checked:text-emerald-600 peer-checked:font-black text-gray-400 text-sm flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                                Entrada
                            </div>
                        </label>
                        <label class="relative flex items-center justify-center p-3 border-2 border-white bg-white rounded-xl cursor-pointer hover:border-indigo-200 transition-all">
                            <input type="radio" name="tipo_ajuste" value="ajuste_saida" class="sr-only peer">
                            <div class="peer-checked:text-red-600 peer-checked:font-black text-gray-400 text-sm flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                                Saída
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Campos Dinâmicos: Transferência --}}
                <div id="camposTransferencia" style="display:none;" class="p-4 bg-amber-50 rounded-2xl border border-amber-100 space-y-3 transition-all">
                    <label class="block text-xs font-black text-amber-600 uppercase tracking-widest">Conta de Destino</label>
                    <select name="conta_destino_id" id="contaDestinoSelect" class="w-full px-4 py-2.5 bg-white border border-amber-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all text-sm font-bold">
                        <option value="">Selecione a conta destino...</option>
                        @foreach($contas as $conta)
                            <option value="{{ $conta->id }}">{{ $conta->nome }}</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-amber-600 italic font-medium">O valor sairá desta conta e entrará na conta selecionada acima.</p>
                </div>

                {{-- Valor --}}
                <div class="group">
                    <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-1">Valor da Operação <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm">R$</span>
                        <input type="number" name="valor" step="0.01" min="0.01" required placeholder="0,00" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-lg font-black text-gray-800">
                    </div>
                    <p class="mt-1 text-[10px] text-gray-400 italic">Digite o valor positivo. O sistema cuidará do sinal conforme a operação.</p>
                </div>

                {{-- Observação --}}
                <div class="group">
                    <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-1">Observação / Motivo</label>
                    <textarea name="observacao" rows="2" placeholder="Ex: Ajuste de tarifa bancária ou correção de saldo..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm font-medium"></textarea>
                </div>
            </div>

            {{-- Rodapé do Modal --}}
            <div class="mt-8 flex items-center gap-3">
                <button type="button" onclick="fecharModal('modalAjusteUnificado')" class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold rounded-xl transition-all active:scale-95">
                    Cancelar
                </button>
                <button type="submit" class="flex-[2] px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 transition-all transform active:scale-95 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Confirmar Operação
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
    
    // Reset views
    divAjuste.style.display = 'none';
    divTransf.style.display = 'none';
    
    if (operacao === 'ajuste') {
        divAjuste.style.display = 'block';
    } else if (operacao === 'transferencia') {
        divTransf.style.display = 'block';
    }
}

// Chamar uma vez no carregamento para garantir o estado inicial correto
document.addEventListener('DOMContentLoaded', function() {
    atualizarCamposAjuste();
});
</script>
<script>
// Função global para abrir o modal e preencher os dados
function abrirModalAjusteUnificado(id, nome) {
    document.getElementById('ajusteUnificadoContaId').value = id;
    document.getElementById('ajusteUnificadoContaNome').innerText = nome;
    document.getElementById('tipoOperacao').value = 'ajuste';
    atualizarCamposAjuste();
    document.getElementById('modalAjusteUnificado').classList.remove('hidden');
}
// Função global para fechar o modal
function fecharModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}
</script>
</script>

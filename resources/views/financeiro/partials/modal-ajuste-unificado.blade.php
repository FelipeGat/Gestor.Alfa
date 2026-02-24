<!-- Modal Ajuste Unificado -->
<x-modal name="modal-ajuste-unificado" maxWidth="lg" title="Ajustar Saldo">
    <form id="formAjusteUnificado" method="POST" action="{{ route('financeiro.contas-financeiras.ajuste-manual') }}">
        @csrf
        <input type="hidden" name="conta_id" id="ajusteUnificadoContaId">
        <input type="hidden" name="saldo_atual" id="ajusteUnificadoSaldoAtual">
        
        <div class="space-y-5">
            {{-- Saldo Atual (apenas visualização) --}}
            <div class="p-4 rounded-xl border" style="background-color: #f0f9ff; border-color: #3f9cae;">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium" style="color: #3f9cae;">Saldo Atual</span>
                    <span id="saldoAtualDisplay" class="text-lg font-bold text-gray-900">R$ 0,00</span>
                </div>
            </div>

            {{-- Data do Lançamento --}}
            <x-form-input 
                name="data" 
                label="Data do Lançamento" 
                type="date" 
                required 
            />

            {{-- Saldo Final --}}
            <div>
                <label class="text-sm font-medium text-gray-700 mb-1">
                    Saldo Final da Conta <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold text-sm z-10">R$</span>
                    <input 
                        type="text" 
                        name="valor" 
                        id="valorAjuste" 
                        step="0.01" 
                        required 
                        placeholder="0,00" 
                        class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm pl-10 focus:border-[#3f9cae] focus:ring-[#3f9cae]/20"
                        oninput="calcularResumoAjuste()"
                    >
                </div>
                <p class="text-xs text-gray-500 mt-1">Informe o valor que a conta deve ter. O sistema ajustará automaticamente.</p>
            </div>

            {{-- Resumo do Ajuste --}}
            <div id="resumoAjuste" class="p-4 rounded-xl border hidden" style="background-color: rgba(63, 156, 174, 0.1); border-color: #3f9cae;">
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">Novo Saldo</span>
                        <span id="novoSaldoDisplay" class="text-lg font-bold text-gray-900">R$ 0,00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">Diferença</span>
                        <span id="diferencaDisplay" class="text-sm font-semibold">R$ 0,00</span>
                    </div>
                </div>
            </div>

            {{-- Observação --}}
            <x-form-textarea 
                name="observacao" 
                label="Observação / Motivo" 
                rows="2" 
                placeholder="Ex: Correção de saldo, ajuste de tarifas..."
            />
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <x-button variant="danger" size="sm" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'modal-ajuste-unificado' }))">
                Cancelar
            </x-button>
            <x-button variant="primary" size="sm" type="submit" form="formAjusteUnificado">
                Confirmar
            </x-button>
        </div>
    </form>
</x-modal>

<script>
function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valor);
}

function parseMoeda(valor) {
    if (!valor) return 0;
    return parseFloat(valor.replace(/\./g, '').replace(',', '.')) || 0;
}

function calcularResumoAjuste() {
    const saldoAtual = parseFloat(document.getElementById('ajusteUnificadoSaldoAtual').value) || 0;
    const valorInput = document.getElementById('valorAjuste').value;
    const valorInformado = parseMoeda(valorInput);
    const resumoDiv = document.getElementById('resumoAjuste');
    const novoSaldoDisplay = document.getElementById('novoSaldoDisplay');
    const diferencaDisplay = document.getElementById('diferencaDisplay');

    if (!valorInput || valorInformado === 0) {
        resumoDiv.classList.add('hidden');
        return;
    }

    const diferenca = valorInformado - saldoAtual;
    const isEntrada = diferenca > 0;

    resumoDiv.classList.remove('hidden');
    novoSaldoDisplay.innerText = formatarMoeda(valorInformado);
    
    diferencaDisplay.innerText = (isEntrada ? '+' : '') + formatarMoeda(diferenca);
    diferencaDisplay.style.color = isEntrada ? '#059669' : '#dc2626';
}

function abrirModalAjusteUnificado(id, nome, saldo) {
    document.getElementById('ajusteUnificadoContaId').value = id;
    document.getElementById('ajusteUnificadoSaldoAtual').value = saldo;
    document.getElementById('saldoAtualDisplay').innerText = formatarMoeda(parseFloat(saldo));
    document.getElementById('valorAjuste').value = '';
    document.getElementById('resumoAjuste').classList.add('hidden');
    
    // Atualiza o título do modal com o nome da conta
    const titleEl = document.querySelector('#modalAjusteUnificado .text-lg.font-semibold');
    if (titleEl) {
        titleEl.innerHTML = 'Ajustar Saldo - <span class="text-gray-900">' + nome + '</span>';
    }
    
    // Abre o modal usando Alpine.js
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'modal-ajuste-unificado' }));
}
</script>

@push('styles')
@vite('resources/css/financeiro/modalcobranca.css')
@endpush

<div
    x-data="gerarCobranca()"
    x-show="$store.modalCobranca.open"
    style="display: none;"
    x-cloak
    class="modal-cobranca-overlay"
    @keydown.escape.window="$store.modalCobranca.fechar()">
    <div
        class="modal-cobranca"
        @click.away="$store.modalCobranca.fechar()">
        {{-- ================= HEADER ================= --}}
        <div class="modal-cobranca-header">
            <div class="modal-cobranca-title">
                💰 Gerar Cobrança —
                Orçamento
                <span x-text="$store.modalCobranca.orcamento?.numero_orcamento"></span>
            </div>

            <button
                type="button"
                class="modal-cobranca-close"
                @click="$store.modalCobranca.fechar()">
                ✕
            </button>
        </div>

        {{-- ================= FORM ================= --}}
        <form
            method="POST"
            :action="`{{ url('/financeiro/orcamentos') }}/${$store.modalCobranca.orcamento?.id}/gerar-cobranca`">
            @csrf

            {{-- CAMPOS FIXOS --}}
            <input
                type="hidden"
                name="valor"
                :value="$store.modalCobranca.orcamento?.valor_total">

            <input
                type="hidden"
                name="descricao"
                :value="`Cobrança do orçamento ${$store.modalCobranca.orcamento?.numero_orcamento}`">

            {{-- ================= BODY ================= --}}
            <div class="modal-cobranca-body">

                <div class="modal-grid">
                    {{-- CLIENTE --}}
                    <div class="modal-field">
                        <label class="modal-label">Cliente</label>
                        <input
                            type="text"
                            class="modal-input"
                            :value="$store.modalCobranca.orcamento?.cliente?.nome_fantasia || 'N/A'"
                            disabled>
                    </div>

                    {{-- ORÇAMENTO --}}
                    <div class="modal-field">
                        <label class="modal-label">Orçamento</label>
                        <input
                            type="text"
                            class="modal-input"
                            :value="$store.modalCobranca.orcamento?.numero_orcamento"
                            disabled>
                    </div>
                </div>

                {{-- FORMA DE PAGAMENTO --}}
                <div class="modal-field" style="margin-top:16px">
                    <label class="modal-label">Forma de Pagamento</label>
                    <select
                        name="forma_pagamento"
                        x-model="forma"
                        @change="atualizarForma()"
                        class="modal-select"
                        required>
                        <option value="">Selecione</option>
                        <option value="pix">Pix</option>
                        <option value="debito">Cartão de Débito</option>
                        <option value="credito">Cartão de Crédito</option>
                        <option value="boleto">Boleto</option>
                        <option value="faturado">Faturado</option>
                    </select>
                </div>

                {{-- PARCELAS --}}
                <template x-if="mostrarParcelas">
                    <div class="modal-parcelas" style="margin-top:16px">
                        <label class="modal-label">Quantidade de Parcelas</label>
                        <input
                            type="number"
                            name="parcelas"
                            min="1"
                            max="12"
                            x-model.number="parcelas"
                            @input="gerarVencimentos()"
                            class="modal-input"
                            style="max-width:120px">
                    </div>
                </template>

                {{-- VENCIMENTOS E VALORES --}}
                <template x-if="vencimentos.length > 0">
                    <div class="modal-vencimentos" style="margin-top:16px">
                        <div style="margin-bottom: 12px; padding: 8px; background: #f0f9ff; border-radius: 4px; border-left: 3px solid #3b82f6;">
                            <strong>Valor Total: R$ <span x-text="formatarMoeda($store.modalCobranca.orcamento?.valor_total)"></span></strong>
                            <span style="margin-left: 16px; color: #666;">Distribuído: R$ <span x-text="formatarMoeda(getValorTotal())"></span></span>
                        </div>

                        <div class="modal-vencimentos-grid" style="display: grid; grid-template-columns: 1fr; gap: 12px;">
                            <template x-for="(v, index) in vencimentos" :key="index">
                                <div style="display: grid; grid-template-columns: 2fr 2fr; gap: 10px; padding: 12px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                                    <div class="modal-field" style="margin: 0;">
                                        <label class="modal-label">
                                            📅 Parcela <span x-text="index + 1"></span> - Vencimento
                                        </label>
                                        <input
                                            type="date"
                                            :name="`vencimentos[${index}]`"
                                            x-model="vencimentos[index]"
                                            class="modal-input"
                                            required>
                                    </div>
                                    <div class="modal-field" style="margin: 0;">
                                        <label class="modal-label">
                                            💵 Valor (R$)
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            :name="`valores_parcelas[${index}]`"
                                            x-model="valoresParcelas[index]"
                                            @input="ajustarValores(index)"
                                            class="modal-input"
                                            required
                                            style="font-weight: 600; color: #059669;">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

            </div>

            {{-- ================= FOOTER ================= --}}
            <div class="modal-cobranca-footer">
                <button
                    type="button"
                    class="modal-btn modal-btn-cancel"
                    @click="$store.modalCobranca.fechar()">
                    Cancelar
                </button>

                <button type="submit" class="modal-btn modal-btn-success">
                    Salvar Cobrança
                </button>
            </div>

        </form>
    </div>
</div>
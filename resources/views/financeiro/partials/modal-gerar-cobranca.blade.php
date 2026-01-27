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
                            min="1"
                            max="12"
                            x-model.number="parcelas"
                            @input="gerarVencimentos()"
                            class="modal-input"
                            style="max-width:120px">
                    </div>
                </template>

                {{-- VENCIMENTOS --}}
                <template x-if="vencimentos.length > 0">
                    <div class="modal-vencimentos" style="margin-top:16px">
                        <div class="modal-vencimentos-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <template x-for="(v, index) in vencimentos" :key="index">
                                <div class="modal-field">
                                    <label class="modal-label">
                                        Parcela <span x-text="index + 1"></span>
                                    </label>
                                    <input
                                        type="date"
                                        :name="`vencimentos[${index}]`"
                                        x-model="vencimentos[index]"
                                        class="modal-input"
                                        required>
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
                    Gerar Cobrança
                </button>
            </div>

        </form>
    </div>
</div>
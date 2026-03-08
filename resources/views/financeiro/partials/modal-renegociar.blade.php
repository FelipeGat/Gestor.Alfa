<div
    x-data="{
        open: false,
        cobrancaId: null,
        clienteNome: '',
        valorOriginal: 0,
        descricao: '',
        dataVencimento: '',
        parcelaNum: 1,
        parcelasTotal: 1,
        numParcelas: 2,
        parcelas: [],
        get actionUrl() {
            return '/financeiro/contas-a-receber/' + this.cobrancaId + '/renegociar';
        },
        get totalNovo() {
            return this.parcelas.reduce(function(sum, p) { return sum + parseFloat(p.valor || 0); }, 0);
        },
        get diffCentavos() {
            return Math.round((this.totalNovo - this.valorOriginal) * 100);
        },
        get valido() {
            return this.diffCentavos === 0 && this.parcelas.every(function(p) { return p.valor > 0 && p.data_vencimento; });
        },
        distribuirIgual() {
            const n = this.parcelas.length;
            if (n === 0) return;
            const base = Math.floor(this.valorOriginal / n * 100) / 100;
            const resto = Math.round((this.valorOriginal - base * n) * 100) / 100;
            this.parcelas = this.parcelas.map(function(p, i) {
                return { valor: (i === n - 1 ? base + resto : base).toFixed(2), data_vencimento: p.data_vencimento };
            });
        },
        gerarDatas() {
            const self = this;
            this.parcelas = this.parcelas.map(function(p, i) {
                const base = new Date(self.dataVencimento + 'T12:00:00');
                base.setMonth(base.getMonth() + i);
                const yyyy = base.getFullYear();
                const mm = String(base.getMonth() + 1).padStart(2, '0');
                const dd = String(base.getDate()).padStart(2, '0');
                return { valor: p.valor, data_vencimento: yyyy + '-' + mm + '-' + dd };
            });
        },
        atualizarParcelas() {
            const n = parseInt(this.numParcelas) || 2;
            while (this.parcelas.length < n) {
                this.parcelas.push({ valor: '', data_vencimento: '' });
            }
            this.parcelas = this.parcelas.slice(0, n);
            this.distribuirIgual();
            this.gerarDatas();
        },
        formatarMoeda(v) {
            return parseFloat(v || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        formatarData(d) {
            if (!d) return '';
            const parts = d.split('-');
            return parts[2] + '/' + parts[1] + '/' + parts[0];
        }
    }"
    x-on:renegociar-cobranca.window="
        open = true;
        cobrancaId = $event.detail.cobrancaId;
        clienteNome = $event.detail.clienteNome;
        valorOriginal = $event.detail.valor;
        descricao = $event.detail.descricao;
        dataVencimento = $event.detail.dataVencimento;
        parcelaNum = $event.detail.parcelaNum;
        parcelasTotal = $event.detail.parcelasTotal;
        numParcelas = 2;
        parcelas = [{ valor: '', data_vencimento: '' }, { valor: '', data_vencimento: '' }];
        distribuirIgual();
        gerarDatas();
    ">

    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex min-h-screen items-center justify-center p-4">

            {{-- Overlay --}}
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="open = false"></div>

            {{-- Painel --}}
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-lg z-10"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                {{-- Cabeçalho --}}
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 1l4 4-4 4M3 11V9a4 4 0 014-4h14M7 23l-4-4 4-4M21 13v2a4 4 0 01-4 4H3"/>
                            </svg>
                            Renegociar Parcela
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5"
                           x-text="clienteNome + ' · R$ ' + formatarMoeda(valorOriginal)"></p>
                    </div>
                    <button type="button" @click="open = false"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form :action="actionUrl" method="POST">
                    @csrf

                    {{-- Info parcela original --}}
                    <div class="px-6 py-3 bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-700">
                        <div class="flex items-start gap-2 text-sm text-amber-700 dark:text-amber-400">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <span class="font-medium">Parcela original: </span>
                                <span x-text="'Parc. ' + parcelaNum + '/' + parcelasTotal + ' · R$ ' + formatarMoeda(valorOriginal) + ' · Venc. ' + formatarData(dataVencimento)"></span>
                                <br>
                                <span class="text-amber-600/80 dark:text-amber-500/80 text-xs">
                                    Esta parcela será substituída pelas novas abaixo. O orçamento não será alterado.
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Controles --}}
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-wrap items-center gap-4">
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Nº de parcelas:</label>
                            <select
                                x-model="numParcelas"
                                @change="atualizarParcelas()"
                                class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm px-3 py-1.5 focus:ring-2 focus:ring-amber-400 focus:border-transparent">
                                <template x-for="n in [2,3,4,5,6,7,8,9,10,11,12]" :key="n">
                                    <option :value="n" x-text="n + 'x'"></option>
                                </template>
                            </select>
                        </div>
                        <button
                            type="button"
                            @click="distribuirIgual(); gerarDatas()"
                            class="text-sm text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-200 underline underline-offset-2 transition">
                            Distribuir igualmente
                        </button>
                    </div>

                    {{-- Linhas das parcelas --}}
                    <div class="px-6 py-4 space-y-2 max-h-64 overflow-y-auto">
                        <div class="grid grid-cols-[24px_1fr_1fr] gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 px-1">
                            <span></span>
                            <span>Valor (R$)</span>
                            <span>Vencimento</span>
                        </div>
                        <template x-for="(parcela, index) in parcelas" :key="index">
                            <div class="grid grid-cols-[24px_1fr_1fr] gap-2 items-center">
                                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 text-right"
                                      x-text="(index + 1) + 'x'"></span>
                                <div class="relative">
                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none">R$</span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        :name="'parcelas[' + index + '][valor]'"
                                        x-model="parcela.valor"
                                        placeholder="0,00"
                                        required
                                        class="w-full pl-8 pr-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-amber-400 focus:border-transparent">
                                </div>
                                <input
                                    type="date"
                                    :name="'parcelas[' + index + '][data_vencimento]'"
                                    x-model="parcela.data_vencimento"
                                    required
                                    class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-amber-400 focus:border-transparent">
                            </div>
                        </template>
                    </div>

                    {{-- Totalizador --}}
                    <div class="px-6 py-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">
                                Total original: <strong class="text-gray-900 dark:text-white" x-text="'R$ ' + formatarMoeda(valorOriginal)"></strong>
                            </span>
                            <span :class="diffCentavos === 0 ? 'text-green-600 dark:text-green-400 font-semibold' : 'text-red-600 dark:text-red-400 font-semibold'">
                                Total novo: <strong x-text="'R$ ' + formatarMoeda(totalNovo)"></strong>
                                <span x-show="diffCentavos !== 0" class="text-xs font-normal"
                                      x-text="diffCentavos > 0 ? ' (excede R$ ' + formatarMoeda(Math.abs(diffCentavos) / 100) + ')' : ' (falta R$ ' + formatarMoeda(Math.abs(diffCentavos) / 100) + ')'">
                                </span>
                                <span x-show="diffCentavos === 0" class="text-xs font-normal text-green-500"> ✓</span>
                            </span>
                        </div>
                    </div>

                    {{-- Rodapé --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                        <button
                            type="button"
                            @click="open = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="!valido"
                            :class="valido
                                ? 'bg-amber-500 hover:bg-amber-600 text-white cursor-pointer'
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'"
                            class="px-5 py-2 text-sm font-semibold rounded-lg transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            Confirmar Renegociação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

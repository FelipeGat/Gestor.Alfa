import './bootstrap';
import Alpine from 'alpinejs';

// 1. Definição da Store Global ANTES de Alpine.start()
Alpine.store('modalCobranca', {
    open: false,
    orcamento: null,

    abrir(orcamento) {
        console.log('Alpine: Abrindo modal para:', orcamento);
        this.orcamento = orcamento;
        this.open = true;
    },

    fechar() {
        this.open = false;
        this.orcamento = null;
    }
});

// 2. Definição do Componente de Dados
Alpine.data('gerarCobranca', () => ({
    forma: '',
    parcelas: 1,
    vencimentos: [],
    valoresParcelas: [],
    mostrarParcelas: false,

    init() {
        this.$watch('$store.modalCobranca.open', (value) => {
            if (!value) {
                this.resetForm();
            } else if (this.$store.modalCobranca.orcamento?.forma_pagamento) {
                this.forma = this.$store.modalCobranca.orcamento.forma_pagamento;
                this.atualizarForma();
            }
        });
    },

    resetForm() {
        this.forma = '';
        this.parcelas = 1;
        this.vencimentos = [];
        this.valoresParcelas = [];
        this.mostrarParcelas = false;
    },

    atualizarForma() {
        const formasParceladas = ['credito', 'boleto', 'faturado'];
        this.mostrarParcelas = formasParceladas.includes(this.forma);
        if (!this.mostrarParcelas) this.parcelas = 1;
        this.gerarVencimentos();
    },

    gerarVencimentos() {
        this.vencimentos = [];
        this.valoresParcelas = [];
        if (!this.mostrarParcelas) return;
        
        const valorTotal = parseFloat(this.$store.modalCobranca.orcamento?.valor_total || 0);
        const valorPorParcela = (valorTotal / this.parcelas).toFixed(2);
        
        let dataBase = new Date();
        for (let i = 0; i < this.parcelas; i++) {
            let data = new Date(dataBase);
            data.setDate(data.getDate() + (30 * (i + 1)));
            this.vencimentos.push(data.toISOString().split('T')[0]);
            
            // Ajuste na última parcela para garantir que a soma seja exata
            if (i === this.parcelas - 1) {
                const somaAnteriores = this.valoresParcelas.reduce((acc, val) => acc + parseFloat(val), 0);
                this.valoresParcelas.push((valorTotal - somaAnteriores).toFixed(2));
            } else {
                this.valoresParcelas.push(valorPorParcela);
            }
        }
    },

    ajustarValores(indexAlterado) {
        const valorTotal = parseFloat(this.$store.modalCobranca.orcamento?.valor_total || 0);
        const valorAlterado = parseFloat(this.valoresParcelas[indexAlterado] || 0);
        
        // Calcular quanto sobrou para distribuir nas outras parcelas
        let somaOutros = 0;
        let countOutros = 0;
        
        for (let i = 0; i < this.parcelas; i++) {
            if (i !== indexAlterado) {
                somaOutros += parseFloat(this.valoresParcelas[i] || 0);
                countOutros++;
            }
        }
        
        const totalSemAlterado = valorTotal - valorAlterado;
        
        if (countOutros > 0 && totalSemAlterado >= 0) {
            const novoValorPorParcela = (totalSemAlterado / countOutros).toFixed(2);
            let somaRecalculada = valorAlterado;
            
            for (let i = 0; i < this.parcelas; i++) {
                if (i !== indexAlterado) {
                    if (i === this.parcelas - 1) {
                        // Última parcela ajusta a diferença
                        this.valoresParcelas[i] = (valorTotal - somaRecalculada).toFixed(2);
                    } else {
                        this.valoresParcelas[i] = novoValorPorParcela;
                        somaRecalculada += parseFloat(novoValorPorParcela);
                    }
                }
            }
        }
    },

    getValorTotal() {
        return this.valoresParcelas.reduce((acc, val) => acc + parseFloat(val || 0), 0).toFixed(2);
    },

    formatarMoeda(valor) {
        return parseFloat(valor || 0).toLocaleString('pt-BR', { 
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2 
        });
    }
}));

// 3. Garante que o Alpine esteja no escopo do window e inicia
if (!window.__alpine_started) {
    window.Alpine = Alpine;
    Alpine.start();
    window.__alpine_started = true;
}

// Delegated listener para botões de gerar cobrança
document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-role="gerar-cobranca"]');
    if (!btn) return;
    try {
        var orc = btn.getAttribute('data-orc');
        var obj = orc ? JSON.parse(orc) : null;

        function callStore() {
            try {
                if (window.Alpine && typeof window.Alpine.store === 'function') {
                    window.Alpine.store('modalCobranca').abrir(obj);
                    console.log('financeiro: called Alpine.store modalCobranca.abrir', obj);
                    return true;
                }
            } catch (err) {
                console.error('financeiro: error calling Alpine.store', err);
            }
            return false;
        }

        if (!callStore()) {
            // se Alpine ainda não inicializou, aguardar evento
            document.addEventListener('alpine:initialized', function () {
                callStore();
            }, { once: true });
        }
    } catch (err) {
        console.error('financeiro: error parsing data-orc', err);
    }
});

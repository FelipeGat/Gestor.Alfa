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
        if (!this.mostrarParcelas) return;
        let dataBase = new Date();
        for (let i = 0; i < this.parcelas; i++) {
            let data = new Date(dataBase);
            data.setDate(data.getDate() + (30 * (i + 1)));
            this.vencimentos.push(data.toISOString().split('T')[0]);
        }
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

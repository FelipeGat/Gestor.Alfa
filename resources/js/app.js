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
    valoresOriginais: [],
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
            // Primeira parcela pode ser hoje (i * 30), demais são +30 dias
            data.setDate(data.getDate() + (30 * i));
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

    salvarValorOriginal(index) {
        // Salvar o valor antes de começar a editar
        this.valoresOriginais[index] = this.valoresParcelas[index];
    },

    ajustarValores(indexAlterado) {
        // Verificar se o valor realmente mudou
        const valorAtual = this.valoresParcelas[indexAlterado];
        const valorOriginal = this.valoresOriginais[indexAlterado];
        
        if (valorAtual === valorOriginal) {
            // Valor não mudou, não fazer nada
            return;
        }
        
        const valorTotal = parseFloat(this.$store.modalCobranca.orcamento?.valor_total || 0);
        let valorAlterado = parseFloat(this.valoresParcelas[indexAlterado] || 0);
        
        // Validar se o valor não é negativo
        if (valorAlterado < 0) {
            valorAlterado = 0;
            this.valoresParcelas[indexAlterado] = '0.00';
        }
        
        // Calcular o valor restante a ser distribuído
        const valorRestante = valorTotal - valorAlterado;
        const outrasParcelasCount = this.parcelas - 1;
        
        if (outrasParcelasCount > 0 && valorRestante >= 0) {
            // Distribuir o valor restante igualmente entre as outras parcelas
            const valorPorParcela = (valorRestante / outrasParcelasCount).toFixed(2);
            let somaDistribuida = valorAlterado;
            
            for (let i = 0; i < this.parcelas; i++) {
                if (i !== indexAlterado) {
                    if (i === this.parcelas - 1 || (i === this.parcelas - 2 && indexAlterado === this.parcelas - 1)) {
                        // Última parcela (ou penúltima se a última for a alterada) ajusta a diferença para garantir soma exata
                        this.valoresParcelas[i] = (valorTotal - somaDistribuida).toFixed(2);
                    } else {
                        this.valoresParcelas[i] = valorPorParcela;
                        somaDistribuida += parseFloat(valorPorParcela);
                    }
                }
            }
        }
    },

    recalcularDatas(indexAlterado) {
        if (!this.vencimentos[indexAlterado]) return;
        
        // Pegar a data alterada
        const dataBase = new Date(this.vencimentos[indexAlterado]);
        
        // Recalcular as datas seguintes a partir da data alterada
        for (let i = indexAlterado + 1; i < this.parcelas; i++) {
            let novaData = new Date(dataBase);
            // Adicionar (i - indexAlterado) meses
            novaData.setMonth(dataBase.getMonth() + (i - indexAlterado));
            this.vencimentos[i] = novaData.toISOString().split('T')[0];
        }
    },

    validarEEnviar(event) {
        const valorTotal = parseFloat(this.$store.modalCobranca.orcamento?.valor_total || 0);
        const somaDistribuida = this.valoresParcelas.reduce((acc, val) => acc + parseFloat(val || 0), 0);
        
        // Validar se a soma total não ultrapassa o valor original
        if (somaDistribuida > valorTotal) {
            alert(`Erro: A soma das parcelas (R$ ${somaDistribuida.toFixed(2)}) ultrapassa o valor total da cobrança (R$ ${valorTotal.toFixed(2)}).\n\nPor favor, ajuste os valores antes de salvar.`);
            return false;
        }
        
        // Se passou na validação, submeter o formulário
        event.target.submit();
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

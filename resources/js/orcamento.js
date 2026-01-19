/* =========================================================
   ORÇAMENTO - CREATE & EDIT (INTEGRADO E CORRIGIDO)
   ========================================================= */

/* ================= ESTADO GLOBAL ================= */
window.itens = [];
window.taxas = [];

/* ================= FUNÇÕES GLOBAIS (HTML inline) ================= */
window.atualizarQtd = (index, qtd) => {
    if (itens[index]) {
        itens[index].quantidade = Math.max(1, parseInt(qtd || 1));
        renderTabela();
    }
};

window.atualizarPreco = (index, valor) => {
    if (itens[index]) {
        const preco = parseFloat(valor);
        itens[index].preco_venda = isNaN(preco) ? 0 : preco;
        renderTabela();
    }
};

window.removerItem = (index) => {
    itens.splice(index, 1);
    renderTabela();
};

/* ================= DOM READY ================= */
document.addEventListener('DOMContentLoaded', () => {

    /* ================= ROOT ================= */
    const root = document.getElementById('orcamento-root');
    if (!root) return;

    const urlBusca = root.dataset.urlBusca;
    const itensIniciais = root.dataset.itens ? JSON.parse(root.dataset.itens) : [];

    /* =========================================================
       NÚMERO DO ORÇAMENTO (AUTOMÁTICO POR EMPRESA)
    ========================================================= */
    const empresaSelect = document.querySelector('[name="empresa_id"]');
    const numeroInput = document.querySelector('[name="numero_orcamento"]');

    if (empresaSelect && numeroInput && !numeroInput.value) {
        empresaSelect.addEventListener('change', async () => {
            const empresaId = empresaSelect.value;
            numeroInput.value = '';

            if (!empresaId) return;

            try {
                const res = await fetch(`/orcamentos/gerar-numero/${empresaId}`);
                if (!res.ok) throw new Error('Erro ao gerar número');
                const data = await res.json();
                numeroInput.value = data.numero ?? '';
            } catch (e) {
                console.error('Erro ao gerar número do orçamento:', e);
            }
        });
    }

    /* =========================================================
       BUSCA DE CLIENTES E PRÉ-CLIENTES
    ========================================================= */
    const inputNome = document.getElementById('cliente_nome');
    const inputClienteId = document.getElementById('cliente_id');
    const inputPreClienteId = document.getElementById('pre_cliente_id');
    const inputTipo = document.getElementById('cliente_tipo');
    const resultados = document.getElementById('cliente-resultados');
    const btnPreCadastro = document.getElementById('btn-pre-cadastro');

    let timeoutCliente = null;

    async function buscarClientes(q = '') {
        try {
            const res = await fetch('/busca-clientes?q=' + encodeURIComponent(q));
            if (!res.ok) throw new Error('Erro na busca');
            return await res.json();
        } catch (e) {
            console.error('Erro ao buscar clientes:', e);
            return [];
        }
    }

    function limparSelecaoCliente() {
        if (inputClienteId) inputClienteId.value = '';
        if (inputPreClienteId) inputPreClienteId.value = '';
        if (inputTipo) inputTipo.value = '';
    }

    function renderClientes(lista) {
        if (!resultados) return;
        resultados.innerHTML = '';

        if (!lista.length) {
            resultados.classList.add('hidden');
            btnPreCadastro?.classList.remove('hidden');
            return;
        }

        btnPreCadastro?.classList.add('hidden');

        lista.forEach(item => {
            const nome = item.nome_fantasia || item.razao_social || item.nome || '—';
            const div = document.createElement('div');
            div.className = 'px-4 py-3 hover:bg-blue-50 cursor-pointer text-sm border-b';
            div.innerHTML = `
                <strong>${nome}</strong><br>
                <span class="text-xs text-gray-500">
                    ${item.cpf_cnpj ?? ''} • ${item.tipo === 'cliente' ? 'Cliente' : 'Pré-Cliente'}
                </span>
            `;

            div.onclick = () => {
                inputNome.value = nome;
                limparSelecaoCliente();

                if (item.tipo === 'cliente') {
                    if (inputClienteId) inputClienteId.value = item.id;
                    if (inputTipo) inputTipo.value = 'cliente';
                } else {
                    if (inputPreClienteId) inputPreClienteId.value = item.id;
                    if (inputTipo) inputTipo.value = 'pre_cliente';
                }
                resultados.classList.add('hidden');
            };
            resultados.appendChild(div);
        });

        resultados.classList.remove('hidden');
    }

    inputNome?.addEventListener('input', () => {
        clearTimeout(timeoutCliente);
        limparSelecaoCliente();

        const q = inputNome.value.trim();
        if (!q) {
            resultados?.classList.add('hidden');
            btnPreCadastro?.classList.add('hidden');
            return;
        }

        timeoutCliente = setTimeout(async () => {
            const data = await buscarClientes(q);
            renderClientes(data);
        }, 300);
    });

    document.addEventListener('click', e => {
        if (resultados && !e.target.closest('#cliente_nome') && !e.target.closest('#cliente-resultados')) {
            resultados.classList.add('hidden');
        }
    });

    /* =========================================================
       ELEMENTOS DA TABELA E RESUMO
    ========================================================= */
    const tabelaServicos = document.getElementById('itens-servicos');
    const tabelaProdutos = document.getElementById('itens-produtos');
    const totalServicosSpan = document.getElementById('total-servicos');
    const totalProdutosSpan = document.getElementById('total-produtos');
    const resumoServicos = document.getElementById('resumo-servicos');
    const resumoProdutos = document.getElementById('resumo-produtos');
    const totalOrcamentoSpan = document.getElementById('total-orcamento');
    const btnAddTaxa = document.getElementById('btn-add-taxa');
    const listaTaxas = document.getElementById('lista-taxas');

    /* ================= UTIL ================= */
    const formatar = (valor) => Number(valor || 0).toFixed(2).replace('.', ',');

    function atualizarInputsHidden(totalTaxasCalculado) {
        const container = document.getElementById('inputs-itens-hidden');
        if (!container) return;
        container.innerHTML = '';

        // Itens (Serviços e Produtos)
        itens.forEach((item, i) => {
            container.innerHTML += `
                <input type="hidden" name="itens[${i}][item_comercial_id]" value="${item.id}">
                <input type="hidden" name="itens[${i}][quantidade]" value="${item.quantidade}">
                <input type="hidden" name="itens[${i}][valor_unitario]" value="${item.preco_venda}">
            `;
        });

        // Taxas individuais (para persistência se necessário)
        taxas.forEach((t, i) => {
            container.innerHTML += `
                <input type="hidden" name="taxas_detalhe[${i}][nome]" value="${t.nome}">
                <input type="hidden" name="taxas_detalhe[${i}][tipo]" value="${t.tipo}">
                <input type="hidden" name="taxas_detalhe[${i}][valor]" value="${t.valor}">
            `;
        });

        // Valor total das taxas (conforme solicitado no segundo código)
        container.innerHTML += `<input type="hidden" name="taxas" value="${totalTaxasCalculado.toFixed(2)}">`;
    }

    /* =========================================================
       TAXAS / IMPOSTOS
    ========================================================= */
    btnAddTaxa?.addEventListener('click', () => {
        taxas.push({ nome: '', tipo: 'valor', valor: 0 });
        renderTaxas();
    });

    window.renderTaxas = function() {
        if (!listaTaxas) return;
        listaTaxas.innerHTML = '';

        taxas.forEach((taxa, index) => {
            const div = document.createElement('div');
            div.className = 'flex gap-2 items-center mb-2';
            div.innerHTML = `
                <input type="text" placeholder="Nome" class="w-1/3 border rounded px-2 py-1 text-sm" 
                    value="${taxa.nome}" onchange="taxas[${index}].nome = this.value">
                
                <input type="number" step="0.01" class="w-1/3 border rounded px-2 py-1 text-sm" 
                    value="${taxa.valor}" onchange="taxas[${index}].valor = parseFloat(this.value || 0); renderTabela();">
                
                <select class="border rounded px-2 py-1 text-sm" 
                    onchange="taxas[${index}].tipo = this.value; renderTabela();">
                    <option value="valor" ${taxa.tipo === 'valor' ? 'selected' : ''}>R$</option>
                    <option value="percentual" ${taxa.tipo === 'percentual' ? 'selected' : ''}>%</option>
                </select>
                
                <button type="button" class="text-red-500" onclick="taxas.splice(${index},1); renderTaxas(); renderTabela();">
                    🗑️
                </button>
            `;
            listaTaxas.appendChild(div);
        });
        renderTabela();
    };

    /* =========================================================
       RENDERIZAR TABELA PRINCIPAL
    ========================================================= */
    window.renderTabela = function () {
        if (!tabelaServicos || !tabelaProdutos) return;

        tabelaServicos.innerHTML = '';
        tabelaProdutos.innerHTML = '';

        let totalServicos = 0;
        let totalProdutos = 0;

        // Renderiza itens (do mais novo para o mais antigo)
        for (let i = itens.length - 1; i >= 0; i--) {
            const item = itens[i];
            const subtotal = item.quantidade * item.preco_venda;

            const linha = `
                <tr class="border-b">
                    <td class="px-3 py-2 text-sm">${item.nome}</td>
                    <td class="px-3 py-2 text-center">
                        <input type="number" min="1" class="w-16 border rounded text-center" 
                            value="${item.quantidade}" onchange="atualizarQtd(${i}, this.value)">
                    </td>
                    <td class="px-3 py-2 text-right">
                        <input type="number" step="0.01" class="w-24 border rounded text-right" 
                            value="${item.preco_venda}" onchange="atualizarPreco(${i}, this.value)">
                    </td>
                    <td class="px-3 py-2 text-right text-sm">
                        R$ ${formatar(subtotal)}
                    </td>
                    <td class="px-3 py-2 text-center">
                        <button type="button" class="text-red-500" onclick="removerItem(${i})">🗑️</button>
                    </td>
                </tr>
            `;

            if (item.tipo === 'servico') {
                totalServicos += subtotal;
                tabelaServicos.innerHTML += linha;
            } else {
                totalProdutos += subtotal;
                tabelaProdutos.innerHTML += linha;
            }
        }

        // Cálculo de Taxas
        const baseCalculo = totalServicos + totalProdutos;
        let totalTaxas = 0;
        taxas.forEach(t => {
            if (!t.valor) return;
            totalTaxas += (t.tipo === 'percentual') ? (baseCalculo * t.valor / 100) : t.valor;
        });

        // Atualização da UI
        if (totalServicosSpan) totalServicosSpan.textContent = formatar(totalServicos);
        if (totalProdutosSpan) totalProdutosSpan.textContent = formatar(totalProdutos);
        if (resumoServicos) resumoServicos.textContent = formatar(totalServicos);
        if (resumoProdutos) resumoProdutos.textContent = formatar(totalProdutos);
        if (totalOrcamentoSpan) totalOrcamentoSpan.textContent = formatar(baseCalculo + totalTaxas);

        atualizarInputsHidden(totalTaxas);
    };

    /* =========================================================
       BUSCA DE SERVIÇOS / PRODUTOS
    ========================================================= */
    const btnAddServico = document.getElementById('btn-add-servico');
    const btnAddProduto = document.getElementById('btn-add-produto');

    btnAddServico?.addEventListener('click', () => toggleBusca('servico'));
    btnAddProduto?.addEventListener('click', () => toggleBusca('produto'));

    function toggleBusca(tipo) {
        document.getElementById('busca-servico-wrapper')?.classList.add('hidden');
        document.getElementById('busca-produto-wrapper')?.classList.add('hidden');
        
        const wrapper = document.getElementById(`busca-${tipo}-wrapper`);
        if (!wrapper) return;
        
        wrapper.classList.remove('hidden');
        wrapper.querySelector('input')?.focus();
    }

    // Setup para ambos os tipos
    ['servico', 'produto'].forEach(tipo => {
        const input = document.getElementById(`busca-${tipo}`);
        const resultado = document.getElementById(`resultado-${tipo}`);
        if (!input || !resultado) return;

        let timeout;
        input.addEventListener('input', () => {
            clearTimeout(timeout);
            const q = input.value.trim();
            if (!q) {
                resultado.classList.add('hidden');
                return;
            }

            timeout = setTimeout(async () => {
                try {
                    const res = await fetch(`${urlBusca}?q=${encodeURIComponent(q)}`);
                    const data = await res.json();
                    renderResultadoItens(data.filter(i => i.tipo === tipo), resultado);
                } catch (e) {
                    console.error(`Erro ao buscar ${tipo}:`, e);
                }
            }, 300);
        });
    });

    function renderResultadoItens(lista, container) {
        container.innerHTML = '';
        if (!lista.length) {
            container.classList.add('hidden');
            return;
        }

        lista.forEach(item => {
            const div = document.createElement('div');
            div.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm border-b';
            div.innerHTML = `
                <strong>${item.nome}</strong><br>
                <span class="text-xs text-gray-500">R$ ${formatar(item.preco_venda)}</span>
            `;
            div.onclick = () => {
                itens.push({
                    id: item.id,
                    nome: item.nome,
                    tipo: item.tipo,
                    preco_venda: Number(item.preco_venda),
                    quantidade: 1
                });
                renderTabela();
                container.classList.add('hidden');
                // Limpa o input após adicionar
                const input = container.id.includes('servico') ? document.getElementById('busca-servico') : document.getElementById('busca-produto');
                if (input) input.value = '';
            };
            container.appendChild(div);
        });
        container.classList.remove('hidden');
    }

    /* =========================================================
       CARREGAR DADOS INICIAIS (MODO EDIÇÃO)
    ========================================================= */
    if (Array.isArray(itensIniciais) && itensIniciais.length) {
        window.itens = itensIniciais.map(item => ({
            id: item.id,
            nome: item.nome,
            tipo: item.tipo,
            preco_venda: Number(item.preco_venda),
            quantidade: Number(item.quantidade)
        }));
        renderTabela();
    }

    /* =========================================================
                FORMAS DE PAGAMENTO
    ========================================================= */
    function atualizarFormasPagamento() {
        const formas = [];

        document.querySelectorAll('.fp-check').forEach((check, index) => {
            const wrapper = check.closest('div, label');
            const parcelasInput = wrapper?.querySelector('.fp-parcelas');

            if (check.checked) {
                formas.push({
                    tipo: check.value,
                    parcelas: parcelasInput ? Number(parcelasInput.value || 1) : 1
                });
            }

            if (parcelasInput) {
                parcelasInput.disabled = !check.checked;
            }
        });

        let hidden = document.querySelector('[name="forma_pagamento"]');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'forma_pagamento';
            document.querySelector('form').appendChild(hidden);
        }

        hidden.value = JSON.stringify(formas);
    }

    document.querySelectorAll('.fp-check, .fp-parcelas')
        .forEach(el => el.addEventListener('change', atualizarFormasPagamento));

});

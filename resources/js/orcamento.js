/* =========================================================
   ORÇAMENTO - CREATE & EDIT
   ========================================================= */

/* ================= ESTADO GLOBAL ================= */
window.itens = [];

/* ================= FUNÇÕES GLOBAIS (usadas no HTML inline) ================= */
window.atualizarQtd = (index, qtd) => {
    itens[index].quantidade = Math.max(1, parseInt(qtd || 1));
    renderTabela();
};

window.atualizarPreco = (index, valor) => {
    const preco = parseFloat(valor);
    itens[index].preco_venda = isNaN(preco) ? 0 : preco;
    renderTabela();
};

window.removerItem = (index) => {
    itens.splice(index, 1);
    renderTabela();
};

/* ================= DOM READY ================= */
document.addEventListener('DOMContentLoaded', () => {

    /* ================= ROOT (CONTRATO COM BLADE) ================= */
    const root = document.getElementById('orcamento-root');
    if (!root) return;

    const urlBusca = root.dataset.urlBusca;
    const itensIniciais = root.dataset.itens
        ? JSON.parse(root.dataset.itens)
        : [];

    /* ================= ELEMENTOS ================= */
    const tabelaServicos = document.getElementById('itens-servicos');
    const tabelaProdutos = document.getElementById('itens-produtos');

    const totalServicosSpan = document.getElementById('total-servicos');
    const totalProdutosSpan = document.getElementById('total-produtos');
    const resumoServicos = document.getElementById('resumo-servicos');
    const resumoProdutos = document.getElementById('resumo-produtos');
    const totalOrcamentoSpan = document.getElementById('total-orcamento');

    const inputDesconto = document.querySelector('[name="desconto"]');
    const inputTaxas = document.querySelector('[name="taxas"]');

    /* ================= UTIL ================= */
    function formatar(valor) {
        return Number(valor).toFixed(2).replace('.', ',');
    }

    function atualizarInputsHidden() {
        const container = document.getElementById('inputs-itens-hidden');
        if (!container) return;

        container.innerHTML = '';

        itens.forEach((item, i) => {
            container.innerHTML += `
                <input type="hidden" name="itens[${i}][item_comercial_id]" value="${item.id}">
                <input type="hidden" name="itens[${i}][quantidade]" value="${item.quantidade}">
                <input type="hidden" name="itens[${i}][valor_unitario]" value="${item.preco_venda}">
            `;
        });
    }

    /* ================= RENDER TABELA ================= */
    window.renderTabela = function () {
        tabelaServicos.innerHTML = '';
        tabelaProdutos.innerHTML = '';

        let totalServicos = 0;
        let totalProdutos = 0;

        for (let i = itens.length - 1; i >= 0; i--) {
            const item = itens[i];
            const index = i;

            const subtotal = item.quantidade * item.preco_venda;

            const linha = `
                <tr>
                    <td class="px-3 py-2 text-sm">${item.nome}</td>

                    <td class="px-3 py-2 text-center">
                        <input type="number" min="1" value="${item.quantidade}"
                            class="w-16 border rounded text-center"
                            onchange="atualizarQtd(${index}, this.value)">
                    </td>

                    <td class="px-3 py-2 text-right">
                        <input type="number" step="0.01" value="${item.preco_venda}"
                            class="w-24 border rounded text-right"
                            onchange="atualizarPreco(${index}, this.value)">
                    </td>

                    <td class="px-3 py-2 text-right text-sm">
                        R$ ${formatar(subtotal)}
                    </td>

                    <td class="px-3 py-2 text-center">
                        <button type="button" onclick="removerItem(${index})">🗑️</button>
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


        totalServicosSpan.textContent = formatar(totalServicos);
        totalProdutosSpan.textContent = formatar(totalProdutos);
        resumoServicos.textContent = formatar(totalServicos);
        resumoProdutos.textContent = formatar(totalProdutos);

        const desconto = Number(inputDesconto?.value || 0);
        const taxas = Number(inputTaxas?.value || 0);

        totalOrcamentoSpan.textContent = formatar(
            totalServicos + totalProdutos - desconto + taxas
        );

        atualizarInputsHidden();
    };

    inputDesconto?.addEventListener('input', renderTabela);
    inputTaxas?.addEventListener('input', renderTabela);

    /* ================= BUSCA SERVIÇOS / PRODUTOS ================= */
    document.getElementById('btn-add-servico')
        ?.addEventListener('click', () => toggleBusca('servico'));

    document.getElementById('btn-add-produto')
        ?.addEventListener('click', () => toggleBusca('produto'));

    function toggleBusca(tipo) {
        document.getElementById('busca-servico-wrapper')?.classList.add('hidden');
        document.getElementById('busca-produto-wrapper')?.classList.add('hidden');

        const wrapper = document.getElementById(`busca-${tipo}-wrapper`);
        if (!wrapper) return;

        wrapper.classList.remove('hidden');
        wrapper.querySelector('input')?.focus();
    }

    setupBusca('servico');
    setupBusca('produto');

    function setupBusca(tipo) {
        const input = document.getElementById(`busca-${tipo}`);
        const resultado = document.getElementById(`resultado-${tipo}`);
        if (!input || !resultado) return;

        let timeout;

        input.addEventListener('input', () => {
            clearTimeout(timeout);
            const q = input.value.trim();
            if (!q) return;

            timeout = setTimeout(async () => {
                try {
                    const res = await fetch(`${urlBusca}?q=${encodeURIComponent(q)}`);
                    const data = await res.json();

                    renderResultado(
                        data.filter(i => i.tipo === tipo),
                        resultado
                    );
                } catch (e) {
                    console.error('Erro ao buscar itens', e);
                }
            }, 300);
        });
    }

    function renderResultado(lista, container) {
        container.innerHTML = '';

        if (!lista.length) {
            container.classList.add('hidden');
            return;
        }

        lista.forEach(item => {
            const div = document.createElement('div');
            div.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm';

            div.innerHTML = `
                <strong>${item.nome}</strong><br>
                <span class="text-xs text-gray-500">
                    R$ ${Number(item.preco_venda).toFixed(2)}
                </span>
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
            };

            container.appendChild(div);
        });

        container.classList.remove('hidden');
    }

    /* ================= CARREGA ITENS (EDIT) ================= */
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
});

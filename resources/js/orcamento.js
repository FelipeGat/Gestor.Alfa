
window.itens = [];
window.taxas = [];

function fecharBuscas() {
    const ws = document.getElementById('busca-servico-wrapper');
    const wp = document.getElementById('busca-produto-wrapper');
    const rs = document.getElementById('resultado-servico');
    const rp = document.getElementById('resultado-produto');
    const is = document.getElementById('busca-servico');
    const ip = document.getElementById('busca-produto');

    ws?.classList.add('hidden');
    wp?.classList.add('hidden');
    rs?.classList.add('hidden');
    rp?.classList.add('hidden');

    if (rs) rs.innerHTML = '';
    if (rp) rp.innerHTML = '';
    if (is) is.value = '';
    if (ip) ip.value = '';

    document.activeElement?.blur();
}


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

document.addEventListener('DOMContentLoaded', () => {
                
        window.taxas = window.taxas || [];

    const root = document.getElementById('orcamento-root');
    if (!root) return;

    const urlBusca = root.dataset.urlBusca;
    
    // Carregamento de dados iniciais (Modo Edição)
    const itensIniciais = root.dataset.itens ? JSON.parse(root.dataset.itens) : [];
    const taxasIniciais = root.dataset.taxas ? JSON.parse(root.dataset.taxas) : [];

    /* ================= NÚMERO AUTOMÁTICO ================= */
    const empresaSelect = document.querySelector('[name="empresa_id"]');
    const numeroInput = document.querySelector('[name="numero_orcamento"]');

    if (empresaSelect && numeroInput && !numeroInput.value) {
        empresaSelect.addEventListener('change', async () => {
            const empresaId = empresaSelect.value;
            numeroInput.value = '';
            if (!empresaId) return;
            try {
                const res = await fetch(`/orcamentos/gerar-numero/${empresaId}`);
                const data = await res.json();
                numeroInput.value = data.numero ?? '';
            } catch (e) { console.error('Erro ao gerar número:', e); }
        });
    }

    /* ================= BUSCA CLIENTES ================= */
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
            return await res.json();
        } catch (e) { return []; }
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
            div.className = 'px-4 py-3 hover:bg-blue-50 cursor-pointer text-sm border-b border-gray-50 transition-colors';
            div.innerHTML = `<strong>${nome}</strong><br><span class="text-xs text-gray-500">${item.cpf_cnpj ?? ''} • ${item.razao_social ?? ''} • ${item.tipo === 'cliente' ? 'Cliente' : 'Pré-Cliente'}</span>`;
            div.onclick = () => {
                inputNome.value = nome;
                if (inputClienteId) inputClienteId.value = item.tipo === 'cliente' ? item.id : '';
                if (inputPreClienteId) inputPreClienteId.value = item.tipo !== 'cliente' ? item.id : '';
                if (inputTipo) inputTipo.value = item.tipo;
                resultados.classList.add('hidden');
            };
            resultados.appendChild(div);
        });
        resultados.classList.remove('hidden');
    }

    inputNome?.addEventListener('input', () => {
        clearTimeout(timeoutCliente);
        const q = inputNome.value.trim();
        if (!q) { resultados?.classList.add('hidden'); return; }
        timeoutCliente = setTimeout(async () => { renderClientes(await buscarClientes(q)); }, 300);
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#cliente_nome') && !e.target.closest('#cliente-resultados')) {
            resultados?.classList.add('hidden');
        }
    });

    /* ================= DESCONTOS ================= */
    const descServValor = document.getElementById('desconto-servico-valor');
    const descServTipo = document.getElementById('desconto-servico-tipo');
    const descProdValor = document.getElementById('desconto-produto-valor');
    const descProdTipo = document.getElementById('desconto-produto-tipo');

    [descServValor, descServTipo, descProdValor, descProdTipo].forEach(el => {
        el?.addEventListener('change', () => renderTabela());
        el?.addEventListener('input', () => renderTabela());
    });

    /* ================= TAXAS ================= */
    const btnAddTaxa = document.getElementById('btn-add-taxa');
    const listaTaxas = document.getElementById('lista-taxas');

    btnAddTaxa?.addEventListener('click', () => {
        taxas.push({ nome: '', tipo: 'valor', valor: 0 });
        renderTaxas();
    });

    window.renderTaxas = function() {
    if (!listaTaxas) return;
    listaTaxas.innerHTML = '';

    taxas.forEach((taxa, index) => {
        const div = document.createElement('div');
        div.className = 'flex gap-2 items-center mb-2 animate-fadeIn';

        const nome = document.createElement('input');
        nome.type = 'text';
        nome.placeholder = 'Nome da Taxa';
        nome.className = 'input-field flex-1';
        nome.value = taxa.nome || '';
        nome.addEventListener('input', e => {
            taxas[index].nome = e.target.value;
        });

        const valor = document.createElement('input');
        valor.type = 'number';
        valor.step = '0.01';
        valor.className = 'input-field w-24';
        valor.value = taxa.valor ?? 0;
        valor.addEventListener('input', e => {
            taxas[index].valor = parseFloat(e.target.value || 0);
            renderTabela();
        });

        const tipo = document.createElement('select');
        tipo.className = 'input-field w-20';
        tipo.innerHTML = `
            <option value="valor">R$</option>
            <option value="percentual">%</option>
        `;
        tipo.value = taxa.tipo || 'valor';
        tipo.addEventListener('change', e => {
            taxas[index].tipo = e.target.value;
            renderTabela();
        });

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'text-red-500 p-2 hover:bg-red-50 rounded-lg transition-colors';
        btn.textContent = '🗑️';
        btn.addEventListener('click', () => {
            taxas.splice(index, 1);
            renderTaxas();
            renderTabela();
        });

        div.appendChild(nome);
        div.appendChild(valor);
        div.appendChild(tipo);
        div.appendChild(btn);

        listaTaxas.appendChild(div);
    });
};


    /* ================= CONDIÇÕES DE PAGAMENTO ================= */
    const fpChecks = document.querySelectorAll('.fp-check');

    fpChecks.forEach(check => {
        check.addEventListener('change', function () {

            // sobe até o bloco do método de pagamento
            const container = this.closest('.flex.items-center');

            if (!container) return;

            const parcelasInput = container.querySelector('.fp-parcelas');

            if (!parcelasInput) return;

            if (this.checked) {
                parcelasInput.disabled = false;
                parcelasInput.focus();
                parcelasInput.select();
            } else {
                parcelasInput.disabled = true;
                parcelasInput.value = 1;
            }
        });
    });

    /* ================= RENDER TABELA ================= */
    window.renderTabela = function () {
        const tabServ = document.getElementById('itens-servicos');
        const tabProd = document.getElementById('itens-produtos');
        if (!tabServ || !tabProd) return;

        tabServ.innerHTML = ''; tabProd.innerHTML = '';
        let totalServ = 0; let totalProd = 0;

        itens.forEach((item, i) => {
            const subtotal = item.quantidade * item.preco_venda;
            const linha = `<tr class="hover:bg-gray-50 transition-colors">
                <td class="table-cell font-medium">${item.nome}</td>
                <td class="table-cell text-center"><input type="number" min="1" class="w-16 border-gray-200 rounded text-center text-xs" value="${item.quantidade}" onchange="atualizarQtd(${i}, this.value)"></td>
                <td class="table-cell text-right"><input type="number" step="0.01" class="w-24 border-gray-200 rounded text-right text-xs" value="${item.preco_venda}" onchange="atualizarPreco(${i}, this.value)"></td>
                <td class="table-cell text-right font-mono font-bold text-gray-900">R$ ${subtotal.toFixed(2).replace('.',',')}</td>
                <td class="table-cell text-center"><button type="button" class="text-red-400 hover:text-red-600" onclick="removerItem(${i})">🗑️</button></td>
            </tr>`;
            if (item.tipo === 'servico') { totalServ += subtotal; tabServ.innerHTML += linha; }
            else { totalProd += subtotal; tabProd.innerHTML += linha; }
        });

        // Cálculo Descontos
        let dServ = parseFloat(descServValor?.value || 0);
        if (descServTipo?.value === 'percentual') dServ = (totalServ * dServ) / 100;
        
        let dProd = parseFloat(descProdValor?.value || 0);
        if (descProdTipo?.value === 'percentual') dProd = (totalProd * dProd) / 100;

        const totalDescontos = dServ + dProd;
        const baseComDesconto = (totalServ - dServ) + (totalProd - dProd);

        // Cálculo Taxas
            let totalTaxas = 0;

            taxas.forEach(t => {
                const valor = parseFloat(t.valor);

                if (isNaN(valor) || valor <= 0) return;

                totalTaxas += (t.tipo === 'percentual')
                    ? (baseComDesconto * valor / 100)
                    : valor;
            });

        // Atualiza UI
        document.getElementById('resumo-servicos').textContent = totalServ.toFixed(2).replace('.',',');
        document.getElementById('resumo-produtos').textContent = totalProd.toFixed(2).replace('.',',');
        
        const resDesc = document.getElementById('resumo-desconto');
        const resDescWrap = document.getElementById('resumo-desconto-wrapper');
        if (totalDescontos > 0) {
            resDesc.textContent = totalDescontos.toFixed(2).replace('.',',');
            resDescWrap.classList.remove('hidden');
        } else { resDescWrap.classList.add('hidden'); }

        const resTax = document.getElementById('resumo-taxas');
        const resTaxWrap = document.getElementById('resumo-taxas-wrapper');
        if (totalTaxas > 0) {
            resTax.textContent = totalTaxas.toFixed(2).replace('.',',');
            resTaxWrap.classList.remove('hidden');
        } else { resTaxWrap.classList.add('hidden'); }

        document.getElementById('total-orcamento').textContent = (baseComDesconto + totalTaxas).toFixed(2).replace('.',',');

        // Hidden Inputs
        const container = document.getElementById('inputs-itens-hidden');
        if (container) {
            container.innerHTML = '';
            itens.forEach((item, i) => {
                container.innerHTML += `<input type="hidden" name="itens[${i}][item_comercial_id]" value="${item.id}">
                    <input type="hidden" name="itens[${i}][quantidade]" value="${item.quantidade}">
                    <input type="hidden" name="itens[${i}][valor_unitario]" value="${item.preco_venda}">`;
            });

            const totalTaxasFormatada = Number(totalTaxas || 0).toFixed(2);
            container.innerHTML += `<input type="hidden" name="taxas" value="${totalTaxasFormatada}">`;

            // Taxas detalhadas para persistência
            taxas.forEach((t, i) => {
                container.innerHTML += `
                    <input type="hidden" name="taxas_detalhe[${i}][nome]" value="${t.nome}">
                    <input type="hidden" name="taxas_detalhe[${i}][tipo]" value="${t.tipo}">
                    <input type="hidden" name="taxas_detalhe[${i}][valor]" value="${t.valor}">`;
            });
        }
    };

    /* ================= BUSCA ITENS ================= */
    ['servico', 'produto'].forEach(tipo => {
        const btn = document.getElementById(`btn-add-${tipo}`);
        const wrap = document.getElementById(`busca-${tipo}-wrapper`);
        const inp = document.getElementById(`busca-${tipo}`);
        const res = document.getElementById(`resultado-${tipo}`);

        btn?.addEventListener('click', () => {
            fecharBuscas();
            wrap.classList.remove('hidden');
            inp.focus();
        });

        let t;
        inp?.addEventListener('input', () => {
            clearTimeout(t);
            const q = inp.value.trim();
            if (!q) { res.classList.add('hidden'); return; }
            t = setTimeout(async () => {
                const r = await fetch(`${urlBusca}?q=${encodeURIComponent(q)}`);
                const data = await r.json();
                const filtered = data.filter(i => i.tipo === tipo);
                res.innerHTML = '';
                if (!filtered.length) { res.classList.add('hidden'); return; }
                filtered.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'px-4 py-3 hover:bg-blue-50 cursor-pointer text-sm border-b border-gray-50 transition-colors';
                    div.innerHTML = `<strong>${item.nome}</strong><br><span class="text-xs text-gray-500">R$ ${item.preco_venda}</span>`;
                    div.onclick = () => {
                        itens.push({
                            id: item.id,
                            nome: item.nome,
                            tipo: item.tipo,
                            preco_venda: Number(item.preco_venda),
                            quantidade: 1
                        });

                        renderTabela();
                        fecharBuscas();
                    };
                    res.appendChild(div);
                });
                res.classList.remove('hidden');
            }, 300);
        });
    });

    // Inicialização dos dados (Modo Edição)
    if (itensIniciais.length) {
        window.itens = itensIniciais.map(i => ({ 
            id: i.item_comercial_id || i.id, 
            nome: i.item_comercial?.nome || i.nome, 
            tipo: i.item_comercial?.tipo || i.tipo, 
            preco_venda: Number(i.valor_unitario || i.preco_venda), 
            quantidade: Number(i.quantidade) 
        }));
    }
    
    if (taxasIniciais && taxasIniciais.length > 0) {
        window.taxas = taxasIniciais.map(t => ({
        nome: t.nome,
        tipo: t.tipo,
        valor: Number(t.valor)
        }));
    } else {
            // Se não tem detalhes, mas o root tem um valor de taxa total, cria uma taxa padrão
            const valorTaxaTotal = Number(root.dataset.taxaValor || 0);
            if (valorTaxaTotal > 0) {
                window.taxas = [{
                    nome: 'Taxas Adicionais',
                    tipo: 'valor',
                    valor: valorTaxaTotal
                }];
            }
        }
        renderTaxas();

    renderTabela();

        const form = document.querySelector('form');
        form?.addEventListener('submit', () => {
            renderTabela();
        });
});

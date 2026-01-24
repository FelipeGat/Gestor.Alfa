document.addEventListener('DOMContentLoaded', () => {

    const inputNome = document.getElementById('cliente_nome');
    const inputClienteId = document.getElementById('cliente_id');
    const inputPreClienteId = document.getElementById('pre_cliente_id');
    const inputTipo = document.getElementById('cliente_tipo');
    const resultados = document.getElementById('cliente-resultados');
    const btnPreCadastro = document.getElementById('btn-pre-cadastro');

    if (!inputNome || !resultados) return;

    let timeout = null;

    async function buscarClientes(q) {
        const res = await fetch('/busca-clientes?q=' + encodeURIComponent(q));
        return await res.json();
    }

    function render(lista) {
        resultados.innerHTML = '';

        if (!lista.length) {
            resultados.classList.add('hidden');
            btnPreCadastro?.classList.remove('hidden');
            return;
        }

        btnPreCadastro?.classList.add('hidden');

        lista.forEach(item => {
            const nome = item.nome_fantasia || item.razao_social || item.nome;

            const div = document.createElement('div');
            div.className = 'px-4 py-3 hover:bg-blue-50 cursor-pointer text-sm border-b transition';

            div.innerHTML = `
                <strong>${nome}</strong><br>
                <span class="text-xs text-gray-500">
                    ${item.cpf_cnpj ?? ''} • ${item.tipo === 'cliente' ? 'Cliente' : 'Pré-Cliente'}
                </span>
            `;

            div.onclick = () => {
                inputNome.value = nome;

                if (item.tipo === 'cliente') {
                    inputClienteId.value = item.id;
                    inputPreClienteId.value = '';
                } else {
                    inputPreClienteId.value = item.id;
                    inputClienteId.value = '';
                }

                inputTipo.value = item.tipo;
                resultados.classList.add('hidden');
            };

            resultados.appendChild(div);
        });

        resultados.classList.remove('hidden');
    }

    inputNome.addEventListener('input', () => {
        clearTimeout(timeout);
        const q = inputNome.value.trim();

        if (!q) {
            resultados.classList.add('hidden');
            return;
        }

        timeout = setTimeout(async () => {
            render(await buscarClientes(q));
        }, 300);
    });

    document.addEventListener('click', e => {
        if (!e.target.closest('#cliente_nome') && !e.target.closest('#cliente-resultados')) {
            resultados.classList.add('hidden');
        }
    });
});

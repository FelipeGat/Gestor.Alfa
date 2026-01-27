<div
    x-data="{ open: false, action: '' }"
    x-on:confirmar-baixa.window="
        open = true;
        action = $event.detail.action;
    "
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            Confirmar Baixa
        </h3>

        <p class="text-gray-600 mb-6 leading-relaxed">
            Tem certeza que deseja <strong>confirmar a baixa</strong> desta cobrança?
            <br>
            Esta ação registrará o pagamento no sistema e o lançamento ficará disponível
            posteriormente na tela de <strong>Movimentações</strong>.
        </p>

        <div class="modal-actions">
            <button
                type="button"
                class="btn btn-secondary modal-btn"
                x-on:click="open = false">
                Cancelar
            </button>

            <form :action="action" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-success modal-btn">
                    Confirmar Baixa
                </button>
            </form>
        </div>
    </div>
</div>
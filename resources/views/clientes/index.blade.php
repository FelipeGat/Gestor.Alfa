<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form.action.includes('clientes')) return;
            if (!form.method.toLowerCase().includes('post')) return;
            
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Salvando...';
            
            fetch(form.action, {
                method: form.method,
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'cliente-modal' }));
                    window.location.reload();
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                
                if (error.errors) {
                    // Erros de validação - mostrar no formulário
                    let errorHtml = '<div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg"><ul class="list-disc list-inside text-sm space-y-1">';
                    Object.values(error.errors).forEach(messages => {
                        messages.forEach(msg => {
                            errorHtml += `<li>${msg}</li>`;
                        });
                    });
                    errorHtml += '</ul></div>';
                    
                    // Inserir erros no topo do formulário
                    const existingError = form.querySelector('.validation-errors');
                    if (existingError) existingError.remove();
                    form.insertAdjacentHTML('afterbegin', `<div class="validation-errors">${errorHtml}</div>`);
                } else {
                    console.error('Error:', error);
                    alert('Erro ao salvar. Tente novamente.');
                }
            });
        });
    });
    </script>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Clientes']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- FILTROS --}}
            <x-filter :action="route('clientes.index')" :show-clear-button="true">
                <x-filter-field name="search" label="Pesquisar Cliente" placeholder="Nome, CPF/CNPJ ou E-mail" colSpan="lg:col-span-6" />
                <x-filter-field name="status" label="Status" type="select" placeholder="Todos" colSpan="lg:col-span-3">
                    <option value="ativo" @selected(request('status')=='ativo')>Ativo</option>
                    <option value="inativo" @selected(request('status')=='inativo')>Inativo</option>
                </x-filter-field>
            </x-filter>

            {{-- RESUMO --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #3b82f6; border-top: 1px solid #3b82f6; border-right: 1px solid #3b82f6; border-bottom: 1px solid #3b82f6; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total de Clientes</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totalClientes }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #22c55e; border-top: 1px solid #22c55e; border-right: 1px solid #22c55e; border-bottom: 1px solid #22c55e; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Ativos</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $clientesAtivos }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #ef4444; border-top: 1px solid #ef4444; border-right: 1px solid #ef4444; border-bottom: 1px solid #ef4444; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Inativos</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">{{ $clientesInativos }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #eab308; border-top: 1px solid #eab308; border-right: 1px solid #eab308; border-bottom: 1px solid #eab308; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Receita Mensal</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">R$ {{ number_format($receitaMensal, 2, ',', '.') }}</p>
                </div>
            </div>

            @if(auth()->user()->canPermissao('clientes', 'incluir'))
            <div class="flex justify-start">
                <x-button x-data="" x-on:click.prevent="openClientModal('{{ route('clientes.ajaxCreate') }}', 'Novo Cliente')" variant="success" size="sm" class="min-w-[130px]">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Adicionar
                </x-button>
            </div>
            @endif

            {{-- TABELA --}}
            @php
                $columns = [
                    ['label' => 'ID'],
                    ['label' => 'CPF/CNPJ'],
                    ['label' => 'Nome'],
                    ['label' => 'Telefone'],
                    ['label' => 'Status'],
                    ['label' => 'Ações'],
                ];
            @endphp

            @if($clientes->count())
            <x-table :columns="$columns" :data="$clientes" emptyMessage="Nenhum cliente encontrado">
                @foreach($clientes as $cliente)
                @php
                    $email = $cliente->emails->where('principal', true)->first();
                    $tel = $cliente->telefones->where('principal', true)->first();
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <x-table-cell :nowrap="true">{{ $cliente->id }}</x-table-cell>
                    <x-table-cell :nowrap="true">{{ \App\Helpers\FormatHelper::cpfCnpj($cliente->cpf_cnpj) }}</x-table-cell>
                    <x-table-cell>{{ $cliente->nome }}</x-table-cell>
                    <x-table-cell :nowrap="true">
                        @if($tel)
                            <a href="tel:{{ preg_replace('/\D/', '', $tel->valor) }}" class="hover:text-blue-600 hover:underline">
                                {{ $tel->valor }}
                            </a>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </x-table-cell>
                    <x-table-cell align="left">
                        <x-badge type="{{ $cliente->ativo ? 'success' : 'danger' }}" :icon="true">
                            {{ $cliente->ativo ? 'Ativo' : 'Inativo' }}
                        </x-badge>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="flex items-center gap-1">
                            <button type="button" x-data="" x-on:click.prevent="openClientModal('{{ route('clientes.ajaxEdit', $cliente) }}', 'Editar Cliente')" class="p-2 rounded-full inline-flex items-center justify-center text-[#3f9cae] hover:bg-[#3f9cae]/10 transition" title="Editar">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                </svg>
                            </button>
                            <x-actions 
                                :edit-url="route('clientes.edit', $cliente)" 
                                :delete-url="route('clientes.destroy', $cliente)"
                                :show-view="false"
                                :show-edit="false"
                                confirm-delete-message="Tem certeza que deseja excluir este cliente?"
                            />
                        </div>
                    </x-table-cell>
                </tr>
                @endforeach
            </x-table>

            {{-- PAGINAÇÃO --}}
            <x-pagination :paginator="$clientes" label="clientes" />
            @else
            <x-table :columns="$columns" :data="$clientes" emptyMessage="Nenhum cliente encontrado" />
            @endif

            {{-- MODAL --}}
            <div x-data="{ openModal: false }" 
                 @open-modal.window="if ($event.detail === 'cliente-modal') openModal = true"
                 @close-modal.window="if ($event.detail === 'cliente-modal') openModal = false"
                 x-show="openModal"
                 :style="openModal ? 'display: flex' : 'display: none'"
                 class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50">
                 <div class="fixed inset-0 bg-gray-500 opacity-75" @click="openModal = false"></div>
                 <div class="relative bg-white rounded-lg shadow-xl max-w-4xl mx-auto w-full" style="border: 1px solid #3f9cae; border-top-width: 4px;">
                     <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center" style="background-color: rgba(63, 156, 174, 0.05);">
                         <h3 class="text-lg font-semibold text-gray-900" id="cliente-modal-title">Novo Cliente</h3>
                         <button @click="openModal = false" class="text-gray-400 hover:text-gray-600">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                             </svg>
                         </button>
                     </div>
                      <div class="p-6 overflow-y-auto" id="cliente-modal-content" style="max-height: calc(85vh - 140px);">
                          <div class="text-center py-8 text-gray-500">Carregando...</div>
                      </div>
                 </div>
             </div>

        </div>
    </div>
</x-app-layout>

<script>
window.clienteForm = function() {
    return {
        step: 1,
        steps: [
            { label: 'Dados', title: 'Dados Básicos', description: 'Informe os dados principais do cliente' },
            { label: 'Endereço', title: 'Endereço', description: 'Endereço de localização' },
            { label: 'Contatos', title: 'Contatos', description: 'Emails e telefones para contato' },
            { label: 'Info', title: 'Informações', description: 'Dados adicionais e configurações' }
        ],
        nextStep() {
            if (this.validateStep(this.step)) {
                this.step++;
            }
        },
        prevStep() {
            this.step--;
        },
        goToStep(s) {
            if (s <= this.step || this.validateStep(s - 1)) {
                this.step = s;
            }
        },
        validateStep(stepNum) {
            // Buscar o formulário diretamente no documento
            const form = document.querySelector('#cliente-modal-content form');
            if (!form) return true;
            
            let valid = true;
            let firstInvalid = null;
            
            // Verificar se é modo de edição pelo campo hidden
            const isEditField = form.querySelector('input[name="is_edit"]');
            console.log('is_edit field:', isEditField, 'value:', isEditField?.value);
            
            // No modo edição, permitir avanço livre
            if (isEditField && isEditField.value === '1') {
                return true;
            }
            
            if (stepNum === 1) {
                const campos = ['tipo_pessoa', 'cpf_cnpj', 'data_cadastro', 'razao_social'];
                campos.forEach(name => {
                    const input = form.querySelector(`[name="${name}"]`);
                    if (input) {
                        input.classList.remove('border-red-500');
                        if (!input.value || !input.value.trim()) {
                            valid = false;
                            input.classList.add('border-red-500');
                            if (!firstInvalid) firstInvalid = input;
                        }
                    }
                });
            }
            
            if (stepNum === 3) {
                const emails = form.querySelectorAll('[name="emails[]"]');
                if (!emails || emails.length === 0) {
                    valid = false;
                }
            }
            
            if (!valid) {
                alert('Preencha os campos obrigatórios antes de continuar.');
                if (firstInvalid) firstInvalid.focus();
            }
            
            return valid;
        }
    };
};

window.openClientModal = function(url, title) {
    document.getElementById('cliente-modal-title').textContent = title || 'Novo Cliente';
    document.getElementById('cliente-modal-content').innerHTML = '<div class="text-center py-8 text-gray-500">Carregando...</div>';
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'cliente-modal' }));
    
    fetch(url)
        .then(r => r.text())
        .then(html => {
            document.getElementById('cliente-modal-content').innerHTML = html;
        })
        .catch(err => {
            document.getElementById('cliente-modal-content').innerHTML = '<div class="text-red-500 p-4">Erro ao carregar formulário</div>';
        });
};

window.closeClientModal = function() {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'cliente-modal' }));
};
</script>

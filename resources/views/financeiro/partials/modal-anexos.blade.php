{{-- Modal de Gerenciamento de Anexos --}}
<div
    x-data="modalAnexos()"
    x-show="modalAberto"
    style="display: none;"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    @keydown.escape.window="fecharModal()"
    @abrir-modal-anexos.window="abrirModal($event.detail)">

    {{-- Overlay --}}
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

    {{-- Modal Container --}}
    <div class="flex items-center justify-center min-h-screen p-4">
        <div
            class="relative bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] flex flex-col"
            @click.away="fecharModal()">

            {{-- Header --}}
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" />
                    </svg>
                    Gerenciar Anexos (NF/Boleto)
                </h3>
                <button
                    type="button"
                    @click="fecharModal()"
                    class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-6">

                {{-- Formulário de Upload --}}
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-lg p-6 border border-emerald-200">
                    <h4 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        Adicionar Novo Anexo
                    </h4>

                    <form @submit.prevent="uploadAnexo()" enctype="multipart/form-data">
                        <div class="space-y-4">
                            {{-- Tipo de Anexo --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Anexo</label>
                                <select
                                    x-model="novoAnexo.tipo"
                                    required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                                    <option value="">Selecione o tipo</option>
                                    <option value="nf">Nota Fiscal</option>
                                    <option value="boleto">Boleto</option>
                                </select>
                            </div>

                            {{-- Upload de Arquivos --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Arquivos PDF (pode selecionar múltiplos)</label>
                                <input
                                    type="file"
                                    @change="selecionarArquivos($event)"
                                    accept=".pdf"
                                    multiple
                                    required
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                                <p class="mt-1 text-xs text-gray-500">Formatos aceitos: PDF | Tamanho máximo: 10MB por arquivo</p>
                            </div>

                            {{-- Arquivos Selecionados --}}
                            <div x-show="arquivosSelecionados.length > 0" class="bg-white rounded-lg p-3 border border-gray-200">
                                <p class="text-sm font-medium text-gray-700 mb-2">Arquivos selecionados:</p>
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <template x-for="(arquivo, index) in arquivosSelecionados" :key="index">
                                        <li class="flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                            </svg>
                                            <span x-text="arquivo.name"></span>
                                            <span class="text-xs text-gray-400">(<span x-text="formatarTamanho(arquivo.size)"></span>)</span>
                                        </li>
                                    </template>
                                </ul>
                            </div>

                            {{-- Botão Enviar --}}
                            <button
                                type="submit"
                                :disabled="enviando"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-4 rounded-lg transition shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!enviando">📤 Enviar Anexo(s)</span>
                                <span x-show="enviando">⏳ Enviando...</span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Lista de Anexos Existentes --}}
                <div>
                    <h4 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                        </svg>
                        Anexos Salvos (<span x-text="anexos.length"></span>)
                    </h4>

                    {{-- Loading State --}}
                    <div x-show="carregando" class="text-center py-8">
                        <svg class="animate-spin h-8 w-8 text-emerald-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="mt-2 text-gray-600">Carregando anexos...</p>
                    </div>

                    {{-- Lista --}}
                    <div x-show="!carregando" class="space-y-3">
                        <template x-if="anexos.length === 0">
                            <div class="bg-gray-50 rounded-lg p-8 text-center border-2 border-dashed border-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <p class="text-gray-500 font-medium">Nenhum anexo encontrado</p>
                                <p class="text-gray-400 text-sm mt-1">Adicione NF ou Boleto usando o formulário acima</p>
                            </div>
                        </template>

                        <template x-for="anexo in anexos" :key="anexo.id">
                            <div class="flex items-center justify-between bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex items-center gap-3 flex-1">
                                    {{-- Ícone PDF --}}
                                    <div class="flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>

                                    {{-- Informações --}}
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-800 truncate" x-text="anexo.nome_original"></p>
                                        <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                                            <span class="px-2 py-1 rounded-full font-semibold"
                                                :class="anexo.tipo === 'nf' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700'"
                                                x-text="anexo.tipo === 'nf' ? '📄 Nota Fiscal' : '💳 Boleto'"></span>
                                            <span x-text="formatarTamanho(anexo.tamanho)"></span>
                                            <span x-text="formatarData(anexo.created_at)"></span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Ações --}}
                                <div class="flex items-center gap-2 ml-4">
                                    {{-- Botão Download --}}
                                    <a
                                        :href="`/financeiro/cobrancas/anexos/${anexo.id}/download`"
                                        target="_blank"
                                        class="p-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition"
                                        title="Baixar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </a>

                                    {{-- Botão Excluir --}}
                                    <button
                                        type="button"
                                        @click="excluirAnexo(anexo.id)"
                                        class="p-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition"
                                        title="Excluir">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50">
                <button
                    type="button"
                    @click="fecharModal()"
                    class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    function modalAnexos() {
        return {
            modalAberto: false,
            cobrancaId: null,
            anexos: [],
            carregando: false,
            enviando: false,
            novoAnexo: {
                tipo: '',
            },
            arquivosSelecionados: [],

            abrirModal(dados) {
                this.cobrancaId = dados.cobrancaId;
                this.modalAberto = true;
                this.carregarAnexos();
                this.resetarFormulario();
            },

            fecharModal() {
                this.modalAberto = false;
                this.cobrancaId = null;
                this.anexos = [];
                this.resetarFormulario();
            },

            resetarFormulario() {
                this.novoAnexo.tipo = '';
                this.arquivosSelecionados = [];
            },

            selecionarArquivos(event) {
                this.arquivosSelecionados = Array.from(event.target.files);
            },

            async carregarAnexos() {
                this.carregando = true;
                try {
                    const response = await fetch(`/financeiro/cobrancas/${this.cobrancaId}/anexos`);
                    const data = await response.json();

                    if (data.success) {
                        this.anexos = data.anexos;
                    }
                } catch (error) {
                    console.error('Erro ao carregar anexos:', error);
                    alert('Erro ao carregar anexos');
                } finally {
                    this.carregando = false;
                }
            },

            async uploadAnexo() {
                if (!this.novoAnexo.tipo || this.arquivosSelecionados.length === 0) {
                    alert('Por favor, selecione o tipo e pelo menos um arquivo');
                    return;
                }

                this.enviando = true;

                try {
                    const formData = new FormData();
                    formData.append('tipo', this.novoAnexo.tipo);

                    this.arquivosSelecionados.forEach((arquivo) => {
                        formData.append('arquivos[]', arquivo);
                    });

                    const response = await fetch(`/financeiro/cobrancas/${this.cobrancaId}/anexos`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert(data.message);
                        this.carregarAnexos();
                        this.resetarFormulario();
                        // Limpar input file
                        document.querySelector('input[type="file"]').value = '';
                        // Recarregar a página para atualizar contadores
                        window.location.reload();
                    } else {
                        alert(data.message || 'Erro ao enviar anexos');
                    }
                } catch (error) {
                    console.error('Erro ao enviar anexo:', error);
                    alert('Erro ao enviar anexo');
                } finally {
                    this.enviando = false;
                }
            },

            async excluirAnexo(anexoId) {
                if (!confirm('Tem certeza que deseja excluir este anexo?')) {
                    return;
                }

                try {
                    const response = await fetch(`/financeiro/cobrancas/anexos/${anexoId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert(data.message);
                        this.carregarAnexos();
                        // Recarregar a página para atualizar contadores
                        window.location.reload();
                    } else {
                        alert(data.message || 'Erro ao excluir anexo');
                    }
                } catch (error) {
                    console.error('Erro ao excluir anexo:', error);
                    alert('Erro ao excluir anexo');
                }
            },

            formatarTamanho(bytes) {
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';
                return (bytes / 1048576).toFixed(2) + ' MB';
            },

            formatarData(dataString) {
                const data = new Date(dataString);
                return data.toLocaleDateString('pt-BR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            }
        };
    }
</script>
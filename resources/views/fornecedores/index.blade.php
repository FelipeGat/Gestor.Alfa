<x-app-layout>
    @push('styles')
    @vite('resources/css/financeiro/index.css')
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">Fornecedores</h2>
            </div>
            <a href="{{ route('fornecedores.create') }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Novo Fornecedor
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form method="GET" class="filters-card mb-6">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label>Buscar</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Razão social, nome fantasia ou CNPJ/CPF">
                    </div>
                    <div class="filter-group">
                        <label>Status</label>
                        <select name="ativo">
                            <option value="">Todos</option>
                            <option value="1" {{ request('ativo') === '1' ? 'selected' : '' }}>Ativos</option>
                            <option value="0" {{ request('ativo') === '0' ? 'selected' : '' }}>Inativos</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="{{ route('fornecedores.index') }}" class="btn btn-secondary">Limpar</a>
                </div>
            </form>

            @if($fornecedores->count())
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>CNPJ/CPF</th>
                                <th>Razão Social</th>
                                <th>Nome Fantasia</th>
                                <th>Cidade/Estado</th>
                                <th>Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fornecedores as $fornecedor)
                            <tr>
                                <td data-label="CNPJ/CPF">{{ $fornecedor->cpf_cnpj }}</td>
                                <td data-label="Razão Social">{{ $fornecedor->razao_social }}</td>
                                <td data-label="Nome Fantasia">{{ $fornecedor->nome_fantasia ?? '—' }}</td>
                                <td data-label="Cidade/Estado">{{ $fornecedor->cidade }}/{{ $fornecedor->estado }}</td>
                                <td data-label="Status">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $fornecedor->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $fornecedor->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td data-label="Ações">
                                    <div class="table-actions">
                                        <a href="{{ route('fornecedores.edit', $fornecedor) }}"
                                            class="btn action-btn btn-icon bg-blue-600 hover:bg-blue-700 text-white"
                                            title="Editar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('fornecedores.destroy', $fornecedor) }}"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este fornecedor?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn action-btn btn-icon bg-red-400 hover:bg-red-500 text-white"
                                                title="Excluir">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            {{ $fornecedores->links() }}
            @else
            <div class="empty-state">
                <h3 class="empty-state-title">Nenhum fornecedor encontrado</h3>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
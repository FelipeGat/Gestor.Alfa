<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Atendimento #{{ $atendimento->numero_atendimento }}
        </h2>
    </x-slot>

    <div class="flex justify-center min-h-screen bg-gray-100">
        <div class="w-3/4 py-12">
            <div class="bg-white shadow rounded-lg p-6">

                {{-- POSSÍVEIS ERROS --}}
                @if ($errors->any())
                <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-300">
                    <h3 class="text-sm font-medium text-red-800 mb-2">
                        ⚠️ Verifique os erros abaixo:
                    </h3>

                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('atendimentos.update', $atendimento) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- CLIENTE --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Cliente (opcional)
                        </label>
                        <select name="cliente_id" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— Não informado —</option>
                            @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" @selected(old('cliente_id', $atendimento->cliente_id) ==
                                $cliente->id)>
                                {{ $cliente->nome }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- SOLICITANTE --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nome do Solicitante *
                            </label>
                            <input type="text" name="nome_solicitante" required
                                value="{{ old('nome_solicitante', $atendimento->nome_solicitante) }}" class="w-full border rounded-md px-3 py-2 text-sm
                                @error('nome_solicitante') border-red-500 @else border-gray-300 @enderror
                                focus:outline-none focus:ring-2 focus:ring-blue-500">

                            @error('nome_solicitante')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Telefone
                            </label>
                            <input type="text" name="telefone_solicitante"
                                value="{{ old('telefone_solicitante', $atendimento->telefone_solicitante) }}" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                E-mail
                            </label>
                            <input type="email" name="email_solicitante"
                                value="{{ old('email_solicitante', $atendimento->email_solicitante) }}" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                    </div>

                    {{-- ASSUNTO --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Assunto *
                        </label>
                        <select name="assunto_id" required class="w-full border rounded-md px-3 py-2 text-sm
                            @error('assunto_id') border-red-500 @else border-gray-300 @enderror
                            focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione o assunto</option>
                            @foreach($assuntos as $assunto)
                            <option value="{{ $assunto->id }}" @selected(old('assunto_id', $atendimento->assunto_id) ==
                                $assunto->id)>
                                {{ $assunto->nome }}
                            </option>
                            @endforeach
                        </select>

                        @error('assunto_id')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- DESCRIÇÃO --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Descrição do Atendimento *
                        </label>
                        <textarea name="descricao" rows="4" required
                            class="w-full border rounded-md px-3 py-2 text-sm
                            @error('descricao') border-red-500 @else border-gray-300 @enderror
                            focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('descricao', $atendimento->descricao) }}</textarea>

                        @error('descricao')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- PRIORIDADE / EMPRESA / TÉCNICO --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Prioridade *
                            </label>
                            <select name="prioridade" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="baixa" @selected(old('prioridade', $atendimento->prioridade) == 'baixa')>
                                    Baixa
                                </option>
                                <option value="media" @selected(old('prioridade', $atendimento->prioridade) == 'media')>
                                    Média
                                </option>
                                <option value="alta" @selected(old('prioridade', $atendimento->prioridade) == 'alta')>
                                    Alta
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Empresa *
                            </label>
                            <select name="empresa_id" required class="w-full border rounded-md px-3 py-2 text-sm
                                @error('empresa_id') border-red-500 @else border-gray-300 @enderror
                                focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione a empresa</option>
                                @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" @selected(old('empresa_id', $atendimento->empresa_id)
                                    == $empresa->id)>
                                    {{ $empresa->nome_fantasia }}
                                </option>
                                @endforeach
                            </select>

                            @error('empresa_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Técnico
                            </label>
                            <select name="funcionario_id" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">— Não atribuído —</option>
                                @foreach($funcionarios as $funcionario)
                                <option value="{{ $funcionario->id }}" @selected(old('funcionario_id', $atendimento->
                                    funcionario_id) == $funcionario->id)>
                                    {{ $funcionario->nome }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    {{-- BOTÕES --}}
                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('atendimentos.index') }}"
                            class="px-4 py-2 bg-gray-300 text-red-600 rounded hover:bg-gray-400">
                            Cancelar
                        </a>

                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-green-600 rounded">
                            Atualizar Atendimento
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>
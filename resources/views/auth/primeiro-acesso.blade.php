<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Primeiro acesso – Alterar senha
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto bg-white p-6 rounded shadow">

            <form method="POST" action="{{ route('password.first.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-gray-700">Nova senha</label>
                    <input type="password" name="password" class="mt-1 block w-full rounded border-gray-300" required>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700">Confirmar senha</label>
                    <input type="password" name="password_confirmation"
                        class="mt-1 block w-full rounded border-gray-300" required>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                    Salvar nova senha
                </button>
            </form>

        </div>
    </div>
</x-app-layout>
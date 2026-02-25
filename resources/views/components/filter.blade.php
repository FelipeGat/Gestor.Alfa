@props([
    'method' => 'GET',
    'action' => null,
    'route' => null,
    'showClearButton' => true,
    'class' => '',
])

@php
    if ($route) {
        $action = route($route);
    }
@endphp

<form 
    method="{{ $method }}" 
    action="{{ $action }}"
    class="bg-white rounded-lg p-6 {{ $class }}"
    style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);"
>
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
        {{ $slot }}
        
        {{-- Botões de ação --}}
        <div class="flex items-end gap-2">
            <x-button type="submit" variant="primary" size="sm" class="min-w-[80px]">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                </svg>
                Filtrar
            </x-button>
            
            @if($showClearButton)
                <x-button href="{{ $action }}" variant="secondary" size="sm" class="min-w-[80px]">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Limpar
                </x-button>
            @endif
        </div>
    </div>
</form>

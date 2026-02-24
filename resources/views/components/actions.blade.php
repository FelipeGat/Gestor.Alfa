@props([
    'editUrl' => null,
    'deleteUrl' => null,
    'viewUrl' => null,
    'adjustUrl' => null,
    'onclickAdjust' => null,
    'showEdit' => true,
    'showDelete' => true,
    'showView' => false,
    'showAdjust' => false,
    'editPermission' => true,
    'deletePermission' => true,
    'viewPermission' => true,
    'confirmDeleteMessage' => 'Tem certeza que deseja excluir este registro?',
    'size' => 'sm',
])

@php
    $sizes = [
        'xs' => 'p-1',
        'sm' => 'p-2',
        'md' => 'p-2.5',
    ];
    
    $iconSizes = [
        'xs' => 'w-3 h-3',
        'sm' => 'w-4 h-4',
        'md' => 'w-5 h-5',
    ];
    
    $padding = $sizes[$size] ?? $sizes['sm'];
    $iconSize = $iconSizes[$size] ?? $iconSizes['sm'];
@endphp

<div class="flex items-center gap-1">
    {{-- Botão Visualizar --}}
    @if($showView && $viewUrl && $viewPermission)
        <a href="{{ $viewUrl }}"
            class="{{ $padding }} rounded-full inline-flex items-center justify-center text-blue-600 hover:bg-blue-50 transition"
            title="Visualizar">
            <svg class="{{ $iconSize }}" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
            </svg>
        </a>
    @endif

    {{-- Botão Editar --}}
    @if($showEdit && $editUrl && $editPermission)
        <a href="{{ $editUrl }}"
            class="{{ $padding }} rounded-full inline-flex items-center justify-center text-[#3f9cae] hover:bg-[#3f9cae]/10 transition"
            title="Editar">
            <svg class="{{ $iconSize }}" fill="currentColor" viewBox="0 0 20 20">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
            </svg>
        </a>
    @endif

    {{-- Botão Ajustar --}}
    @if($showAdjust && ($adjustUrl || $onclickAdjust))
        <button type="button"
            onclick="{{ $onclickAdjust ?? ($adjustUrl ? 'window.location.href=' . $adjustUrl : '') }}"
            @if($onclickAdjust)
            class="{{ $padding }} rounded-full inline-flex items-center justify-center text-yellow-600 hover:bg-yellow-50 transition"
            @else
            class="{{ $padding }} rounded-full inline-flex items-center justify-center text-yellow-600 hover:bg-yellow-50 transition"
            @endif
            title="Ajuste Manual">
            <svg class="{{ $iconSize }}" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
            </svg>
        </button>
    @endif

    {{-- Botão Excluir --}}
    @if($showDelete && $deleteUrl && $deletePermission)
        <form action="{{ $deleteUrl }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="{{ $padding }} rounded-full inline-flex items-center justify-center text-red-600 hover:bg-red-50 transition"
                title="Excluir"
                onclick="return confirm('{{ $confirmDeleteMessage }}')">
                <svg class="{{ $iconSize }}" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </button>
        </form>
    @endif
</div>

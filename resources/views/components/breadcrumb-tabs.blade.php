@props(['tabs' => [], 'items' => [], 'activeId' => null])

@php
$tabs = !empty($tabs) ? $tabs : array_map(function($item) {
    return [
        'id' => md5($item['url'] ?? $item['label']),
        'url' => $item['url'] ?? '#',
        'label' => $item['label']
    ];
}, $items);
@endphp

<div class="w-full">
    <nav class="flex items-end gap-1 overflow-x-auto">
        @foreach ($tabs as $tab)
            @php
                $isActive = $activeId ? ($tab['id'] === $activeId) : ($loop->last ?? false);
                $tabId = $tab['id'];
                $tabUrl = $tab['url'];
            @endphp

            <div class="tab-item group relative flex-shrink-0" data-tab-id="{{ $tabId }}" data-tab-url="{{ $tabUrl }}">
                @if ($isActive)
                    <span class="relative bg-white px-4 py-2 text-sm font-semibold text-[#3f9cae] rounded-t-lg border-2 border-[#3f9cae] flex items-center">
                        {{ $tab['label'] }}
                    </span>
                    <button type="button" 
                            onclick="event.stopPropagation(); window.fecharTab('{{ $tabId }}')"
                            class="absolute right-0 top-1/2 -translate-y-1/2 ml-auto w-5 h-5 flex items-center justify-center rounded-full 
                                   text-gray-400 hover:text-red-500 hover:bg-red-100 
                                   transition-colors text-sm leading-none">
                        X
                    </button>
                @else
                    <a href="{{ $tabUrl }}" 
                       onclick="event.preventDefault(); window.ativarTab('{{ $tabId }}')"
                       class="relative bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 rounded-t-lg border border-gray-300 flex items-center hover:bg-gray-300 hover:text-gray-800 transition-all">
                        {{ $tab['label'] }}
                    </a>
                    <button type="button" 
                            onclick="event.preventDefault(); event.stopPropagation(); window.fecharTab('{{ $tabId }}')"
                            class="absolute right-0 top-1/2 -translate-y-1/2 ml-auto w-5 h-5 flex items-center justify-center rounded-full 
                                   text-gray-300 hover:text-red-500 hover:bg-red-100 
                                   transition-colors text-sm leading-none">
                        X
                    </button>
                @endif
            </div>
        @endforeach
    </nav>
</div>

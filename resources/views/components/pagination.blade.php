@props([
    'paginator' => null,
    'items' => null,
    'showInfo' => true,
    'label' => 'itens',
])

@php
    $collection = $paginator ?? $items;
    
    if (!$collection) {
        return;
    }
    
    $currentPage = $collection->currentPage();
    $lastPage = $collection->lastPage();
    $total = $collection->total();
    $firstItem = $collection->firstItem() ?? 0;
    $lastItem = $collection->lastItem() ?? 0;
@endphp

@if($collection->hasPages())
<div class="flex flex-col sm:flex-row justify-between items-center gap-4 mt-4">
    @if($showInfo && $total > 0)
        <div class="text-sm text-gray-600 order-2 sm:order-1">
            Mostrando <strong>{{ $firstItem }}</strong> - <strong>{{ $lastItem }}</strong> de <strong>{{ $total }}</strong> {{ $label }}
        </div>
    @else
        <div></div>
    @endif

    <nav class="flex items-center gap-1 order-1 sm:order-2">
        {{-- Anterior --}}
        @if($collection->onFirstPage())
            <span class="px-3 py-2 rounded-full text-sm min-w-[40px] text-center bg-gray-100 text-gray-400 cursor-not-allowed">
                ←
            </span>
        @else
            <a href="{{ $collection->previousPageUrl() }}" 
                class="px-3 py-2 rounded-full text-sm min-w-[40px] text-center bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                ←
            </a>
        @endif

        {{-- Números das páginas --}}
        @foreach($collection->getUrlRange(1, $lastPage) as $page => $url)
            @if($page == $currentPage)
                <span class="px-3 py-2 rounded-full text-sm min-w-[40px] text-center bg-[#3f9cae] text-white font-medium">
                    {{ $page }}
                </span>
            @elseif($page >= $currentPage - 2 && $page <= $currentPage + 2)
                <a href="{{ $url }}" 
                    class="px-3 py-2 rounded-full text-sm min-w-[40px] text-center bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                    {{ $page }}
                </a>
            @endif
        @endforeach

        {{-- Próximo --}}
        @if($collection->hasMorePages())
            <a href="{{ $collection->nextPageUrl() }}" 
                class="px-3 py-2 rounded-full text-sm min-w-[40px] text-center bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                →
            </a>
        @else
            <span class="px-3 py-2 rounded-full text-sm min-w-[40px] text-center bg-gray-100 text-gray-400 cursor-not-allowed">
                →
            </span>
        @endif
    </nav>
</div>
@endif

@props(['items' => []])

<nav class="flex items-end gap-1">
    @foreach ($items as $index => $item)
        @php
            $isLast = $index === count($items) - 1;
        @endphp

        @if (!$isLast && isset($item['url']))
            <a href="{{ $item['url'] }}"
               class="relative bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 
                      rounded-t-lg border border-gray-300 border-b-0
                      hover:bg-gray-300 hover:text-gray-800 transition-all">
                @if ($index === 0)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                @else
                    {{ $item['label'] }}
                @endif
            </a>
        @else
            <span class="relative bg-white px-4 py-2 text-sm font-semibold text-[#3f9cae] 
                         rounded-t-lg border-2 border-[#3f9cae] border-b-0">
                @if ($index === 0)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                @else
                    {{ $item['label'] }}
                @endif
            </span>
        @endif
    @endforeach
</nav>

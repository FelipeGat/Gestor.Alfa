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
                {{ $item['label'] }}
            </a>
        @else
            <span class="relative bg-white px-4 py-2 text-sm font-semibold text-[#3f9cae]
                         rounded-t-lg border-2 border-[#3f9cae] border-b-0">
                {{ $item['label'] }}
            </span>
        @endif
    @endforeach
</nav>

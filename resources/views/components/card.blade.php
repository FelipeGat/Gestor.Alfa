@props([
    'title' => null,
    'subtitle' => null,
    'href' => null,
    'clickable' => false,
    'padding' => true,
])

@php
    $paddingClass = $padding ? 'p-6' : 'p-0';
@endphp

@if($href || $clickable)
    <a href="{{ $href ?? '#' }}" 
       class="block bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200"
       style="border: 1px solid #3f9cae; border-top-width: 4px;"
       {{ $clickable ? 'style=cursor:pointer' : '' }}
       {{ $attributes->except(['class']) }}>
        @if($title)
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                @if($subtitle)
                    <p class="text-sm text-gray-500 mt-1">{{ $subtitle }}</p>
                @endif
            </div>
        @endif
        <div class="{{ $paddingClass }}">
            {{ $slot }}
        </div>
    </a>
@else
    <div class="bg-white rounded-lg shadow-sm" 
         style="border: 1px solid #3f9cae; border-top-width: 4px;"
         {{ $attributes->except(['class']) }}>
        @if($title)
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                @if($subtitle)
                    <p class="text-sm text-gray-500 mt-1">{{ $subtitle }}</p>
                @endif
            </div>
        @endif
        <div class="{{ $paddingClass }}">
            {{ $slot }}
        </div>
    </div>
@endif

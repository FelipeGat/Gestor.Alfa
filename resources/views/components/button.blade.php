@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'loading' => false,
    'icon' => false,
    'iconLeft' => null,
    'iconRight' => null,
    'disabled' => false,
    'class' => '',
])

@php
    $variants = [
        'primary' => 'bg-[#3f9cae] hover:bg-[#2d7a8a] focus:ring-[#3f9cae] text-white shadow-sm',
        'secondary' => 'bg-gray-500 hover:bg-gray-600 focus:ring-gray-500 text-white shadow-sm',
        'danger' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500 text-white shadow-sm',
        'success' => 'bg-green-500 hover:bg-green-600 focus:ring-green-500 text-white shadow-sm',
        'warning' => 'bg-yellow-500 hover:bg-yellow-600 focus:ring-yellow-500 text-white shadow-sm',
        'info' => 'bg-blue-500 hover:bg-blue-600 focus:ring-blue-500 text-white shadow-sm',
        'light' => 'bg-gray-100 hover:bg-gray-200 focus:ring-gray-400 text-gray-700',
        'outline-primary' => 'bg-transparent border-2 border-[#3f9cae] text-[#3f9cae] hover:bg-[#3f9cae] hover:text-white',
        'outline-danger' => 'bg-transparent border-2 border-red-600 text-red-600 hover:bg-red-600 hover:text-white',
        'outline-success' => 'bg-transparent border-2 border-green-500 text-green-500 hover:bg-green-500 hover:text-white',
    ];
    
    $sizes = [
        'xs' => 'px-2 py-1 text-xs gap-1',
        'sm' => 'px-3 py-1.5 text-sm gap-1.5',
        'md' => 'px-4 py-2 text-sm gap-2',
        'lg' => 'px-5 py-2.5 text-base gap-2',
        'xl' => 'px-6 py-3 text-base gap-2',
    ];
    
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $variantClasses = $variants[$variant] ?? $variants['primary'];
    $sizeClasses = $sizes[$size] ?? $sizes['md'];
    
    $allClasses = trim($baseClasses . ' ' . $variantClasses . ' ' . $sizeClasses . ' ' . $class);
@endphp

@if($href)
    <a href="{{ $href }}" 
       class="{{ $allClasses }}"
       {{ $disabled ? 'aria-disabled=true' : '' }}
       {{ $attributes->except(['class']) }}>
        @if($loading)
            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        @elseif($icon || $iconLeft)
            {{ $iconLeft ?? '' }}
        @endif
        {{ $slot }}
        @if($iconRight && !$icon && !$iconLeft)
            {{ $iconRight }}
        @endif
    </a>
@else
    <button 
        type="{{ $type }}" 
        class="{{ $allClasses }}"
        {{ $loading || $disabled ? 'disabled' : '' }}
        {{ $attributes->except(['class']) }}>
        @if($loading)
            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        @elseif($icon || $iconLeft)
            {{ $iconLeft ?? '' }}
        @endif
        {{ $slot }}
        @if($iconRight && !$icon && !$iconLeft)
            {{ $iconRight }}
        @endif
    </button>
@endif

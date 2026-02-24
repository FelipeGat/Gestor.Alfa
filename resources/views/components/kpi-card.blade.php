@props([
    'title' => '',
    'value' => '',
    'icon' => null,
    'iconPosition' => 'left',
    'color' => 'blue',
    'clickable' => false,
    'href' => null,
    'trend' => null,
    'trendLabel' => null,
])

@php
    $colors = [
        'blue' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-500'],
        'green' => ['bg' => 'bg-green-50', 'text' => 'text-green-600', 'border' => 'border-green-500'],
        'red' => ['bg' => 'bg-red-50', 'text' => 'text-red-600', 'border' => 'border-red-500'],
        'yellow' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-600', 'border' => 'border-yellow-500'],
        'purple' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-600', 'border' => 'border-purple-500'],
        'gray' => ['bg' => 'bg-gray-50', 'text' => 'text-gray-600', 'border' => 'border-gray-500'],
        'teal' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-600', 'border' => 'border-teal-500'],
    ];
    
    $colorData = $colors[$color] ?? $colors['blue'];
    $containerClasses = $clickable || $href ? 'hover:shadow-lg hover:scale-[1.02] transition-all duration-200 cursor-pointer' : 'shadow-sm';
@endphp

@if($clickable || $href)
    <a href="{{ $href ?? '#' }}" 
       class="bg-white rounded-lg p-6 flex flex-col {{ $containerClasses }} border-l-4 {{ $colorData['border'] }} border-t border-r border-b border-gray-200 shadow">
        <div class="flex items-start {{ $iconPosition === 'right' ? 'justify-between' : 'gap-3' }}">
            @if($icon && $iconPosition === 'left')
                <div class="{{ $colorData['bg'] }} p-2 rounded-lg">{{ $icon }}</div>
            @endif
            <div class="{{ $icon && $iconPosition === 'right' ? 'flex-1' : '' }}">
                <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $title }}</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-3xl font-bold {{ $colorData['text'] }} mt-1">{{ $value }}</p>
                    @if($trend)
                        <span class="text-xs font-medium {{ $trend > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $trend > 0 ? '+' : '' }}{{ $trend }}%
                        </span>
                    @endif
                </div>
                @if($trendLabel)
                    <p class="text-xs text-gray-400 mt-1">{{ $trendLabel }}</p>
                @endif
            </div>
            @if($icon && $iconPosition === 'right')
                <div class="{{ $colorData['bg'] }} p-2 rounded-lg">{{ $icon }}</div>
            @endif
        </div>
        {{ $slot }}
    </a>
@else
    <div class="bg-white rounded-lg p-6 flex flex-col {{ $containerClasses }} border-l-4 {{ $colorData['border'] }} border-t border-r border-b border-gray-200 shadow">
        <div class="flex items-start {{ $iconPosition === 'right' ? 'justify-between' : 'gap-3' }}">
            @if($icon && $iconPosition === 'left')
                <div class="{{ $colorData['bg'] }} p-2 rounded-lg">{{ $icon }}</div>
            @endif
            <div class="{{ $icon && $iconPosition === 'right' ? 'flex-1' : '' }}">
                <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $title }}</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-3xl font-bold {{ $colorData['text'] }} mt-1">{{ $value }}</p>
                    @if($trend)
                        <span class="text-xs font-medium {{ $trend > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $trend > 0 ? '+' : '' }}{{ $trend }}%
                        </span>
                    @endif
                </div>
                @if($trendLabel)
                    <p class="text-xs text-gray-400 mt-1">{{ $trendLabel }}</p>
                @endif
            </div>
            @if($icon && $iconPosition === 'right')
                <div class="{{ $colorData['bg'] }} p-2 rounded-lg">{{ $icon }}</div>
            @endif
        </div>
        {{ $slot }}
    </div>
@endif

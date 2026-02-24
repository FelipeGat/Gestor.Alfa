@props([
    'align' => 'left',
    'bold' => false,
    'color' => 'gray-900',
    'nowrap' => false,
])

@php
    $alignClasses = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ];
    
    $classes = 'px-4 py-3 text-sm ' . ($alignClasses[$align] ?? 'text-left') . ' ' . ($bold ? 'font-semibold ' : 'font-medium ') . 'text-' . $color . ($nowrap ? ' whitespace-nowrap' : '');
@endphp

<td class="{{ $classes }}">
    {{ $slot }}
</td>

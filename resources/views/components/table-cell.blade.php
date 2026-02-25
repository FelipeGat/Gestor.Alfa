@props([
    'align' => 'left',
    'bold' => false,
    'type' => 'default',
    'nowrap' => false,
])

@php
    $alignClasses = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ];

    $types = [
        'default' => 'text-gray-900',
        'muted' => 'text-gray-500',
        'primary' => 'text-[#3f9cae]',
        'success' => 'text-green-600',
        'danger' => 'text-red-600',
        'warning' => 'text-yellow-600',
    ];
    
    $typeClasses = $types[$type] ?? $types['default'];
    $classes = 'px-4 py-3 text-sm ' . ($alignClasses[$align] ?? 'text-left') . ' font-semibold ' . $typeClasses . ($nowrap ? ' whitespace-nowrap' : '');
@endphp

<td class="{{ $classes }}">
    {{ $slot }}
</td>

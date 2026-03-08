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
        'default' => 'text-gray-900 dark:text-gray-100',
        'muted' => 'text-gray-500 dark:text-gray-400',
        'primary' => 'text-[#3f9cae]',
        'success' => 'text-green-600 dark:text-green-400',
        'danger' => 'text-red-600 dark:text-red-400',
        'warning' => 'text-yellow-600 dark:text-yellow-400',
    ];

    $typeClasses = $types[$type] ?? $types['default'];
    $classes = 'px-4 py-3 text-sm ' . ($alignClasses[$align] ?? 'text-left') . ' font-medium ' . $typeClasses . ($nowrap ? ' whitespace-nowrap' : '');
@endphp

<td class="{{ $classes }}">
    {{ $slot }}
</td>

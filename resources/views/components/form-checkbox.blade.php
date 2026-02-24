@props([
    'name' => '',
    'label' => '',
    'value' => '',
    'checked' => false,
    'id' => null,
])

@php
    $id = $id ?? $name;
@endphp

<label for="{{ $id }}" class="flex items-center gap-2 cursor-pointer">
    <input 
        type="checkbox" 
        name="{{ $name }}" 
        id="{{ $id }}"
        value="{{ $value }}"
        @checked(old($name, $checked))
        class="rounded-full border-gray-300 text-[#3f9cae] focus:ring-[#3f9cae] shadow-sm"
    >
    @if($label)
        <span class="text-sm text-gray-700">{{ $label }}</span>
    @endif
    {{ $slot }}
</label>

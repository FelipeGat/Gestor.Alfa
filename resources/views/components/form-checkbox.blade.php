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

<div class="flex items-center gap-2">
    <input 
        type="checkbox" 
        name="{{ $name }}" 
        id="{{ $id }}"
        value="{{ $value }}"
        @checked(old($name, $checked))
        class="rounded-full border-gray-300 text-[#3f9cae] focus:ring-[#3f9cae] shadow-sm"
    >
    @if($label)
        <label for="{{ $id }}" class="text-sm text-gray-700 cursor-pointer">
            {{ $label }}
        </label>
    @endif
    @if($slot->isEmpty() === false)
        <label for="{{ $id }}" class="text-sm text-gray-700 cursor-pointer">
            {{ $slot }}
        </label>
    @endif
</div>

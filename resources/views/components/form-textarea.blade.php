@props([
    'name' => '',
    'label' => '',
    'value' => '',
    'placeholder' => '',
    'rows' => 4,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'help' => null,
])

@php
    $hasError = $errors->has($name) || $error;
    $errorMessage = $error ?? ($errors->first($name) ?? '');
@endphp

<div class="flex flex-col">
    @if($label)
        <label for="{{ $name }}" class="text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <textarea 
        name="{{ $name }}" 
        id="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        class="w-full rounded-lg border shadow-sm px-3 py-2 text-sm transition-all duration-200 resize-y
            {{ $hasError 
                ? 'border-red-500 focus:border-red-500 focus:ring-red-500' 
                : 'border-gray-300 focus:border-[#3f9cae] focus:ring-[#3f9cae]/20' }}
            {{ $disabled ? 'bg-gray-100 cursor-not-allowed' : 'bg-white' }}"
    >{{ old($name, $value) }}</textarea>
    
    @if($help && !$hasError)
        <p class="mt-1 text-xs text-gray-500">{{ $help }}</p>
    @endif
    
    @if($hasError)
        <p class="mt-1 text-xs text-red-600">{{ $errorMessage }}</p>
    @endif
</div>

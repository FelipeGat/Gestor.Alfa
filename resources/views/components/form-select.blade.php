@props([
    'name' => '',
    'label' => '',
    'options' => [],
    'selected' => '',
    'placeholder' => 'Selecione uma opção',
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
    
    <select 
        name="{{ $name }}" 
        id="{{ $name }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->filter(fn($value, $key) => $key !== 'label' && $key !== 'options' && $key !== 'selected' && $key !== 'placeholder' && $key !== 'required' && $key !== 'disabled' && $key !== 'error' && $key !== 'help') }}
        class="w-full rounded-lg border shadow-sm px-3 py-2 text-sm transition-all duration-200
            {{ $hasError 
                ? 'border-red-500 focus:border-red-500 focus:ring-red-500' 
                : 'border-gray-300 focus:border-[#3f9cae] focus:ring-[#3f9cae]/20' }}
            {{ $disabled ? 'bg-gray-100 cursor-not-allowed' : 'bg-white' }}"
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        
        @foreach($options as $optionValue => $optionLabel)
            @if(is_array($optionLabel))
                <optgroup label="{{ $optionValue }}">
                    @foreach($optionLabel as $optValue => $optLabel)
                        <option value="{{ $optValue }}" @selected(old($name, $selected) == $optValue)>
                            {{ $optLabel }}
                        </option>
                    @endforeach
                </optgroup>
            @else
                <option value="{{ $optionValue }}" @selected(old($name, $selected) == $optionValue)>
                    {{ $optionLabel }}
                </option>
            @endif
        @endforeach
    </select>
    
    @if($help && !$hasError)
        <p class="mt-1 text-xs text-gray-500">{{ $help }}</p>
    @endif
    
    @if($hasError)
        <p class="mt-1 text-xs text-red-600">{{ $errorMessage }}</p>
    @endif
</div>

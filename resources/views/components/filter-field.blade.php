@props([
    'name' => '',
    'label' => '',
    'type' => 'text',
    'placeholder' => '',
    'colSpan' => 'md:col-span-3',
])

<div class="{{ $colSpan }}">
    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
        </label>
    @endif
    
    @if($type === 'select')
        <select 
            name="{{ $name }}" 
            class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20 px-3 py-2 text-sm bg-white"
        >
            <option value="">{{ $placeholder ?: 'Selecione...' }}</option>
            {{ $slot }}
        </select>
    @else
        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            value="{{ request($name) }}"
            placeholder="{{ $placeholder }}"
            class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20 px-3 py-2 text-sm"
        >
    @endif
</div>

@props([
    'title' => '',
    'icon' => null,
])

<div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
    @if($title)
        <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200 flex items-center gap-2">
            @if($icon)
                {{ $icon }}
            @endif
            {{ $title }}
        </h3>
    @endif
    
    {{ $slot }}
</div>

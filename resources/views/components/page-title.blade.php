@props([
    'title' => '',
    'route' => null,
    'icon' => null,
])

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mt-4 mb-6 bg-white rounded-lg p-4 flex items-center justify-between"
         style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
        <h1 class="text-xl font-bold text-gray-900">
            {{ $title }}
        </h1>
        
        @if($route)
            <x-button href="{{ $route }}" variant="secondary" size="sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar
            </x-button>
        @endif
    </div>
</div>

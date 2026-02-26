@props([
    'name' => 'modal',
    'title' => null,
    'show' => false,
    'maxWidth' => '3xl',
    'steps' => [],
    'currentStep' => 1,
    'showStepIndicator' => true,
])

@php
    $maxWidthMap = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '3xl' => 'sm:max-w-3xl',
        '4xl' => 'sm:max-w-4xl',
        '5xl' => 'sm:max-w-5xl',
    ];
    $maxWidthClass = $maxWidthMap[$maxWidth] ?? $maxWidthMap['3xl'];
    
    $totalSteps = count($steps);
    $isLastStep = $currentStep >= $totalSteps;
    $isFirstStep = $currentStep <= 1;
    
    $currentStepData = $steps[$currentStep - 1] ?? ['label' => 'Step ' . $currentStep, 'title' => '', 'description' => ''];
@endphp

<div
    x-data="{ 
        show: @js($show),
        step: @js($currentStep),
        totalSteps: @js($totalSteps),
    }"
    x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-y-hidden');
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    })"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
>
    <div
        x-show="show"
        x-on:click="show = false"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div
        x-show="show"
        class="mb-6 bg-white rounded-lg shadow-xl transform transition-all sm:w-full {{ $maxWidthClass }} sm:mx-auto"
        style="border: 1px solid #3f9cae; border-top-width: 4px; max-height: 85vh; display: flex; flex-direction: column;"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        @if($title || $showStepIndicator)
            <div class="px-6 py-4 border-b border-gray-200 flex-shrink-0" style="background-color: rgba(63, 156, 174, 0.05);">
                <div class="flex items-center justify-between">
                    @if($title)
                        <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                    @endif
                    <button @click="show = false" class="text-gray-400 hover:text-gray-600 ml-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                @if($showStepIndicator && count($steps) > 0)
                    <div class="mt-4">
                        <x-step-indicator 
                            :steps="$steps" 
                            :current-step="$currentStep"
                            :show-description="true"
                        />
                        
                        @if($currentStepData['title'] ?? false)
                            <div class="text-center mt-2">
                                <h4 class="text-base font-semibold text-gray-800">{{ $currentStepData['title'] }}</h4>
                                @if($currentStepData['description'] ?? false)
                                    <p class="text-sm text-gray-500">{{ $currentStepData['description'] }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <div class="p-6 overflow-y-auto flex-1" style="max-height: calc(85vh - 200px);">
            {{ $slot }}
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center flex-shrink-0">
            <div>
                @if(!$isFirstStep)
                    <button 
                        type="button" 
                        x-show="step > 1"
                        @click="step--; $dispatch('step-changed', step)"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium text-sm transition-colors"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Anterior
                    </button>
                @endif
            </div>
            <div class="flex gap-3">
                <button 
                    type="button" 
                    @click="show = false"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium text-sm transition-colors"
                >
                    Cancelar
                </button>
                
                @if(!$isLastStep)
                    <button 
                        type="button"
                        x-show="step < totalSteps"
                        @click="step++; $dispatch('step-changed', step)"
                        class="inline-flex items-center px-4 py-2 bg-[#3f9cae] text-white rounded-lg hover:bg-[#2d7a8a] font-medium text-sm transition-colors"
                    >
                        Próximo
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                @else
                    {{ $submitButton ?? '' }}
                @endif
            </div>
        </div>
    </div>
</div>

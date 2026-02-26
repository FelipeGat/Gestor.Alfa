@props([
    'steps' => [],
    'currentStep' => 1,
    'showDescription' => false,
    'xModel' => null,
])

@php
    $totalSteps = count($steps);
    
    $circleClass = function($stepNumber) use ($currentStep) {
        if ($stepNumber < $currentStep) {
            return 'bg-green-500 border-green-500 text-white';
        } elseif ($stepNumber === $currentStep) {
            return 'bg-[#3f9cae] border-[#3f9cae] text-white ring-4 ring-[#3f9cae]/20';
        }
        return 'bg-white border-gray-300 text-gray-400';
    };
    
    $labelClass = function($stepNumber) use ($currentStep) {
        if ($stepNumber < $currentStep) {
            return 'text-green-600';
        } elseif ($stepNumber === $currentStep) {
            return 'text-[#3f9cae]';
        }
        return 'text-gray-400';
    };
@endphp

<div class="w-full mb-8">
    <div class="flex items-center justify-between">
        @foreach($steps as $index => $step)
            @php
                $stepNumber = $index + 1;
                $isCompleted = $stepNumber < $currentStep;
                $isCurrent = $stepNumber === $currentStep;
                
                $circleClasses = $circleClass($stepNumber);
                $labelClasses = $labelClass($stepNumber);
            @endphp
            
            <div class="flex items-center {{ $index < $totalSteps - 1 ? 'flex-1' : '' }}">
                <div class="flex flex-col items-center">
                    @if($xModel)
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all duration-300"
                        :class="{
                            'bg-green-500 border-green-500 text-white': {{ $xModel }} > {{ $stepNumber }},
                            'bg-[#3f9cae] border-[#3f9cae] text-white ring-4 ring-[#3f9cae]/20': {{ $xModel }} === {{ $stepNumber }},
                            'bg-white border-gray-300 text-gray-400': {{ $xModel }} < {{ $stepNumber }}
                        }">
                        <template x-if="{{ $xModel }} > {{ $stepNumber }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </template>
                        <template x-if="{{ $xModel }} <= {{ $stepNumber }}">
                            <span x-text="{{ $stepNumber }}"></span>
                        </template>
                    </div>
                    <span class="text-xs mt-2 font-medium"
                        :class="{
                            'text-green-600': {{ $xModel }} > {{ $stepNumber }},
                            'text-[#3f9cae]': {{ $xModel }} === {{ $stepNumber }},
                            'text-gray-400': {{ $xModel }} < {{ $stepNumber }}
                        }">
                        {{ $step['label'] ?? 'Step ' . $stepNumber }}
                    </span>
                    @else
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all duration-300 {{ $circleClasses }}">
                        @if($isCompleted)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        @else
                            {{ $stepNumber }}
                        @endif
                    </div>
                    
                    <div class="mt-2 text-center">
                        <span class="text-xs font-medium {{ $labelClasses }} block">
                            {{ $step['label'] ?? 'Step ' . $stepNumber }}
                        </span>
                        @if($showDescription && isset($step['title']))
                            <span class="text-xs text-gray-500 block mt-0.5 max-w-[80px]">
                                {{ $step['title'] }}
                            </span>
                        @endif
                    </div>
                    @endif
                </div>
                
                @if($index < $totalSteps - 1)
                    <div class="flex-1 h-1 mx-2 transition-colors duration-300"
                        :class="{
                            'bg-green-500': {{ $xModel ? $xModel : $currentStep }} > {{ $stepNumber }},
                            'bg-gray-200': {{ $xModel ? $xModel : $currentStep }} <= {{ $stepNumber }}
                        }">
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

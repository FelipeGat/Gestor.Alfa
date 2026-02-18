@props(['status' => ''])

@php
    $statusConfig = [
        'pago' => ['label' => 'Pago', 'bg' => '#dcfce7', 'color' => '#166534', 'icon' => 'check'],
        'vencido' => ['label' => 'Vencido', 'bg' => '#fee2e2', 'color' => '#991b1b', 'icon' => 'x'],
        'vence_hoje' => ['label' => 'Vence Hoje', 'bg' => '#ffedd5', 'color' => '#9a3412', 'icon' => 'clock'],
        'a_vencer' => ['label' => 'A Vencer', 'bg' => '#dbeafe', 'color' => '#1e40af', 'icon' => 'clock'],
        'em_aberto' => ['label' => 'Em Aberto', 'bg' => '#f3f4f6', 'color' => '#374151', 'icon' => 'clock'],
    ];

    $config = $statusConfig[$status] ?? $statusConfig['em_aberto'];
@endphp

<span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-semibold" style="width: 130px; justify-content: center; background-color: {{ $config['bg'] }}; color: {{ $config['color'] }};">
    @if($config['icon'] === 'check')
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
    @else
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
        </svg>
    @endif
    {{ $config['label'] }}
</span>

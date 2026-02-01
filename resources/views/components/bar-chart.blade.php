<div class="bg-white shadow rounded-xl p-6">
    <div class="text-xs text-gray-500 mb-2">Gráfico de Barras (visual)</div>
    <div class="flex flex-col gap-2">
        @foreach($labels as $i => $label)
            @php
                $barColor = 'bg-blue-500';
                if (stripos($label, 'Custo Total') !== false) {
                    $barColor = 'bg-red-500';
                } elseif (stripos($label, 'Receita Recebida') !== false) {
                    $barColor = 'bg-emerald-500';
                }
            @endphp
            <div class="flex items-center gap-2">
                <span class="w-32 font-semibold">{{ $label }}</span>
                <div class="flex-1 h-6 bg-blue-100 rounded-xl relative">
                    <div class="h-6 rounded-xl {{ $barColor }}" style="width: {{ isset($data[$i]) && max($data) > 0 ? ($data[$i]/max($data))*100 : 0 }}%"></div>
                </div>
                <span class="font-bold text-gray-800">R$ {{ isset($data[$i]) ? number_format($data[$i],2,',','.') : '-' }}</span>
            </div>
        @endforeach
    </div>
</div>

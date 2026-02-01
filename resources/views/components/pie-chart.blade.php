<div class="bg-white shadow rounded-xl p-6">
    <div class="text-xs text-gray-500 mb-2">Gráfico de Pizza (visual)</div>
    <div class="grid grid-cols-1 gap-2">
        @foreach($labels as $i => $label)
            <div class="flex items-center justify-between py-2 px-3 rounded-xl bg-gray-50">
                <span class="font-semibold">{{ $label }}</span>
                <span class="font-bold text-gray-800">R$ {{ isset($data[$i]) ? number_format($data[$i],2,',','.') : '-' }}</span>
            </div>
        @endforeach
    </div>
</div>

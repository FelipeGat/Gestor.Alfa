<div class="bg-white shadow rounded-xl p-6">
    <div class="text-xs text-gray-500 mb-2">Gráfico de Linha (visual)</div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-xs">
            <thead>
                <tr class="bg-gray-50">
                    <th class="py-2 px-3 text-left">Data</th>
                    @foreach($datasets as $ds)
                        <th class="py-2 px-3 text-left">{{ $ds['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($labels as $i => $label)
                    <tr>
                        <td class="py-2 px-3 font-semibold">{{ $label }}</td>
                        @foreach($datasets as $ds)
                            <td class="py-2 px-3">{{ isset($ds['data'][$i]) ? number_format($ds['data'][$i],2,',','.') : '-' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

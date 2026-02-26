@props([
    'columns' => [],
    'data' => [],
    'emptyMessage' => 'Nenhum registro encontrado',
    'emptyIcon' => true,
    'striped' => false,
    'hover' => true,
    'responsive' => true,
    'actions' => false,
    'actionsLabel' => 'Ações',
    'title' => null,
    'class' => '',
])

<div class="bg-white rounded-lg overflow-hidden {{ $class }}" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
    @if($title)
        <div class="px-4 py-3 border-b border-gray-200" style="background-color: rgba(63, 156, 174, 0.05);">
            <h3 class="text-sm font-semibold text-gray-700">{{ $title }}</h3>
        </div>
    @endif

    @if($responsive)
    <div class="overflow-x-auto">
    @endif
        <table class="w-full table-auto">
            <thead style="background-color: rgba(63, 156, 174, 0.05); border-bottom: 1px solid #3f9cae;">
                <tr>
                    @foreach($columns as $column)
                        <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700">
                            {{ $column['label'] ?? '' }}
                        </th>
                    @endforeach
                    @if($actions)
                        <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700">Ações</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @if($data->count() > 0)
                    {{ $slot }}
                @else
                    <tr>
                        <td colspan="{{ count($columns) + ($actions ? 1 : 0) }}" class="px-4 py-12 text-center">
                            @if($emptyIcon)
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            @endif
                            <h3 class="text-lg font-medium text-gray-900">{{ $emptyMessage }}</h3>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    @if($responsive)
    </div>
    @endif
</div>

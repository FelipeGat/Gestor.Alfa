@php
    $inputId = $inputId ?? ('portal-live-filter-'.uniqid());
    $tableId = $tableId ?? '';
    $placeholder = $placeholder ?? 'Digite para filtrar';
    $emptyId = $emptyId ?? ('portal-live-empty-'.uniqid());
    $mode = $mode ?? 'client';
    $action = $action ?? url()->current();
    $queryParam = $queryParam ?? 'q';
@endphp

<div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
    <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 mb-1">Buscar na lista</label>
    @if($mode === 'server')
    <form id="{{ $inputId }}Form" method="GET" action="{{ $action }}">
        @foreach(request()->except($queryParam, 'page') as $key => $value)
            @if(is_array($value))
                @foreach($value as $item)
                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
    @endif
    <input
        id="{{ $inputId }}"
        name="{{ $queryParam }}"
        type="text"
        class="w-full rounded border-gray-300"
        placeholder="{{ $placeholder }}"
        value="{{ request($queryParam, '') }}"
        autocomplete="off"
    >
    @if($mode === 'server')
    </form>
    @endif
    <p id="{{ $emptyId }}" class="hidden text-xs text-gray-500 mt-2"></p>
</div>

<script>
    (function () {
        const input = document.getElementById(@json($inputId));
        const mode = @json($mode);

        if (!input) {
            return;
        }

        if (mode === 'server') {
            const form = document.getElementById(@json($inputId.'Form'));
            if (!form) {
                return;
            }

            let timer = null;
            input.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(function () {
                    form.submit();
                }, 350);
            });

            return;
        }

        const table = document.getElementById(@json($tableId));
        const emptyMessage = document.getElementById(@json($emptyId));

        if (!table) {
            return;
        }

        const body = table.querySelector('tbody');
        if (!body) {
            return;
        }

        const rows = Array.from(body.querySelectorAll('tr'));
        const emptyRow = rows.find((row) => row.textContent.toLowerCase().includes('nenhum') || row.textContent.toLowerCase().includes('nenhuma'));

        const filterRows = () => {
            const query = input.value.trim().toLowerCase();
            let visible = 0;

            rows.forEach((row) => {
                if (row === emptyRow) {
                    return;
                }

                const text = row.textContent.toLowerCase();
                const match = query === '' || text.includes(query);
                row.style.display = match ? '' : 'none';

                if (match) {
                    visible++;
                }
            });

            if (emptyRow) {
                emptyRow.style.display = query === '' ? '' : 'none';
            }

            if (!emptyMessage) {
                return;
            }

            if (query !== '' && visible === 0) {
                emptyMessage.textContent = `Nenhum resultado para "${input.value}".`;
                emptyMessage.classList.remove('hidden');
            } else {
                emptyMessage.textContent = '';
                emptyMessage.classList.add('hidden');
            }
        };

        input.addEventListener('input', filterRows);
    })();
</script>

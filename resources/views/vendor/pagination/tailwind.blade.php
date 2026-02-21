@if ($paginator->hasPages())
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-top: 1rem;">
        <div class="pagination-info">
            Mostrando <strong>{{ $paginator->count() }}</strong> de
            <strong>{{ $paginator->total() }}</strong>
            resultados
        </div>

        <div class="pagination-links" style="display: flex; gap: 0.5rem; align-items: center;">
            {{-- Link Anterior --}}
            @if ($paginator->onFirstPage())
                <span class="pagination-link disabled" style="padding: 0.5rem 1rem; border-radius: 9999px; min-width: 40px; text-align: center; background: #f3f4f6; color: #9ca3af; cursor: not-allowed; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-size: 0.875rem;">← Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-link" style="padding: 0.5rem 1rem; border-radius: 9999px; min-width: 40px; text-align: center; background: white; border: 1px solid #e5e7eb; color: #374151; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-size: 0.875rem; transition: all 0.2s;" onmouseover="this.style.background='#f3f4f6'; this.style.borderColor='#3f9cae'" onmouseout="this.style.background='white'; this.style.borderColor='#e5e7eb'">← Anterior</a>
            @endif

            {{-- Links de Página --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span style="padding: 0.5rem 1rem; border-radius: 9999px; min-width: 40px; text-align: center; background: #f3f4f6; color: #9ca3af; cursor: default; display: inline-flex; align-items: center; justify-content: center; font-size: 0.875rem;">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-link active" style="padding: 0.5rem 1rem; border-radius: 9999px; min-width: 40px; text-align: center; background: #3f9cae; border: 1px solid #3f9cae; color: white; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; font-size: 0.875rem;">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination-link" style="padding: 0.5rem 1rem; border-radius: 9999px; min-width: 40px; text-align: center; background: white; border: 1px solid #e5e7eb; color: #374151; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-size: 0.875rem; transition: all 0.2s;" onmouseover="this.style.background='#f3f4f6'; this.style.borderColor='#3f9cae'" onmouseout="this.style.background='white'; this.style.borderColor='#e5e7eb'">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Link Próximo --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-link" style="padding: 0.5rem 1rem; border-radius: 9999px; min-width: 40px; text-align: center; background: white; border: 1px solid #e5e7eb; color: #374151; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-size: 0.875rem; transition: all 0.2s;" onmouseover="this.style.background='#f3f4f6'; this.style.borderColor='#3f9cae'" onmouseout="this.style.background='white'; this.style.borderColor='#e5e7eb'">Próximo →</a>
            @else
                <span class="pagination-link disabled" style="padding: 0.5rem 1rem; border-radius: 9999px; min-width: 40px; text-align: center; background: #f3f4f6; color: #9ca3af; cursor: not-allowed; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-size: 0.875rem;">Próximo →</span>
            @endif
        </div>
    </div>
@endif

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Documentos
        </h2>
    </x-slot>

    <style>
        .documentos-container {
            padding: 2rem 1rem;
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }

        .placeholder-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
        }

        .placeholder-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .placeholder-text {
            color: #6b7280;
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #3f9cae 0%, #327d8c 100%);
            color: white;
            border-radius: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .back-btn:hover {
            transform: scale(1.05);
        }

        .future-features {
            margin-top: 3rem;
            padding: 1.5rem;
            background: #f9fafb;
            border-radius: 0.75rem;
            text-align: left;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: white;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .feature-icon {
            font-size: 1.5rem;
        }

        .feature-text {
            color: #374151;
            font-size: 0.875rem;
        }
    </style>

    <div class="documentos-container">
        <div class="placeholder-icon">📁</div>
        <div class="placeholder-title">Em Desenvolvimento</div>
        <div class="placeholder-text">
            A área de documentos está sendo preparada e estará disponível em breve.
        </div>

        <a href="{{ route('portal-funcionario.index') }}" class="back-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Voltar ao Início
        </a>

        <div class="future-features">
            <div style="font-weight: 700; margin-bottom: 1rem; color: #1f2937;">
                Recursos Futuros:
            </div>

            <div class="feature-item">
                <div class="feature-icon">📄</div>
                <div class="feature-text">Manuais técnicos e procedimentos</div>
            </div>

            <div class="feature-item">
                <div class="feature-icon">📋</div>
                <div class="feature-text">Formulários e checklists</div>
            </div>

            <div class="feature-item">
                <div class="feature-icon">🔧</div>
                <div class="feature-text">Guias de instalação</div>
            </div>

            <div class="feature-item">
                <div class="feature-icon">📊</div>
                <div class="feature-text">Relatórios e documentação técnica</div>
            </div>

            <div class="feature-item">
                <div class="feature-icon">🎓</div>
                <div class="feature-text">Material de treinamento</div>
            </div>
        </div>
    </div>
</x-app-layout>

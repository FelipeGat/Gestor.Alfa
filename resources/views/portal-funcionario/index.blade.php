<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🛠️ Portal do Funcionário
        </h2>
    </x-slot>

    <div class="portal-home">
        <style>
            .portal-home {
                min-height: calc(100vh - 64px);
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 2rem 1rem;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }

            .portal-welcome {
                text-align: center;
                color: white;
                margin-bottom: 3rem;
            }

            .portal-welcome h1 {
                font-size: 2rem;
                font-weight: 700;
                margin-bottom: 0.5rem;
            }

            .portal-welcome p {
                font-size: 1.125rem;
                opacity: 0.9;
            }

            .portal-stats {
                display: flex;
                gap: 1rem;
                margin-bottom: 3rem;
                flex-wrap: wrap;
                justify-content: center;
            }

            .stat-badge {
                background: rgba(255, 255, 255, 0.2);
                backdrop-filter: blur(10px);
                padding: 0.75rem 1.5rem;
                border-radius: 50px;
                color: white;
                font-weight: 600;
                font-size: 0.875rem;
                border: 1px solid rgba(255, 255, 255, 0.3);
            }

            .portal-buttons {
                display: grid;
                grid-template-columns: 1fr;
                gap: 1.5rem;
                width: 100%;
                max-width: 400px;
            }

            .portal-btn {
                background: white;
                border-radius: 1.5rem;
                padding: 2rem 1.5rem;
                text-align: center;
                text-decoration: none;
                color: #1f2937;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                transition: all 0.3s ease;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.75rem;
                cursor: pointer;
            }

            .portal-btn:hover {
                transform: translateY(-8px);
                box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            }

            .portal-btn:active {
                transform: translateY(-4px);
            }

            .portal-btn-icon {
                font-size: 3.5rem;
                line-height: 1;
            }

            .portal-btn-title {
                font-size: 1.5rem;
                font-weight: 700;
                margin: 0;
            }

            .portal-btn-desc {
                font-size: 0.875rem;
                color: #6b7280;
                margin: 0;
            }

            .portal-btn.chamados {
                border-top: 4px solid #3b82f6;
            }

            .portal-btn.agenda {
                border-top: 4px solid #10b981;
            }

            .portal-btn.documentos {
                border-top: 4px solid #f59e0b;
            }

            @media (min-width: 640px) {
                .portal-buttons {
                    max-width: 500px;
                }

                .portal-welcome h1 {
                    font-size: 2.5rem;
                }
            }
        </style>

        <div class="portal-welcome">
            <h1>Olá, {{ Auth::user()->name }}!</h1>
            <p>Selecione uma opção abaixo</p>
        </div>

        <div class="portal-stats">
            <div class="stat-badge">
                📋 {{ $totalAbertos }} Chamados Abertos
            </div>
            <div class="stat-badge">
                ✅ {{ $totalFinalizados }} Finalizados
            </div>
        </div>

        <div class="portal-buttons">
            <a href="{{ route('portal-funcionario.chamados') }}" class="portal-btn chamados">
                <div class="portal-btn-icon">📋</div>
                <h3 class="portal-btn-title">Painel de Chamados</h3>
                <p class="portal-btn-desc">Gerencie seus atendimentos</p>
            </a>

            <a href="{{ route('portal-funcionario.agenda') }}" class="portal-btn agenda">
                <div class="portal-btn-icon">📅</div>
                <h3 class="portal-btn-title">Agenda Técnica</h3>
                <p class="portal-btn-desc">Visualize sua agenda</p>
            </a>

            <a href="{{ route('portal-funcionario.documentos') }}" class="portal-btn documentos">
                <div class="portal-btn-icon">📁</div>
                <h3 class="portal-btn-title">Documentos</h3>
                <p class="portal-btn-desc">Em breve</p>
            </a>
        </div>
    </div>
</x-app-layout>

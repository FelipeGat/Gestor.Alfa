<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Bem-vindo ao seu portal
        </h2>
    </x-slot>

    <div class="portal-home">
        <style>
            .portal-home {
                min-height: calc(100dvh - 48px);
                background: linear-gradient(180deg, #f3f4f6 0%, #e5f2f5 100%);
                padding: 2rem 1rem;
                padding-bottom: calc(2rem + env(safe-area-inset-bottom));
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }

            .portal-welcome {
                text-align: center;
                color: #1f2937;
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
                background: #ffffff;
                padding: 0.75rem 1.5rem;
                border-radius: 50px;
                color: #2f7c8a;
                font-weight: 600;
                font-size: 0.875rem;
                border: 1px solid #b7dbe1;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
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
                min-height: 130px;
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
                border-top: 4px solid #3f9cae;
            }

            .portal-btn.ponto {
                border-top: 4px solid #3f9cae;
            }

            .portal-btn.agenda {
                border-top: 4px solid #3f9cae;
            }

            .portal-btn.documentos {
                border-top: 4px solid #3f9cae;
            }

            @media (min-width: 640px) {
                .portal-buttons {
                    max-width: 500px;
                }

                .portal-welcome h1 {
                    font-size: 2.5rem;
                }
            }

            @media (max-width: 640px) {
                .portal-home {
                    justify-content: flex-start;
                    padding-top: 1.25rem;
                }

                .portal-welcome {
                    margin-bottom: 1.5rem;
                }

                .portal-welcome h1 {
                    font-size: 1.5rem;
                }

                .portal-welcome p {
                    font-size: 0.95rem;
                }

                .portal-stats {
                    margin-bottom: 1.5rem;
                    width: 100%;
                    gap: 0.5rem;
                }

                .stat-badge {
                    width: 100%;
                    text-align: center;
                    padding: 0.7rem 0.9rem;
                }

                .portal-buttons {
                    max-width: 100%;
                    gap: 1rem;
                }

                .portal-btn {
                    border-radius: 1rem;
                    padding: 1.1rem 1rem;
                    min-height: 96px;
                    gap: 0.45rem;
                }

                .portal-btn-icon {
                    width: 2rem !important;
                    height: 2rem !important;
                }

                .portal-btn-title {
                    font-size: 1.05rem;
                }

                .portal-btn-desc {
                    font-size: 0.8rem;
                }
            }
        </style>

        <div class="portal-welcome">
            <h1>Olá, {{ Auth::user()->name }}!</h1>
            <p>Selecione uma opção abaixo</p>
        </div>

        <div class="portal-stats">
            <div class="stat-badge">
                <svg style="width: 1.25rem; height: 1.25rem; display: inline-block; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                </svg>
                {{ $totalAbertos }} Chamados Abertos
            </div>
            @if($temPausado && $totalEmAtendimento > 0)
            <div class="stat-badge">
                <svg style="width: 1.25rem; height: 1.25rem; display: inline-block; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                </svg>
                {{ $totalEmAtendimento }} Em Atendimento
            </div>
            @endif
            <div class="stat-badge">
                <svg style="width: 1.25rem; height: 1.25rem; display: inline-block; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                {{ $totalFinalizados }} Finalizados
            </div>
        </div>

        <div class="portal-buttons">
            <button type="button" class="portal-btn ponto">
                <svg class="portal-btn-icon" style="width: 3.5rem; height: 3.5rem;" fill="none" stroke="#3f9cae" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="portal-btn-title">Registro de Ponto</h3>
                <p class="portal-btn-desc">Em breve</p>
            </button>

            <a href="{{ route('portal-funcionario.chamados') }}" class="portal-btn chamados">
                <svg class="portal-btn-icon" style="width: 3.5rem; height: 3.5rem;" fill="none" stroke="#3f9cae" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <h3 class="portal-btn-title">Painel de Chamados</h3>
                <p class="portal-btn-desc">Gerencie seus atendimentos</p>
            </a>

            <a href="{{ route('portal-funcionario.agenda') }}" class="portal-btn agenda">
                <svg class="portal-btn-icon" style="width: 3.5rem; height: 3.5rem;" fill="none" stroke="#3f9cae" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="portal-btn-title">Agenda Técnica</h3>
                <p class="portal-btn-desc">Visualize sua agenda</p>
            </a>

            <a href="{{ route('portal-funcionario.documentos') }}" class="portal-btn documentos">
                <svg class="portal-btn-icon" style="width: 3.5rem; height: 3.5rem;" fill="none" stroke="#3f9cae" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="portal-btn-title">Documentos</h3>
                <p class="portal-btn-desc">Em breve</p>
            </a>
        </div>
    </div>
</x-app-layout>

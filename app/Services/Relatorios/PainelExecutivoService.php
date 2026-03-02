<?php

namespace App\Services\Relatorios;

class PainelExecutivoService extends BaseRelatorioService
{
    public function __construct(
        private readonly RelatorioFinanceiroService $financeiroService,
        private readonly RelatorioTecnicoService $tecnicoService,
        private readonly RelatorioComercialService $comercialService,
        private readonly RelatorioRHService $rhService,
    ) {}

    public function gerar(array $filtros): array
    {
        $financeiro = $this->financeiroService->gerar($filtros);
        $tecnico = $this->tecnicoService->gerar($filtros);
        $comercial = $this->comercialService->gerar($filtros);
        $rh = $this->rhService->gerar($filtros);

        $lucroAtual = (float) ($financeiro['lucro_liquido'] ?? 0);
        $lucroAnterior = (float) ($financeiro['lucro_liquido_anterior'] ?? 0);
        $crescimentoLucro = abs($lucroAnterior) < 0.01
            ? 0.0
            : $this->percentual($lucroAtual - $lucroAnterior, $lucroAnterior);

        return [
            'periodo' => $financeiro['periodo'] ?? [
                'data_inicio' => $filtros['data_inicio'] ?? null,
                'data_fim' => $filtros['data_fim'] ?? null,
            ],
            'receita_total' => $this->f($financeiro['receita_total'] ?? 0),
            'despesa_total' => $this->f($financeiro['despesa_total'] ?? 0),
            'lucro' => $this->f($lucroAtual),
            'crescimento_vs_mes_anterior' => $this->f($crescimentoLucro),
            'conversao_comercial' => $this->f($comercial['taxa_conversao'] ?? 0),
            'receita_por_tecnico' => $tecnico['receita_por_tecnico'] ?? [],
            'indice_absenteismo' => $this->f($rh['indice_absenteismo'] ?? 0),
            'consolidado' => [
                'financeiro' => $financeiro,
                'tecnico' => $tecnico,
                'comercial' => $comercial,
                'rh' => $rh,
            ],
        ];
    }
}

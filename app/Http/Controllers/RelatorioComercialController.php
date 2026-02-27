<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\User;
use App\Services\Relatorio\RelatorioComercialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RelatorioComercialController extends Controller
{
    public function __construct(
        private RelatorioComercialService $service
    ) {}

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(! $user->isAdminPanel() && $user->tipo !== 'comercial', 403, 'Acesso não autorizado');

        $filtros = [
            'data_inicio' => $request->input('data_inicio', now()->startOfMonth()->format('Y-m-d')),
            'data_fim' => $request->input('data_fim', now()->endOfMonth()->format('Y-m-d')),
            'empresa_id' => $request->input('empresa_id'),
            'vendedor_id' => $request->input('vendedor_id'),
            'tipo_servico' => $request->input('tipo_servico'),
        ];

        $dados = $this->service->gerar($filtros);

        $empresas = Empresa::query()
            ->select('id', 'nome_fantasia')
            ->orderBy('nome_fantasia')
            ->get();

        $vendedores = User::query()
            ->select('id', 'name')
            ->whereNotNull('funcionario_id')
            ->orderBy('name')
            ->get();

        return view('relatorios.comercial', compact('dados', 'filtros', 'empresas', 'vendedores'));
    }
}

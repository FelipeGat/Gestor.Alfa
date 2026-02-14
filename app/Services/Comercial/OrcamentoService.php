<?php

namespace App\Services\Comercial;

use App\Models\Orcamento;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class OrcamentoService
{
    public function listar(Request $request): LengthAwarePaginator
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $sortable = ['numero_orcamento', 'status', 'valor_total', 'created_at'];
        $sort = in_array($request->get('sort'), $sortable) ? $request->get('sort') : 'created_at';
        $direction = $request->get('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $query = Orcamento::with([
            'empresa:id,nome_fantasia',
            'cliente:id,nome,nome_fantasia,razao_social',
            'preCliente:id,nome_fantasia,razao_social'
        ]);

        if ($user->tipo === 'comercial') {
            $empresaIds = $user->empresas->pluck('id');
            if ($empresaIds->isEmpty()) {
                return new LengthAwarePaginator([], 0, 15);
            }
            $query->whereIn('empresa_id', $empresaIds);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_orcamento', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('cliente', function ($qc) use ($search) {
                        $qc->where('nome', 'like', "%{$search}%");
                    })
                    ->orWhereHas('preCliente', function ($qp) use ($search) {
                        $qp->where('nome_fantasia', 'like', "%{$search}%")
                            ->orWhere('razao_social', 'like', "%{$search}%");
                    })
                    ->orWhereHas('empresa', function ($qe) use ($search) {
                        $qe->where('nome_fantasia', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->whereIn('status', (array) $request->status);
        }

        if ($request->filled('empresa_id')) {
            $query->whereIn('empresa_id', (array) $request->empresa_id);
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        return $query->orderBy($sort, $direction)->paginate(15)->withQueryString();
    }

    public function buscarPorId(int $id): ?Orcamento
    {
        return Orcamento::with([
            'empresa',
            'cliente',
            'preCliente',
            'itens',
            'itens.itemComercial',
            'itens.medida',
            'cobrancas',
        ])->find($id);
    }

    public function criar(array $data): Orcamento
    {
        return Orcamento::create($data);
    }

    public function atualizar(int $id, array $data): ?Orcamento
    {
        $orcamento = Orcamento::find($id);
        if ($orcamento) {
            $orcamento->update($data);
        }
        return $orcamento;
    }

    public function excluir(int $id): bool
    {
        $orcamento = Orcamento::find($id);
        return $orcamento ? $orcamento->delete() : false;
    }

    public function atualizarStatus(Orcamento $orcamento, string $status): void
    {
        $orcamento->update(['status' => $status]);
    }

    public function gerarNumero(int $empresaId): string
    {
        $empresa = Empresa::find($empresaId);
        $ano = date('Y');
        $sequencial = Orcamento::where('empresa_id', $empresaId)
            ->whereYear('created_at', $ano)
            ->count() + 1;

        return sprintf('%s-%s-%04d', $empresa?->sigla ?? 'ORC', $ano, $sequencial);
    }
}

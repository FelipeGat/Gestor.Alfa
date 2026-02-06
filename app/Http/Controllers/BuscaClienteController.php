<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\PreCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BuscaClienteController extends Controller
{
    public function buscar(Request $request)
    {
        $user = $request->user();
        // Permitir admin, administrativo e tecnico 
        $permitidos = ['admin', 'administrativo', 'comercial', 'tecnico'];
        if (!$user || !$user->perfis()->whereIn('slug', $permitidos)->exists()) {
            abort(403);
        }
        try {
            $q = trim((string) $request->query('q'));

            // ================= CLIENTES =================
            $clientes = Cliente::query()
                ->when($q !== '', function ($query) use ($q) {
                    $query->where(function ($sub) use ($q) {
                        $sub->where('cpf_cnpj', 'like', "%{$q}%")
                            ->orWhere('nome_fantasia', 'like', "%{$q}%")
                            ->orWhere('nome', 'like', "%{$q}%")
                            ->orWhere('razao_social', 'like', "%{$q}%");
                    });
                })
                ->limit(10)
                ->get()
                ->map(fn($cliente) => [
                    'id'            => $cliente->id,
                    'tipo'          => 'cliente',
                    'cpf_cnpj'      => $cliente->cpf_cnpj,
                    'nome_fantasia' => $cliente->nome_fantasia,
                    'razao_social'  => $cliente->razao_social,
                ]);

            // ================= PRÉ-CLIENTES =================
            $preClientes = PreCliente::query()
                ->when($q !== '', function ($query) use ($q) {
                    $query->where(function ($sub) use ($q) {
                        $sub->where('cpf_cnpj', 'like', "%{$q}%")
                            ->orWhere('nome_fantasia', 'like', "%{$q}%")
                            ->orWhere('razao_social', 'like', "%{$q}%");
                    });
                })
                ->limit(10)
                ->get()
                ->map(fn($pre) => [
                    'id'            => $pre->id,
                    'tipo'          => 'pre_cliente',
                    'cpf_cnpj'      => $pre->cpf_cnpj,
                    'nome_fantasia' => $pre->nome_fantasia,
                    'razao_social'  => $pre->razao_social,
                ]);

            // ================= MERGE + REMOVER DUPLICADOS =================
            $resultado = collect()
                ->merge($clientes)
                ->merge($preClientes)
                ->unique('cpf_cnpj')
                ->values();

            return response()->json($resultado);
        } catch (\Throwable $e) {
            Log::error('Erro na busca de clientes', [
                'error' => $e->getMessage()
            ]);

            return response()->json([], 500);
        }
    }
}

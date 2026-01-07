<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Boleto;
use Illuminate\Support\Facades\DB;

class PortalController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user || $user->tipo !== 'cliente') {
            abort(403);
        }

        $cliente = $user->cliente;

        // 🔹 Boletos (já existentes no sistema)
        $boletos = $cliente->boletos()
            ->with('cobranca')
            ->orderByDesc('ano')
            ->orderByDesc('mes')
            ->get();

        // 🔹 Carrega arquivos de NF da pasta do cliente
            $pastaNotasFisica = storage_path("app/boletos/cliente_{$cliente->id}");
                $arquivosNotas = [];

                if (is_dir($pastaNotasFisica)) {
                    $arquivosNotas = glob($pastaNotasFisica . DIRECTORY_SEPARATOR . '*.pdf');
                }

                foreach ($boletos as $boleto) {

                    $mes = str_pad($boleto->data_vencimento->month, 2, '0', STR_PAD_LEFT);
                    $boleto->nota_fiscal = null;

                    foreach ($arquivosNotas as $arquivoCompleto) {
                        $nome = basename($arquivoCompleto);

                        if (str_starts_with($nome, $mes . '_')) {
                            $boleto->nota_fiscal = $nome;
                            break;
                        }
                    }
                }


        return view('portal.index', compact(
            'cliente',
            'boletos'
        ));
    }

    public function downloadBoleto(Boleto $boleto)
    {
        $user = Auth::user();

        // Segurança: cliente só baixa boleto dele
        if ($boleto->cliente_id !== $user->cliente_id) {
            abort(403);
        }

        // Marca como baixado (se ainda não foi)
            DB::table('boletos')
                ->where('id', $boleto->id)
                ->whereNull('baixado_em')
                ->update([
                    'baixado_em' => now(),
            ]);

        $filePath = storage_path('app/' . $boleto->arquivo);

        if (!file_exists($filePath)) {
            abort(404, 'Boleto não encontrado.');
        }

        return response()->download($filePath);
    }

    public function downloadNotaFiscal($arquivo)
        {
            $cliente = Auth::user()->cliente;

            $caminho = storage_path("app/boletos/cliente_{$cliente->id}/{$arquivo}");

            if (!file_exists($caminho)) {
                abort(404, 'Nota fiscal não encontrada.');
            }

            return response()->download($caminho);
        }

}
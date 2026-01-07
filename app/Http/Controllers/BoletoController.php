<?php

namespace App\Http\Controllers;

use App\Models\Boleto;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BoletoController extends Controller
{
    public function upload(Request $request, Cliente $cliente)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:pdf|max:2048',
            'mes' => 'required|integer|min:1|max:12',
            'ano' => 'required|integer|min:2000',
            'valor' => 'required|numeric|min:0',
            'data_vencimento' => 'required|date',
        ]);

        // 📁 Pasta do cliente
        $path = "boletos/{$cliente->id}";

        // 🧾 Nome padrão do arquivo
        $fileName = "boleto_{$request->mes}_{$request->ano}.pdf";

        // 💾 Salva o PDF
        $filePath = $request->file('arquivo')
            ->storeAs($path, $fileName);

        // 💾 Cria ou atualiza o boleto
        Boleto::updateOrCreate(
            [
                'cliente_id' => $cliente->id,
                'mes' => $request->mes,
                'ano' => $request->ano,
            ],
            [
                'valor' => $request->valor,
                'arquivo' => $filePath,
                'status' => Boleto::STATUS_ABERTO,
                'data_vencimento' => $request->data_vencimento,
            ]
        );

        return back()->with('success', 'Boleto enviado com sucesso!');
    }
}
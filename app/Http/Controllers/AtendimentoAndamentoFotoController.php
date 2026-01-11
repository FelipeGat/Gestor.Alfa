<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\AtendimentoAndamento;
use App\Models\AtendimentoAndamentoFoto;

class AtendimentoAndamentoFotoController extends Controller
{
    /**
     * STORE
     */
    public function store(Request $request, AtendimentoAndamento $andamento)
    {
        $user = Auth::user();
        $atendimento = $andamento->atendimento;

        // Técnico só pode anexar no atendimento dele
        if ($user->tipo === 'funcionario') {
            abort_if(
                $atendimento->funcionario_id !== $user->funcionario_id,
                403,
                'Acesso não autorizado.'
            );

            abort_if(
                in_array($atendimento->status_atual, ['finalizacao', 'concluido']),
                403,
                'Não é possível anexar fotos neste status.'
            );
        }

        // Limite de fotos
        $quantidadeAtual = $andamento->fotos()->count();
        $novasFotos = count($request->file('fotos', []));

        abort_if(
            ($quantidadeAtual + $novasFotos) > 8,
            422,
            'Cada andamento pode conter no máximo 8 fotos.'
        );

        // Validação
        $request->validate(
            [
                'fotos'   => ['required', 'array'],
                'fotos.*' => ['image', 'max:2048'],
            ],
            [
                'fotos.required' => 'Selecione ao menos uma imagem.',
                'fotos.*.image'  => 'O arquivo deve ser uma imagem válida.',
                'fotos.*.max'    => 'Cada imagem deve ter no máximo 2MB.',
            ]
        );

        foreach ($request->file('fotos') as $foto) {

        $nomeArquivo = uniqid() . '.' . $foto->getClientOriginalExtension();

        $destino = public_path("uploads/andamentos/atendimento_{$atendimento->id}");

        if (!file_exists($destino)) {
            mkdir($destino, 0755, true);
        }

        $foto->move($destino, $nomeArquivo);

        $path = "uploads/andamentos/atendimento_{$atendimento->id}/{$nomeArquivo}";

        $andamento->fotos()->create([
            'arquivo' => $path,
        ]);
    }


        return back()->with('success', 'Fotos anexadas com sucesso.');
    }

    /**
     * DESTROY
     */
    public function destroy($fotoId)
    {
        $user = Auth::user();

        // Buscar foto com relacionamento
        $foto = AtendimentoAndamentoFoto::with('andamento.atendimento')->findOrFail($fotoId);

        $andamento = $foto->andamento;
        $atendimento = $andamento->atendimento;

        // Técnico só remove se for o responsável
        if ($user->tipo === 'funcionario') {
            abort_if(
                $atendimento->funcionario_id !== $user->funcionario_id,
                403,
                'Acesso não autorizado.'
            );

            abort_if(
                in_array($atendimento->status_atual, ['finalizacao', 'concluido']),
                403,
                'Não é possível remover fotos neste status.'
            );
        }

        // Remove arquivo físico
        if (Storage::disk('public')->exists($foto->arquivo)) {
            Storage::disk('public')->delete($foto->arquivo);
        }

        // Remove registro
        $foto->delete();

        return back()->with('success', 'Foto removida com sucesso.');
    }
}
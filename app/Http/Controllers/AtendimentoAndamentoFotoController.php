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
     * Retorna o caminho base correto para uploads
     */
    private function uploadsBasePath(): string
    {
        // Produção: usa o caminho definido no .env
        // Local: fallback para public/uploads
        return rtrim(
            env('UPLOADS_PUBLIC_PATH', public_path('uploads')),
            '/'
        );
    }

    /**
     * STORE
     */
    public function store(Request $request, AtendimentoAndamento $andamento)
    {
        $user = Auth::user();
        $atendimento = $andamento->atendimento;

        // Técnico só pode anexar no atendimento dele e se não estiver finalizado/concluído
        if ($user->tipo === 'funcionario') {
            // Permite visualizar fotos de qualquer andamento do atendimento ao qual está vinculado
            // Só restringe status para anexar, não para visualizar
            if ($request->isMethod('post')) {
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
        }

        // Validação: apenas 1 foto obrigatória
        $request->validate([
            'fotos'   => ['required', 'array', 'min:1', 'max:1'],
            'fotos.*' => ['image', 'max:2048'],
        ]);

        foreach ($request->file('fotos') as $foto) {
            $extensao = $foto->getClientOriginalExtension();
            $nomeArquivo = uniqid() . '_' . time() . '.' . $extensao;
            $caminho = $foto->storeAs('atendimentos/fotos', $nomeArquivo, 'public');
            // Remove prefixo 'public/' ou 'storage/' se houver
            $caminho = preg_replace('#^(public/|storage/)#', '', $caminho);
            $andamento->fotos()->create([
                'arquivo' => $caminho,
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

        $foto = AtendimentoAndamentoFoto::with('andamento.atendimento')->findOrFail($fotoId);

        $andamento = $foto->andamento;
        $atendimento = $andamento->atendimento;

        // Técnico só remove se for o responsável e se não estiver finalizado/concluído
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

        // Caminho físico correto do arquivo
        if (Storage::disk('public')->exists($foto->arquivo_storage_path)) {
            Storage::disk('public')->delete($foto->arquivo_storage_path);
        }

        $foto->delete();

        return back()->with('success', 'Foto removida com sucesso.');
    }
}

<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Advertencia;
use App\Models\Ferias;
use App\Models\Funcionario;
use App\Models\FuncionarioBeneficio;
use App\Models\FuncionarioDocumento;
use App\Models\FuncionarioEpi;
use App\Models\FuncionarioJornada;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class FuncionarioRhDadosController extends Controller
{
    public function storeDocumento(Request $request, Funcionario $funcionario): RedirectResponse
    {
        $dados = $request->validate([
            'tipo' => ['required', 'string', 'max:100'],
            'numero' => ['nullable', 'string', 'max:120'],
            'data_emissao' => ['nullable', 'date'],
            'data_vencimento' => ['nullable', 'date'],
            'arquivo' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $funcionario->documentos()->create($dados);

        return back()->with('success', 'Documento adicionado com sucesso.');
    }

    public function updateDocumento(Request $request, Funcionario $funcionario, FuncionarioDocumento $documento): RedirectResponse
    {
        $this->assertPertence($funcionario, $documento->funcionario_id);

        $dados = $request->validate([
            'tipo' => ['required', 'string', 'max:100'],
            'numero' => ['nullable', 'string', 'max:120'],
            'data_emissao' => ['nullable', 'date'],
            'data_vencimento' => ['nullable', 'date'],
            'arquivo' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $documento->update($dados);

        return back()->with('success', 'Documento atualizado com sucesso.');
    }

    public function destroyDocumento(Funcionario $funcionario, FuncionarioDocumento $documento): RedirectResponse
    {
        $this->assertPertence($funcionario, $documento->funcionario_id);
        $documento->delete();

        return back()->with('success', 'Documento removido com sucesso.');
    }

    public function storeEpi(Request $request, Funcionario $funcionario): RedirectResponse
    {
        $dados = $request->validate([
            'epi_id' => ['required', 'exists:epis,id'],
            'data_entrega' => ['required', 'date'],
            'data_prevista_troca' => ['nullable', 'date', 'after_or_equal:data_entrega'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $funcionario->episVinculos()->create($dados);

        return back()->with('success', 'EPI vinculado com sucesso.');
    }

    public function updateEpi(Request $request, Funcionario $funcionario, FuncionarioEpi $funcionarioEpi): RedirectResponse
    {
        $this->assertPertence($funcionario, $funcionarioEpi->funcionario_id);

        $dados = $request->validate([
            'epi_id' => ['required', 'exists:epis,id'],
            'data_entrega' => ['required', 'date'],
            'data_prevista_troca' => ['nullable', 'date', 'after_or_equal:data_entrega'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $funcionarioEpi->update($dados);

        return back()->with('success', 'Vínculo de EPI atualizado com sucesso.');
    }

    public function destroyEpi(Funcionario $funcionario, FuncionarioEpi $funcionarioEpi): RedirectResponse
    {
        $this->assertPertence($funcionario, $funcionarioEpi->funcionario_id);
        $funcionarioEpi->delete();

        return back()->with('success', 'Vínculo de EPI removido com sucesso.');
    }

    public function storeBeneficio(Request $request, Funcionario $funcionario): RedirectResponse
    {
        $dados = $request->validate([
            'tipo' => ['required', 'in:VT,VR,VA,Outro'],
            'valor' => ['required', 'numeric', 'min:0'],
            'desconto_percentual' => ['nullable', 'numeric', 'between:0,100'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
        ]);

        $funcionario->beneficios()->create($dados);

        return back()->with('success', 'Benefício adicionado com sucesso.');
    }

    public function updateBeneficio(Request $request, Funcionario $funcionario, FuncionarioBeneficio $beneficio): RedirectResponse
    {
        $this->assertPertence($funcionario, $beneficio->funcionario_id);

        $dados = $request->validate([
            'tipo' => ['required', 'in:VT,VR,VA,Outro'],
            'valor' => ['required', 'numeric', 'min:0'],
            'desconto_percentual' => ['nullable', 'numeric', 'between:0,100'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
        ]);

        $beneficio->update($dados);

        return back()->with('success', 'Benefício atualizado com sucesso.');
    }

    public function destroyBeneficio(Funcionario $funcionario, FuncionarioBeneficio $beneficio): RedirectResponse
    {
        $this->assertPertence($funcionario, $beneficio->funcionario_id);
        $beneficio->delete();

        return back()->with('success', 'Benefício removido com sucesso.');
    }

    public function storeJornada(Request $request, Funcionario $funcionario): RedirectResponse
    {
        $dados = $request->validate([
            'jornada_id' => ['required', Rule::exists('jornadas', 'id')->where('ativo', true)],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
        ]);

        $inicio = Carbon::parse($dados['data_inicio'])->startOfDay();
        $fimInformado = !empty($dados['data_fim']) ? Carbon::parse($dados['data_fim'])->endOfDay() : null;

        $vinculosConflitantes = FuncionarioJornada::query()
            ->where('funcionario_id', $funcionario->id)
            ->whereDate('data_inicio', '<=', $inicio->toDateString())
            ->where(function ($query) use ($inicio) {
                $query->whereNull('data_fim')
                    ->orWhereDate('data_fim', '>=', $inicio->toDateString());
            })
            ->get();

        foreach ($vinculosConflitantes as $vinculo) {
            if (Carbon::parse($vinculo->data_inicio)->isSameDay($inicio)) {
                abort(422, 'Já existe jornada iniciando nesta mesma data. Ajuste a data de início da nova jornada.');
            }

            $novoFim = $inicio->copy()->subDay();
            if (!$vinculo->data_fim || Carbon::parse($vinculo->data_fim)->gt($novoFim)) {
                $vinculo->update([
                    'data_fim' => $novoFim->toDateString(),
                ]);
            }
        }

        if ($fimInformado && $fimInformado->lt($inicio)) {
            abort(422, 'A data fim da jornada não pode ser anterior à data de início.');
        }

        $funcionario->jornadasVinculos()->create([
            'jornada_id' => (int) $dados['jornada_id'],
            'data_inicio' => $inicio->toDateString(),
            'data_fim' => $fimInformado?->toDateString(),
        ]);

        return back()->with('success', 'Nova jornada agendada com sucesso a partir da data informada. Histórico anterior preservado.');
    }

    public function updateJornada(Request $request, Funcionario $funcionario, FuncionarioJornada $funcionarioJornada): RedirectResponse
    {
        $this->assertPertence($funcionario, $funcionarioJornada->funcionario_id);

        $dados = $request->validate([
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
        ]);

        $hoje = Carbon::today();
        $inicioVinculo = Carbon::parse($funcionarioJornada->data_inicio)->startOfDay();

        if ($inicioVinculo->lt($hoje)) {
            abort(422, 'Não é permitido alterar jornada já iniciada. Para mudança de horário, cadastre uma nova jornada com data futura.');
        }

        $funcionarioJornada->update([
            'data_fim' => $dados['data_fim'] ?? null,
        ]);

        return back()->with('success', 'Período da jornada futura atualizado com sucesso.');
    }

    public function destroyJornada(Funcionario $funcionario, FuncionarioJornada $funcionarioJornada): RedirectResponse
    {
        $this->assertPertence($funcionario, $funcionarioJornada->funcionario_id);

        $hoje = Carbon::today();
        $inicioVinculo = Carbon::parse($funcionarioJornada->data_inicio)->startOfDay();

        if ($inicioVinculo->lt($hoje)) {
            abort(422, 'Não é permitido excluir jornada já iniciada. O histórico deve ser preservado.');
        }

        $funcionarioJornada->delete();

        return back()->with('success', 'Vínculo de jornada removido com sucesso.');
    }

    public function storeFerias(Request $request, Funcionario $funcionario): RedirectResponse
    {
        $dados = $request->validate([
            'periodo_aquisitivo_inicio' => ['required', 'date'],
            'periodo_aquisitivo_fim' => ['required', 'date', 'after_or_equal:periodo_aquisitivo_inicio'],
            'periodo_gozo_inicio' => ['nullable', 'date'],
            'periodo_gozo_fim' => ['nullable', 'date', 'after_or_equal:periodo_gozo_inicio'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $funcionario->ferias()->create($dados);

        return back()->with('success', 'Registro de férias adicionado com sucesso.');
    }

    public function updateFerias(Request $request, Funcionario $funcionario, Ferias $ferias): RedirectResponse
    {
        $this->assertPertence($funcionario, $ferias->funcionario_id);

        $dados = $request->validate([
            'periodo_aquisitivo_inicio' => ['required', 'date'],
            'periodo_aquisitivo_fim' => ['required', 'date', 'after_or_equal:periodo_aquisitivo_inicio'],
            'periodo_gozo_inicio' => ['nullable', 'date'],
            'periodo_gozo_fim' => ['nullable', 'date', 'after_or_equal:periodo_gozo_inicio'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $ferias->update($dados);

        return back()->with('success', 'Registro de férias atualizado com sucesso.');
    }

    public function destroyFerias(Funcionario $funcionario, Ferias $ferias): RedirectResponse
    {
        $this->assertPertence($funcionario, $ferias->funcionario_id);
        $ferias->delete();

        return back()->with('success', 'Registro de férias removido com sucesso.');
    }

    public function storeAdvertencia(Request $request, Funcionario $funcionario): RedirectResponse
    {
        $dados = $request->validate([
            'tipo' => ['required', 'string', 'max:100'],
            'descricao' => ['required', 'string', 'max:1000'],
            'data' => ['required', 'date'],
        ]);

        $funcionario->advertencias()->create($dados);

        return back()->with('success', 'Advertência adicionada com sucesso.');
    }

    public function updateAdvertencia(Request $request, Funcionario $funcionario, Advertencia $advertencia): RedirectResponse
    {
        $this->assertPertence($funcionario, $advertencia->funcionario_id);

        $dados = $request->validate([
            'tipo' => ['required', 'string', 'max:100'],
            'descricao' => ['required', 'string', 'max:1000'],
            'data' => ['required', 'date'],
        ]);

        $advertencia->update($dados);

        return back()->with('success', 'Advertência atualizada com sucesso.');
    }

    public function destroyAdvertencia(Funcionario $funcionario, Advertencia $advertencia): RedirectResponse
    {
        $this->assertPertence($funcionario, $advertencia->funcionario_id);
        $advertencia->delete();

        return back()->with('success', 'Advertência removida com sucesso.');
    }

    private function assertPertence(Funcionario $funcionario, int $funcionarioId): void
    {
        abort_if($funcionario->id !== $funcionarioId, 404);
    }

}

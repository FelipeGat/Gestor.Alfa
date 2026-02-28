<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Feriado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeriadoController extends Controller
{
    public function index()
    {
        $this->garantirFeriadosNacionaisPadrao();

        $feriados = Feriado::query()
            ->orderBy('data')
            ->get();

        return view('rh.feriados.index', compact('feriados'));
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'data' => ['required', 'date'],
            'tipo' => ['required', 'string', 'max:50'],
            'recorrente_anual' => ['nullable', 'boolean'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        Feriado::create([
            ...$dados,
            'recorrente_anual' => $request->boolean('recorrente_anual'),
            'ativo' => $request->boolean('ativo', true),
        ]);

        return back()->with('success', 'Feriado cadastrado com sucesso.');
    }

    public function update(Request $request, Feriado $feriado): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'data' => ['required', 'date'],
            'tipo' => ['required', 'string', 'max:50'],
            'recorrente_anual' => ['nullable', 'boolean'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $feriado->update([
            ...$dados,
            'recorrente_anual' => $request->boolean('recorrente_anual'),
            'ativo' => $request->boolean('ativo'),
        ]);

        return back()->with('success', 'Feriado atualizado com sucesso.');
    }

    public function destroy(Feriado $feriado): RedirectResponse
    {
        $feriado->delete();

        return back()->with('success', 'Feriado removido com sucesso.');
    }

    private function garantirFeriadosNacionaisPadrao(): void
    {
        $anoAtual = now()->year;

        $fixos = [
            ['nome' => 'Confraternização Universal', 'data' => sprintf('%04d-01-01', $anoAtual)],
            ['nome' => 'Tiradentes', 'data' => sprintf('%04d-04-21', $anoAtual)],
            ['nome' => 'Dia do Trabalho', 'data' => sprintf('%04d-05-01', $anoAtual)],
            ['nome' => 'Independência do Brasil', 'data' => sprintf('%04d-09-07', $anoAtual)],
            ['nome' => 'Nossa Senhora Aparecida', 'data' => sprintf('%04d-10-12', $anoAtual)],
            ['nome' => 'Finados', 'data' => sprintf('%04d-11-02', $anoAtual)],
            ['nome' => 'Proclamação da República', 'data' => sprintf('%04d-11-15', $anoAtual)],
            ['nome' => 'Dia da Consciência Negra', 'data' => sprintf('%04d-11-20', $anoAtual)],
            ['nome' => 'Natal', 'data' => sprintf('%04d-12-25', $anoAtual)],
        ];

        foreach ($fixos as $feriado) {
            Feriado::query()->firstOrCreate(
                [
                    'nome' => $feriado['nome'],
                    'tipo' => 'nacional',
                    'recorrente_anual' => true,
                ],
                [
                    'data' => $feriado['data'],
                    'ativo' => true,
                ]
            );
        }

        $diaPascoa = $this->calcularDiaPascoa($anoAtual);
        $pascoa = now()->setDate($anoAtual, 1, 1)->startOfDay()->setTimestamp($diaPascoa);
        $moveis = [
            ['nome' => 'Carnaval', 'data' => $pascoa->copy()->subDays(48)->toDateString()],
            ['nome' => 'Carnaval', 'data' => $pascoa->copy()->subDays(47)->toDateString()],
            ['nome' => 'Sexta-feira Santa', 'data' => $pascoa->copy()->subDays(2)->toDateString()],
            ['nome' => 'Corpus Christi', 'data' => $pascoa->copy()->addDays(60)->toDateString()],
        ];

        foreach ($moveis as $feriado) {
            Feriado::query()->firstOrCreate(
                [
                    'nome' => $feriado['nome'],
                    'data' => $feriado['data'],
                    'tipo' => 'nacional',
                ],
                [
                    'recorrente_anual' => false,
                    'ativo' => true,
                ]
            );
        }
    }

    /**
     * Calcula o dia de Páscoa usando o algoritmo de Meeus/Jones/Butcher.
     * Retorna o timestamp Unix do dia de Páscoa.
     */
    private function calcularDiaPascoa(int $ano): int
    {
        $a = $ano % 19;
        $b = (int) floor($ano / 100);
        $c = $ano % 100;
        $d = (int) floor($b / 4);
        $e = $b % 4;
        $f = (int) floor(($b + 8) / 25);
        $g = (int) floor(($b - $f + 1) / 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = (int) floor($c / 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = (int) floor(($a + 11 * $h + 22 * $l) / 451);
        $mes = (int) floor(($h + $l - 7 * $m + 114) / 31);
        $dia = (($h + $l - 7 * $m + 114) % 31) + 1;

        return mktime(0, 0, 0, $mes, $dia, $ano);
    }
}

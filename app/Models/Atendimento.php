<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Assunto;
use App\Models\Orcamento;

class Atendimento extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero_atendimento',
        'cliente_id',
        'nome_solicitante',
        'telefone_solicitante',
        'email_solicitante',
        'assunto_id',
        'descricao',
        'prioridade',
        'empresa_id',
        'funcionario_id',
        'status_atual',
        'is_orcamento',
        'atendimento_origem_id',
        'data_atendimento',
        'iniciado_em',
        'iniciado_por_user_id',
        'finalizado_em',
        'finalizado_por_user_id',
        'tempo_execucao_segundos',
        'tempo_pausa_segundos',
        'em_execucao',
        'em_pausa',
        'assinatura_cliente_nome',
        'assinatura_cliente_cargo',
        'assinatura_cliente_path',
    ];

    protected $casts = [
        'data_atendimento' => 'date',
        'iniciado_em' => 'datetime',
        'finalizado_em' => 'datetime',
        'em_execucao' => 'boolean',
        'em_pausa' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function assunto()
    {
        return $this->belongsTo(Assunto::class)->withDefault([
            'nome' => '—',
        ]);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class)->withDefault([
            'nome_fantasia' => '—',
        ]);
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function historicos()
    {
        return $this->hasMany(AtendimentoStatusHistorico::class);
    }

    public function atendimentoOrigem()
    {
        return $this->belongsTo(Atendimento::class, 'atendimento_origem_id');
    }

    public function andamentos()
    {
        return $this->hasMany(AtendimentoAndamento::class)
            ->orderBy('created_at', 'desc');
    }

    public function orcamento()
    {
        return $this->hasOne(Orcamento::class);
    }

    public function statusHistoricos()
    {
        return $this->hasMany(
            \App\Models\AtendimentoStatusHistorico::class,
            'atendimento_id'
        )->orderBy('created_at', 'desc');
    }

    public function pausas()
    {
        return $this->hasMany(AtendimentoPausa::class)->orderBy('iniciada_em', 'desc');
    }

    public function iniciadoPor()
    {
        return $this->belongsTo(User::class, 'iniciado_por_user_id');
    }

    public function finalizadoPor()
    {
        return $this->belongsTo(User::class, 'finalizado_por_user_id');
    }

    /**
     * Retorna a pausa ativa (em andamento)
     */
    public function pausaAtiva()
    {
        return $this->pausas()->whereNull('encerrada_em')->first();
    }

    /**
     * Formata o tempo de execução em horas:minutos:segundos
     */
    public function getTempoExecucaoFormatadoAttribute(): string
    {
        $segundos = max(0, $this->tempo_execucao_segundos ?? 0);
        $horas = floor($segundos / 3600);
        $minutos = floor(($segundos % 3600) / 60);
        $segs = $segundos % 60;
        return sprintf('%02d:%02d:%02d', $horas, $minutos, $segs);
    }

    /**
     * Formata o tempo de pausa em horas:minutos:segundos
     */
    public function getTempoPausaFormatadoAttribute(): string
    {
        $segundos = max(0, $this->tempo_pausa_segundos ?? 0);
        $horas = floor($segundos / 3600);
        $minutos = floor(($segundos % 3600) / 60);
        $segs = $segundos % 60;
        return sprintf('%02d:%02d:%02d', $horas, $minutos, $segs);
    }
}

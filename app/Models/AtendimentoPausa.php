<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtendimentoPausa extends Model
{
    protected $fillable = [
        'atendimento_id',
        'user_id',
        'tipo_pausa',
        'iniciada_em',
        'encerrada_em',
        'retomado_por_user_id',
        'tempo_segundos',
        'foto_inicio_path',
        'foto_retorno_path',
    ];

    protected $casts = [
        'iniciada_em' => 'datetime',
        'encerrada_em' => 'datetime',
    ];

    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function retomadoPor()
    {
        return $this->belongsTo(User::class, 'retomado_por_user_id');
    }

    /**
     * Verifica se a pausa está em andamento
     */
    public function emAndamento(): bool
    {
        return is_null($this->encerrada_em);
    }

    /**
     * Encerra a pausa e calcula o tempo
     */
    public function encerrar(): void
    {
        $this->encerrada_em = now();
        $this->tempo_segundos = $this->iniciada_em->diffInSeconds($this->encerrada_em);
        $this->save();
    }

    /**
     * Retorna o label do tipo de pausa
     */
    public function getTipoPausaLabelAttribute(): string
    {
        return match ($this->tipo_pausa) {
            'almoco' => 'Almoço',
            'deslocamento' => 'Deslocamento entre Clientes',
            'material' => 'Compra de Material',
            'fim_dia' => 'Encerramento do Dia',
            default => $this->tipo_pausa,
        };
    }
}

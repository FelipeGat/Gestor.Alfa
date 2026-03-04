<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Equipamento extends Model
{
    protected $table = 'equipamentos';

    protected $fillable = [
        'cliente_id',
        'setor_id',
        'responsavel_id',
        'fornecedor_id',
        'nome',
        'modelo',
        'fabricante',
        'numero_serie',
        'codigo_ativo',
        'tag_patrimonial',
        'data_aquisicao',
        'data_instalacao',
        'vida_util_anos',
        'capacidade',
        'potencia',
        'voltagem',
        'status_ativo',
        'criticidade',
        'possui_garantia',
        'garantia_inicio',
        'garantia_fim',
        'valor_aquisicao',
        'unidade',
        'andar',
        'sala',
        'localizacao_detalhada',
        'ultima_manutencao',
        'ultima_limpeza',
        'periodicidade_manutencao_meses',
        'periodicidade_limpeza_meses',
        'observacoes',
        'ativo',
        'qrcode_token',
        'qr_code',
        'foto_principal',
    ];

    protected $casts = [
        'data_aquisicao' => 'date',
        'data_instalacao' => 'date',
        'vida_util_anos' => 'integer',
        'status_ativo' => 'string',
        'criticidade' => 'string',
        'possui_garantia' => 'boolean',
        'garantia_inicio' => 'date',
        'garantia_fim' => 'date',
        'valor_aquisicao' => 'decimal:2',
        'ultima_manutencao' => 'date',
        'ultima_limpeza' => 'date',
        'periodicidade_manutencao_meses' => 'integer',
        'periodicidade_limpeza_meses' => 'integer',
        'ativo' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($equipamento) {
            if (empty($equipamento->qrcode_token)) {
                $equipamento->qrcode_token = Str::uuid()->toString();
            }
        });
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function setor(): BelongsTo
    {
        return $this->belongsTo(EquipamentoSetor::class, 'setor_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(EquipamentoResponsavel::class, 'responsavel_id');
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    public function manutencoes(): HasMany
    {
        return $this->hasMany(EquipamentoManutencao::class)->orderByDesc('data');
    }

    public function limpezas(): HasMany
    {
        return $this->hasMany(EquipamentoLimpeza::class)->orderByDesc('data');
    }

    public function historicoManutencoes(): HasMany
    {
        return $this->hasMany(AtivoManutencao::class, 'ativo_id')->orderByDesc('data_manutencao');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(AtivoDocumento::class, 'ativo_id')->orderByDesc('created_at');
    }

    public function getProximaManutencaoAttribute(): ?Carbon
    {
        if (! $this->ultima_manutencao) {
            return null;
        }

        return $this->ultima_manutencao->copy()->addMonths($this->periodicidade_manutencao_meses);
    }

    public function getProximaLimpezaAttribute(): ?Carbon
    {
        if (! $this->ultima_limpeza) {
            return null;
        }

        return $this->ultima_limpeza->copy()->addMonths($this->periodicidade_limpeza_meses);
    }

    public function getDiasParaManutencaoAttribute(): ?int
    {
        $proxima = $this->proxima_manutencao;
        if (! $proxima) {
            return null;
        }

        return now()->diffInDays($proxima, false);
    }

    public function getDiasParaLimpezaAttribute(): ?int
    {
        $proxima = $this->proxima_limpeza;
        if (! $proxima) {
            return null;
        }

        return now()->diffInDays($proxima, false);
    }

    public function getStatusManutencaoAttribute(): array
    {
        $dias = $this->dias_para_manutencao;

        if ($dias === null) {
            return [
                'cor' => 'gray',
                'classe' => 'bg-gray-100 text-gray-800',
                'dias' => null,
                'mensagem' => 'Sem registro',
            ];
        }

        if ($dias < 0) {
            return [
                'cor' => 'vermelho',
                'classe' => 'bg-red-100 text-red-800',
                'dias' => abs($dias),
                'mensagem' => 'Manutenção vencida há '.abs($dias).' dias',
            ];
        }

        if ($dias <= 30) {
            return [
                'cor' => 'amarelo',
                'classe' => 'bg-yellow-100 text-yellow-800',
                'dias' => $dias,
                'mensagem' => 'Atenção: manutenção vence em '.$dias.' dias',
            ];
        }

        return [
            'cor' => 'verde',
            'classe' => 'bg-green-100 text-green-800',
            'dias' => $dias,
            'mensagem' => 'Próxima manutenção em '.$dias.' dias',
        ];
    }

    public function getStatusLimpezaAttribute(): array
    {
        $dias = $this->dias_para_limpeza;

        if ($dias === null) {
            return [
                'cor' => 'gray',
                'classe' => 'bg-gray-100 text-gray-800',
                'dias' => null,
                'mensagem' => 'Sem registro',
            ];
        }

        if ($dias < 0) {
            return [
                'cor' => 'vermelho',
                'classe' => 'bg-red-100 text-red-800',
                'dias' => abs($dias),
                'mensagem' => 'Limpeza vencida há '.abs($dias).' dias',
            ];
        }

        if ($dias <= 30) {
            return [
                'cor' => 'amarelo',
                'classe' => 'bg-yellow-100 text-yellow-800',
                'dias' => $dias,
                'mensagem' => 'Atenção: limpeza vence em '.$dias.' dias',
            ];
        }

        return [
            'cor' => 'verde',
            'classe' => 'bg-green-100 text-green-800',
            'dias' => $dias,
            'mensagem' => 'Próxima limpeza em '.$dias.' dias',
        ];
    }

    public function getQrCodeUrlAttribute(): string
    {
        return route('portal.equipamento.chamado', $this->qrcode_token);
    }

    public function getLocalizacaoResumoAttribute(): string
    {
        $partes = array_filter([
            $this->unidade,
            $this->setor?->nome,
            $this->andar ? 'Andar '.$this->andar : null,
            $this->sala ? 'Sala '.$this->sala : null,
        ]);

        return ! empty($partes) ? implode(' • ', $partes) : '-';
    }

    public function getCustoTotalManutencaoAttribute(): string
    {
        $total = $this->historicoManutencoes()->sum('custo');

        return number_format((float) $total, 2, '.', '');
    }

    public function registrarManutencao(array $dados): EquipamentoManutencao
    {
        $manutencao = $this->manutencoes()->create([
            'data' => $dados['data'] ?? now()->format('Y-m-d'),
            'tipo' => $dados['tipo'] ?? 'preventiva',
            'descricao' => $dados['descricao'] ?? null,
            'realizado_por' => $dados['realizado_por'] ?? null,
        ]);

        $this->update([
            'ultima_manutencao' => $manutencao->data,
        ]);

        return $manutencao;
    }

    public function registrarLimpeza(array $dados): EquipamentoLimpeza
    {
        $limpeza = $this->limpezas()->create([
            'data' => $dados['data'] ?? now()->format('Y-m-d'),
            'descricao' => $dados['descricao'] ?? null,
            'realizado_por' => $dados['realizado_por'] ?? null,
        ]);

        $this->update([
            'ultima_limpeza' => $limpeza->data,
        ]);

        return $limpeza;
    }

    public function scopeAtivo(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    public function scopeCliente(Builder $query, int $clienteId): Builder
    {
        return $query->where('cliente_id', $clienteId);
    }
}

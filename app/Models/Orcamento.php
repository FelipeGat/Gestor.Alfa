<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Orcamento extends Model
{
    use HasFactory;

    protected $table = 'orcamentos';

    protected $fillable = [
        'empresa_id',
        'atendimento_id',
        'cliente_id',
        'numero_orcamento',
        'status',
        'valor_total',
        'observacoes',
        'created_by',
    ];

    /* ===================== RELACIONAMENTOS ===================== */

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function gerarNumero(int $empresaId): string
    {
        $ano = now()->year;

        $ultimo = self::where('empresa_id', $empresaId)
            ->whereYear('created_at', $ano)
            ->select(DB::raw("MAX(CAST(SUBSTRING_INDEX(numero_orcamento, '/', 1) AS UNSIGNED)) as ultimo"))
            ->value('ultimo');

        $sequencial = str_pad(($ultimo ?? 0) + 1, 3, '0', STR_PAD_LEFT);

        return "{$sequencial}/{$ano}";
    }
}
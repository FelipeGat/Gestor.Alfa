<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Cobranca;
use App\Models\Boleto;
use App\Models\User;
use App\Models\Email;
use App\Models\Telefone;
use App\Models\NotaFiscal;

class Cliente extends Model
{
    use SoftDeletes;

    /**
     * Retorna o CPF ou CNPJ formatado
     */
    public function getCpfCnpjFormatadoAttribute(): ?string
    {
        $valor = preg_replace('/\D/', '', $this->cpf_cnpj);
        if (!$valor) return null;
        if (strlen($valor) === 11) {
            // CPF: xxx.xxx.xxx-xx
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $valor);
        } elseif (strlen($valor) === 14) {
            // CNPJ: xx.xxx.xxx/xxxx-xx
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $valor);
        }
        return $this->cpf_cnpj;
    }

    protected $fillable = [
        'nome',
        'nome_fantasia',
        'ativo',
        'valor_mensal',
        'dia_vencimento',
        'tipo_pessoa',
        'cpf_cnpj',
        'razao_social',
        'tipo_cliente',
        'data_cadastro',
        'cep',
        'logradouro',
        'numero',
        'bairro',
        'cidade',
        'estado',
        'complemento',
        'inscricao_estadual',
        'inscricao_municipal',
        'observacoes',
    ];

    protected $casts = [
        'ativo'          => 'boolean',
        'valor_mensal'   => 'decimal:2',
        'dia_vencimento' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function emails()
    {
        return $this->hasMany(Email::class);
    }

    public function telefones()
    {
        return $this->hasMany(Telefone::class);
    }

    public function cobrancas()
    {
        return $this->hasMany(Cobranca::class);
    }

    public function boletos()
    {
        return $this->hasMany(Boleto::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function notasFiscais()
    {
        return $this->hasMany(NotaFiscal::class);
    }

    public function empresas()
    {
        return $this->belongsToMany(Empresa::class);
    }

    public function getNomeExibicaoAttribute(): string
    {
        // Pessoa Física
        if ($this->tipo_pessoa === 'PF') {
            return $this->nome;
        }

        // Pessoa Jurídica
        if (!empty($this->nome)) {
            return $this->nome;
        }

        return $this->razao_social;
    }

    /**
     * Usuários vinculados a este cliente (multi-unidade)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'cliente_user',
            'cliente_id',
            'user_id'
        );
    }

    /** Cliente possui contrato mensal ativo*/
    public function isContratoMensal(): bool
    {
        return $this->ativo
            && $this->tipo_cliente === 'CONTRATO'
            && $this->valor_mensal > 0
            && $this->dia_vencimento > 0;
    }

    /** Cliente é avulso (sem cobrança automática)*/
    public function isAvulso(): bool
    {
        return $this->tipo_cliente === 'AVULSO';
    }

    /** bug de fevereiro*/
    public function getDiaVencimentoSeguro(): int
    {
        return min($this->dia_vencimento ?? 1, 28);
    }

    /** data vencimento automatica*/
    public function gerarDataVencimento(int $mes, int $ano)
    {
        $diaDesejado = $this->dia_vencimento ?? 1;

        // Criar data com o primeiro dia do mês
        $data = \Carbon\Carbon::create($ano, $mes, 1);

        // Obter o último dia do mês
        $ultimoDiaDoMes = $data->endOfMonth()->day;

        // Se o dia desejado é 30 ou 31, usar o último dia do mês
        // Caso contrário, usar o menor entre dia desejado e último dia
        if ($diaDesejado >= 30) {
            $data->day($ultimoDiaDoMes);
        } else {
            $data->day(min($diaDesejado, $ultimoDiaDoMes));
        }

        return $data;
    }
}

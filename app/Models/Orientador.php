<?php

namespace App\Models;

class Orientador extends BaseModel
{
    protected $table = 'orientadores';

    protected $fillable = [
        'usuario_id',
        'departamento_id',
        'titulacao',
        'areas_atuacao',
        'lattes_url',
        'orcid',
        'max_orientandos',
        'orientandos_atuais',
        'aceita_coorientacao',
        'ativo',
    ];

    protected $casts = [
        'areas_atuacao' => 'array',
        'max_orientandos' => 'integer',
        'orientandos_atuais' => 'integer',
        'aceita_coorientacao' => 'boolean',
        'ativo' => 'boolean',
    ];

    // Relacionamentos
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function orientacoes()
    {
        return $this->hasMany(Orientacao::class);
    }

    public function orientacoesAtivas()
    {
        return $this->orientacoes()->where('ativo', true);
    }

    public function tccsOrientando()
    {
        return $this->hasManyThrough(
            Tcc::class,
            Orientacao::class,
            'orientador_id',
            'id',
            'id',
            'tcc_id'
        )->where('orientacoes.tipo_orientacao', 'ORIENTADOR')
         ->where('orientacoes.ativo', true);
    }

    public function tccsCoorientando()
    {
        return $this->hasManyThrough(
            Tcc::class,
            Orientacao::class,
            'orientador_id',
            'id',
            'id',
            'tcc_id'
        )->where('orientacoes.tipo_orientacao', 'COORIENTADOR')
         ->where('orientacoes.ativo', true);
    }

    // Scopes
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeDisponiveis($query)
    {
        return $query->where('ativo', true)
            ->whereRaw('orientandos_atuais < max_orientandos');
    }

    public function scopePorDepartamento($query, string $departamentoId)
    {
        return $query->where('departamento_id', $departamentoId);
    }

    public function scopePorTitulacao($query, string $titulacao)
    {
        return $query->where('titulacao', $titulacao);
    }

    public function scopeAceitaCoorientacao($query)
    {
        return $query->where('aceita_coorientacao', true);
    }

    // Methods
    public function getNomeCompleto(): string
    {
        return $this->usuario->nome_completo;
    }

    public function getEmail(): string
    {
        return $this->usuario->email;
    }

    public function getTitulacaoFormatada(): string
    {
        $titulacoes = [
            'ESPECIALISTA' => 'Esp.',
            'MESTRE' => 'Me.',
            'DOUTOR' => 'Dr.',
            'POS_DOUTOR' => 'Dr.',
        ];

        return $titulacoes[$this->titulacao] ?? '';
    }

    public function podeOrientar(): bool
    {
        return $this->ativo && $this->orientandos_atuais < $this->max_orientandos;
    }

    public function podeCoorientar(): bool
    {
        return $this->ativo && $this->aceita_coorientacao;
    }

    public function getVagasDisponiveis(): int
    {
        return max(0, $this->max_orientandos - $this->orientandos_atuais);
    }

    public function getTaxaOcupacao(): float
    {
        if ($this->max_orientandos == 0) {
            return 0;
        }

        return ($this->orientandos_atuais / $this->max_orientandos) * 100;
    }

    public function incrementarOrientandos(): void
    {
        $this->increment('orientandos_atuais');
    }

    public function decrementarOrientandos(): void
    {
        if ($this->orientandos_atuais > 0) {
            $this->decrement('orientandos_atuais');
        }
    }

    public function getTccsPorStatus(): array
    {
        return $this->tccsOrientando()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    public function getTotalTccsOrientados(): int
    {
        return $this->tccsOrientando()->count();
    }

    public function getTotalTccsConcluidos(): int
    {
        return $this->tccsOrientando()
            ->whereIn('status', ['APROVADO', 'APROVADO_COM_RESSALVAS'])
            ->count();
    }

    public function getMediaNotasOrientados(): ?float
    {
        return $this->tccsOrientando()
            ->whereNotNull('nota_final')
            ->avg('nota_final');
    }
}

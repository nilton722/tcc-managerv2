<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends BaseModel
{
    protected $table = 'cursos';

    protected $fillable = [
        'departamento_id',
        'coordenador_id',
        'nome',
        'codigo',
        'nivel',
        'duracao_semestres',
        'ativo',
    ];

    protected $casts = [
        'duracao_semestres' => 'integer',
        'ativo' => 'boolean',
    ];

    // ==================== RELATIONSHIPS ====================

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function coordenador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'coordenador_id');
    }

    public function linhasPesquisa(): HasMany
    {
        return $this->hasMany(LinhaPesquisa::class);
    }

    public function alunos(): HasMany
    {
        return $this->hasMany(Aluno::class);
    }

    public function tccs(): HasMany
    {
        return $this->hasMany(Tcc::class);
    }

    public function templatesCronograma(): HasMany
    {
        return $this->hasMany(TemplateCronograma::class);
    }

    // ==================== SCOPES ====================

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorDepartamento($query, string $departamentoId)
    {
        return $query->where('departamento_id', $departamentoId);
    }

    public function scopePorNivel($query, string $nivel)
    {
        return $query->where('nivel', $nivel);
    }

    public function scopeBuscar($query, string $termo)
    {
        return $query->where(function ($q) use ($termo) {
            $q->where('nome', 'like', "%{$termo}%")
              ->orWhere('codigo', 'like', "%{$termo}%");
        });
    }

    // ==================== METHODS ====================

    public function ativar(): void
    {
        $this->update(['ativo' => true]);
    }

    public function desativar(): void
    {
        $this->update(['ativo' => false]);
    }

    public function getTotalAlunos(): int
    {
        return $this->alunos()->count();
    }

    public function getTotalAlunosAtivos(): int
    {
        return $this->alunos()->whereHas('usuario', function ($query) {
            $query->where('status', 'ATIVO');
        })->count();
    }

    public function getTotalTccs(): int
    {
        return $this->tccs()->count();
    }

    public function getTotalTccsPorStatus(string $status): int
    {
        return $this->tccs()->where('status', $status)->count();
    }

    public function getMediaNotas(): ?float
    {
        return $this->tccs()
            ->whereNotNull('nota_final')
            ->avg('nota_final');
    }

    public function getTaxaAprovacao(): float
    {
        $total = $this->tccs()
            ->whereIn('status', ['APROVADO', 'APROVADO_COM_RESSALVAS', 'REPROVADO'])
            ->count();

        if ($total === 0) {
            return 0;
        }

        $aprovados = $this->tccs()
            ->whereIn('status', ['APROVADO', 'APROVADO_COM_RESSALVAS'])
            ->count();

        return round(($aprovados / $total) * 100, 2);
    }

    public function getNivelFormatado(): string
    {
        return match ($this->nivel) {
            'GRADUACAO' => 'Graduação',
            'ESPECIALIZACAO' => 'Especialização',
            'MESTRADO' => 'Mestrado',
            'DOUTORADO' => 'Doutorado',
            'LICENCIATURA' => 'Licenciatura',
            default => $this->nivel,
        };
    }
}

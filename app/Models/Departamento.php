<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departamento extends BaseModel
{
    protected $table = 'departamentos';

    protected $fillable = [
        'instituicao_id',
        'nome',
        'codigo',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    // ==================== RELATIONSHIPS ====================

    public function instituicao(): BelongsTo
    {
        return $this->belongsTo(Instituicao::class);
    }

    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class);
    }

    public function orientadores(): HasMany
    {
        return $this->hasMany(Orientador::class);
    }

    // ==================== SCOPES ====================

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorInstituicao($query, string $instituicaoId)
    {
        return $query->where('instituicao_id', $instituicaoId);
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

    public function getTotalCursos(): int
    {
        return $this->cursos()->count();
    }

    public function getTotalOrientadores(): int
    {
        return $this->orientadores()->count();
    }

    public function getTotalOrientadoresAtivos(): int
    {
        return $this->orientadores()->ativos()->count();
    }
}

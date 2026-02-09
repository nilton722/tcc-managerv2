<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LinhaPesquisa extends BaseModel
{
    protected $table = 'linhas_pesquisa';

    protected $fillable = [
        'curso_id',
        'nome',
        'descricao',
        'area_conhecimento',
        'palavras_chave',
        'ativo',
    ];

    protected $casts = [
        'palavras_chave' => 'array',
        'ativo' => 'boolean',
    ];

    // ==================== RELATIONSHIPS ====================

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function tccs(): HasMany
    {
        return $this->hasMany(Tcc::class);
    }

    // ==================== SCOPES ====================

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorCurso($query, string $cursoId)
    {
        return $query->where('curso_id', $cursoId);
    }

    public function scopePorArea($query, string $area)
    {
        return $query->where('area_conhecimento', $area);
    }

    public function scopeBuscar($query, string $termo)
    {
        return $query->where(function ($q) use ($termo) {
            $q->where('nome', 'like', "%{$termo}%")
              ->orWhere('descricao', 'like', "%{$termo}%")
              ->orWhere('area_conhecimento', 'like', "%{$termo}%");
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

    public function getTotalTccs(): int
    {
        return $this->tccs()->count();
    }

    public function getTotalTccsAtivos(): int
    {
        return $this->tccs()
            ->whereIn('status', ['RASCUNHO', 'EM_ORIENTACAO', 'AGUARDANDO_BANCA', 'BANCA_AGENDADA', 'EM_AVALIACAO'])
            ->count();
    }

    public function getTotalTccsConcluidos(): int
    {
        return $this->tccs()
            ->whereIn('status', ['APROVADO', 'APROVADO_COM_RESSALVAS'])
            ->count();
    }
}

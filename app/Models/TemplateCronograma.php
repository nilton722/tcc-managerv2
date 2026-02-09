<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateCronograma extends BaseModel
{
    protected $table = 'templates_cronograma';

    protected $fillable = [
        'curso_id',
        'nome',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    // ==================== RELATIONSHIPS ====================

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function etapas(): HasMany
    {
        return $this->hasMany(EtapaTemplate::class);
    }

    public function cronogramasTcc(): HasMany
    {
        return $this->hasMany(CronogramaTcc::class);
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

    // ==================== METHODS ====================

    public function ativar(): void
    {
        $this->update(['ativo' => true]);
    }

    public function desativar(): void
    {
        $this->update(['ativo' => false]);
    }

    public function getTotalEtapas(): int
    {
        return $this->etapas()->count();
    }

    public function getTotalEtapasObrigatorias(): int
    {
        return $this->etapas()->where('obrigatoria', true)->count();
    }

    public function getDuracaoTotalDias(): int
    {
        return $this->etapas()->sum('duracao_dias');
    }

    public function clonar(string $novoNome): self
    {
        $novoTemplate = $this->replicate();
        $novoTemplate->nome = $novoNome;
        $novoTemplate->save();

        // Clonar etapas
        foreach ($this->etapas as $etapa) {
            $novaEtapa = $etapa->replicate();
            $novaEtapa->template_cronograma_id = $novoTemplate->id;
            $novaEtapa->save();
        }

        return $novoTemplate;
    }
}

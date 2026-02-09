<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Orientacao extends BaseModel
{
    protected $table = 'orientacoes';

    protected $fillable = [
        'tcc_id',
        'orientador_id',
        'tipo_orientacao',
        'data_inicio',
        'data_fim',
        'ativo',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'ativo' => 'boolean',
    ];

    // ==================== RELATIONSHIPS ====================

    public function tcc(): BelongsTo
    {
        return $this->belongsTo(Tcc::class);
    }

    public function orientador(): BelongsTo
    {
        return $this->belongsTo(Orientador::class);
    }

    // ==================== SCOPES ====================

    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_orientacao', $tipo);
    }

    public function scopePorOrientador($query, string $orientadorId)
    {
        return $query->where('orientador_id', $orientadorId);
    }

    public function scopePorTcc($query, string $tccId)
    {
        return $query->where('tcc_id', $tccId);
    }

    // ==================== METHODS ====================

    public function isOrientador(): bool
    {
        return $this->tipo_orientacao === 'ORIENTADOR';
    }

    public function isCoorientador(): bool
    {
        return $this->tipo_orientacao === 'COORIENTADOR';
    }

    public function finalizar(): void
    {
        $this->update([
            'data_fim' => now(),
            'ativo' => false,
        ]);
    }

    public function getTipoFormatado(): string
    {
        return match ($this->tipo_orientacao) {
            'ORIENTADOR' => 'Orientador',
            'COORIENTADOR' => 'Coorientador',
            default => $this->tipo_orientacao,
        };
    }

    public function getDuracaoEmMeses(): ?int
    {
        if (!$this->data_fim) {
            return now()->diffInMonths($this->data_inicio);
        }

        return $this->data_fim->diffInMonths($this->data_inicio);
    }
}

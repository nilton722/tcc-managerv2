<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Avaliacao extends BaseModel
{
    protected $table = 'avaliacoes';

    protected $fillable = [
        'banca_id',
        'membro_banca_id',
        'nota',
        'parecer',
        'criterios_avaliacao',
        'resultado',
        'recomendacoes',
        'data_avaliacao',
    ];

    protected $casts = [
        'nota' => 'decimal:2',
        'criterios_avaliacao' => 'array',
        'data_avaliacao' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function banca(): BelongsTo
    {
        return $this->belongsTo(Banca::class);
    }

    public function membroBanca(): BelongsTo
    {
        return $this->belongsTo(MembroBanca::class);
    }

    // ==================== SCOPES ====================

    public function scopePorBanca($query, string $bancaId)
    {
        return $query->where('banca_id', $bancaId);
    }

    public function scopePorMembro($query, string $membroBancaId)
    {
        return $query->where('membro_banca_id', $membroBancaId);
    }

    public function scopePorResultado($query, string $resultado)
    {
        return $query->where('resultado', $resultado);
    }

    public function scopeAprovadas($query)
    {
        return $query->whereIn('resultado', ['APROVADO', 'APROVADO_COM_RESSALVAS']);
    }

    public function scopeReprovadas($query)
    {
        return $query->where('resultado', 'REPROVADO');
    }

    // ==================== METHODS ====================

    public function isAprovado(): bool
    {
        return in_array($this->resultado, ['APROVADO', 'APROVADO_COM_RESSALVAS']);
    }

    public function isAprovadoSemRessalvas(): bool
    {
        return $this->resultado === 'APROVADO';
    }

    public function isAprovadoComRessalvas(): bool
    {
        return $this->resultado === 'APROVADO_COM_RESSALVAS';
    }

    public function isReprovado(): bool
    {
        return $this->resultado === 'REPROVADO';
    }

    public function getResultadoFormatado(): string
    {
        return match ($this->resultado) {
            'APROVADO' => 'Aprovado',
            'APROVADO_COM_RESSALVAS' => 'Aprovado com Ressalvas',
            'REPROVADO' => 'Reprovado',
            default => $this->resultado,
        };
    }

    public function getNotaFormatada(): string
    {
        return number_format($this->nota, 2, ',', '.');
    }

    public function getResultadoBadge(): string
    {
        return match ($this->resultado) {
            'APROVADO' => '<span class="badge bg-success">Aprovado</span>',
            'APROVADO_COM_RESSALVAS' => '<span class="badge bg-warning">Aprovado com Ressalvas</span>',
            'REPROVADO' => '<span class="badge bg-danger">Reprovado</span>',
            default => '<span class="badge bg-secondary">' . $this->resultado . '</span>',
        };
    }

    public function getConceito(): string
    {
        $nota = (float) $this->nota;

        return match (true) {
            $nota >= 9.0 => 'Excelente',
            $nota >= 8.0 => 'Ótimo',
            $nota >= 7.0 => 'Bom',
            $nota >= 6.0 => 'Regular',
            default => 'Insuficiente',
        };
    }
}

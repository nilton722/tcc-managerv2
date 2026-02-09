<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Banca extends BaseModel
{
    protected $table = 'bancas';

    protected $fillable = [
        'tcc_id',
        'ata_documento_id',
        'tipo_banca',
        'data_agendada',
        'local',
        'formato',
        'link_reuniao',
        'status',
    ];

    protected $casts = [
        'data_agendada' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function tcc(): BelongsTo
    {
        return $this->belongsTo(Tcc::class);
    }

    public function ataDocumento(): BelongsTo
    {
        return $this->belongsTo(Documento::class, 'ata_documento_id');
    }

    public function membros(): HasMany
    {
        return $this->hasMany(MembroBanca::class);
    }

    public function presidente()
    {
        return $this->hasOne(MembroBanca::class)->where('tipo_participacao', 'PRESIDENTE');
    }

    public function orientador()
    {
        return $this->hasOne(MembroBanca::class)->where('tipo_participacao', 'ORIENTADOR');
    }

    public function examinadores()
    {
        return $this->hasMany(MembroBanca::class)
            ->whereIn('tipo_participacao', ['EXAMINADOR_INTERNO', 'EXAMINADOR_EXTERNO']);
    }

    public function suplentes()
    {
        return $this->hasMany(MembroBanca::class)->where('tipo_participacao', 'SUPLENTE');
    }

    public function avaliacoes(): HasMany
    {
        return $this->hasMany(Avaliacao::class);
    }

    // ==================== SCOPES ====================

    public function scopePorTcc($query, string $tccId)
    {
        return $query->where('tcc_id', $tccId);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_banca', $tipo);
    }

    public function scopePorStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAgendadas($query)
    {
        return $query->where('status', 'AGENDADA');
    }

    public function scopeConcluidas($query)
    {
        return $query->where('status', 'CONCLUIDA');
    }

    public function scopeProximas($query)
    {
        return $query->whereIn('status', ['AGENDADA', 'CONFIRMADA'])
            ->where('data_agendada', '>=', now())
            ->orderBy('data_agendada');
    }

    // ==================== METHODS ====================

    public function confirmar(): void
    {
        $this->update(['status' => 'CONFIRMADA']);
    }

    public function iniciar(): void
    {
        $this->update(['status' => 'EM_ANDAMENTO']);
    }

    public function concluir(): void
    {
        $this->update(['status' => 'CONCLUIDA']);
    }

    public function cancelar(): void
    {
        $this->update(['status' => 'CANCELADA']);
    }

    public function reagendar(\DateTime $novaData): void
    {
        $this->update([
            'data_agendada' => $novaData,
            'status' => 'REAGENDADA',
        ]);
    }

    public function isAgendada(): bool
    {
        return $this->status === 'AGENDADA';
    }

    public function isConfirmada(): bool
    {
        return $this->status === 'CONFIRMADA';
    }

    public function isEmAndamento(): bool
    {
        return $this->status === 'EM_ANDAMENTO';
    }

    public function isConcluida(): bool
    {
        return $this->status === 'CONCLUIDA';
    }

    public function isCancelada(): bool
    {
        return $this->status === 'CANCELADA';
    }

    public function isPresencial(): bool
    {
        return $this->formato === 'PRESENCIAL';
    }

    public function isRemota(): bool
    {
        return $this->formato === 'REMOTA';
    }

    public function isHibrida(): bool
    {
        return $this->formato === 'HIBRIDA';
    }

    public function getTipoFormatado(): string
    {
        return match ($this->tipo_banca) {
            'QUALIFICACAO' => 'Qualificação',
            'DEFESA_FINAL' => 'Defesa Final',
            default => $this->tipo_banca,
        };
    }

    public function getFormatoFormatado(): string
    {
        return match ($this->formato) {
            'PRESENCIAL' => 'Presencial',
            'REMOTA' => 'Remota',
            'HIBRIDA' => 'Híbrida',
            default => $this->formato,
        };
    }

    public function getStatusBadge(): string
    {
        return match ($this->status) {
            'AGENDADA' => '<span class="badge bg-info">Agendada</span>',
            'CONFIRMADA' => '<span class="badge bg-primary">Confirmada</span>',
            'EM_ANDAMENTO' => '<span class="badge bg-warning">Em Andamento</span>',
            'CONCLUIDA' => '<span class="badge bg-success">Concluída</span>',
            'CANCELADA' => '<span class="badge bg-danger">Cancelada</span>',
            'REAGENDADA' => '<span class="badge bg-secondary">Reagendada</span>',
            default => '<span class="badge bg-light">' . $this->status . '</span>',
        };
    }

    public function getTotalMembros(): int
    {
        return $this->membros()->count();
    }

    public function getTotalMembrosConfirmados(): int
    {
        return $this->membros()->where('confirmado', true)->count();
    }

    public function getTotalMembrosPresentes(): int
    {
        return $this->membros()->where('presente', true)->count();
    }

    public function getTotalAvaliacoes(): int
    {
        return $this->avaliacoes()->count();
    }

    public function getMediaNotas(): ?float
    {
        return $this->avaliacoes()->avg('nota');
    }

    public function todosMembrosConfirmados(): bool
    {
        return $this->membros()->where('confirmado', false)->count() === 0;
    }

    public function temQuorumMinimo(): bool
    {
        // Mínimo de 3 membros confirmados (presidente + 2 examinadores)
        return $this->getTotalMembrosConfirmados() >= 3;
    }
}

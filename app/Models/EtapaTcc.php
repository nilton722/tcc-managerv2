<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EtapaTcc extends BaseModel
{
    protected $table = 'etapas_tcc';

    protected $fillable = [
        'cronograma_tcc_id',
        'etapa_template_id',
        'nome',
        'ordem',
        'data_inicio_prevista',
        'data_fim_prevista',
        'data_inicio_real',
        'data_conclusao',
        'status',
        'progresso_percentual',
        'observacoes',
    ];

    protected $casts = [
        'ordem' => 'integer',
        'data_inicio_prevista' => 'date',
        'data_fim_prevista' => 'date',
        'data_inicio_real' => 'date',
        'data_conclusao' => 'date',
        'progresso_percentual' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    public function cronograma(): BelongsTo
    {
        return $this->belongsTo(CronogramaTcc::class, 'cronograma_tcc_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EtapaTemplate::class, 'etapa_template_id');
    }

    // ==================== SCOPES ====================

    public function scopePorCronograma($query, string $cronogramaId)
    {
        return $query->where('cronograma_tcc_id', $cronogramaId);
    }

    public function scopePorStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'PENDENTE');
    }

    public function scopeEmAndamento($query)
    {
        return $query->where('status', 'EM_ANDAMENTO');
    }

    public function scopeConcluidas($query)
    {
        return $query->where('status', 'CONCLUIDA');
    }

    public function scopeAtrasadas($query)
    {
        return $query->where('status', 'ATRASADA');
    }

    public function scopeOrdenadas($query)
    {
        return $query->orderBy('ordem');
    }

    // ==================== METHODS ====================

    public function iniciar(): void
    {
        $this->update([
            'status' => 'EM_ANDAMENTO',
            'data_inicio_real' => now(),
        ]);
    }

    public function concluir(): void
    {
        $this->update([
            'status' => 'CONCLUIDA',
            'data_conclusao' => now(),
            'progresso_percentual' => 100,
        ]);
    }

    public function cancelar(): void
    {
        $this->update([
            'status' => 'CANCELADA',
        ]);
    }

    public function bloquear(): void
    {
        $this->update([
            'status' => 'BLOQUEADA',
        ]);
    }

    public function marcarAtrasada(): void
    {
        $this->update([
            'status' => 'ATRASADA',
        ]);
    }

    public function atualizarProgresso(int $percentual): void
    {
        $this->update([
            'progresso_percentual' => min(100, max(0, $percentual)),
        ]);

        if ($percentual >= 100 && $this->status !== 'CONCLUIDA') {
            $this->concluir();
        }
    }

    public function isPendente(): bool
    {
        return $this->status === 'PENDENTE';
    }

    public function isEmAndamento(): bool
    {
        return $this->status === 'EM_ANDAMENTO';
    }

    public function isConcluida(): bool
    {
        return $this->status === 'CONCLUIDA';
    }

    public function isAtrasada(): bool
    {
        return $this->status === 'ATRASADA';
    }

    public function isBloqueada(): bool
    {
        return $this->status === 'BLOQUEADA';
    }

    public function isCancelada(): bool
    {
        return $this->status === 'CANCELADA';
    }

    public function getStatusFormatado(): string
    {
        return match ($this->status) {
            'PENDENTE' => 'Pendente',
            'EM_ANDAMENTO' => 'Em Andamento',
            'CONCLUIDA' => 'Concluída',
            'ATRASADA' => 'Atrasada',
            'BLOQUEADA' => 'Bloqueada',
            'CANCELADA' => 'Cancelada',
            default => $this->status,
        };
    }

    public function getStatusBadge(): string
    {
        return match ($this->status) {
            'PENDENTE' => '<span class="badge bg-secondary">Pendente</span>',
            'EM_ANDAMENTO' => '<span class="badge bg-primary">Em Andamento</span>',
            'CONCLUIDA' => '<span class="badge bg-success">Concluída</span>',
            'ATRASADA' => '<span class="badge bg-danger">Atrasada</span>',
            'BLOQUEADA' => '<span class="badge bg-warning">Bloqueada</span>',
            'CANCELADA' => '<span class="badge bg-dark">Cancelada</span>',
            default => '<span class="badge bg-light">' . $this->status . '</span>',
        };
    }

    public function getDiasRestantes(): ?int
    {
        if (!$this->data_fim_prevista || $this->isConcluida()) {
            return null;
        }

        return now()->diffInDays($this->data_fim_prevista, false);
    }

    public function getDiasAtraso(): int
    {
        if (!$this->data_fim_prevista || $this->isConcluida()) {
            return 0;
        }

        $diasRestantes = $this->getDiasRestantes();
        return $diasRestantes < 0 ? abs($diasRestantes) : 0;
    }

    public function getDuracaoReal(): ?int
    {
        if (!$this->data_inicio_real) {
            return null;
        }

        $dataFim = $this->data_conclusao ?? now();
        return $this->data_inicio_real->diffInDays($dataFim);
    }

    public function verificarAtraso(): void
    {
        if ($this->isConcluida() || $this->isCancelada() || $this->isBloqueada()) {
            return;
        }

        if ($this->data_fim_prevista && now()->isAfter($this->data_fim_prevista)) {
            $this->marcarAtrasada();
        }
    }
}

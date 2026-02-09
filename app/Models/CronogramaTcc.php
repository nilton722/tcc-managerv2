<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CronogramaTcc extends BaseModel
{
    protected $table = 'cronogramas_tcc';

    protected $fillable = [
        'tcc_id',
        'template_cronograma_id',
        'data_inicio',
        'data_fim_prevista',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim_prevista' => 'date',
    ];

    // ==================== RELATIONSHIPS ====================

    public function tcc(): BelongsTo
    {
        return $this->belongsTo(Tcc::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TemplateCronograma::class, 'template_cronograma_id');
    }

    public function etapas(): HasMany
    {
        return $this->hasMany(EtapaTcc::class);
    }

    // ==================== SCOPES ====================

    public function scopePorTcc($query, string $tccId)
    {
        return $query->where('tcc_id', $tccId);
    }

    public function scopeComAtrasos($query)
    {
        return $query->whereHas('etapas', function ($q) {
            $q->where('status', 'ATRASADA');
        });
    }

    // ==================== METHODS ====================

    public function getTotalEtapas(): int
    {
        return $this->etapas()->count();
    }

    public function getEtapasConcluidas(): int
    {
        return $this->etapas()->where('status', 'CONCLUIDA')->count();
    }

    public function getEtapasEmAndamento(): int
    {
        return $this->etapas()->where('status', 'EM_ANDAMENTO')->count();
    }

    public function getEtapasPendentes(): int
    {
        return $this->etapas()->where('status', 'PENDENTE')->count();
    }

    public function getEtapasAtrasadas(): int
    {
        return $this->etapas()->where('status', 'ATRASADA')->count();
    }

    public function getProgressoPercentual(): float
    {
        $total = $this->getTotalEtapas();

        if ($total === 0) {
            return 0;
        }

        $concluidas = $this->getEtapasConcluidas();

        return round(($concluidas / $total) * 100, 2);
    }

    public function temAtrasos(): bool
    {
        return $this->getEtapasAtrasadas() > 0;
    }

    public function getDiasRestantes(): ?int
    {
        if (!$this->data_fim_prevista) {
            return null;
        }

        return now()->diffInDays($this->data_fim_prevista, false);
    }

    public function isAtrasado(): bool
    {
        $diasRestantes = $this->getDiasRestantes();
        return $diasRestantes !== null && $diasRestantes < 0;
    }

    public function getEtapaAtual(): ?EtapaTcc
    {
        return $this->etapas()
            ->where('status', 'EM_ANDAMENTO')
            ->orderBy('ordem')
            ->first();
    }

    public function getProximaEtapa(): ?EtapaTcc
    {
        $etapaAtual = $this->getEtapaAtual();

        if (!$etapaAtual) {
            return $this->etapas()
                ->where('status', 'PENDENTE')
                ->orderBy('ordem')
                ->first();
        }

        return $this->etapas()
            ->where('ordem', '>', $etapaAtual->ordem)
            ->where('status', 'PENDENTE')
            ->orderBy('ordem')
            ->first();
    }

    public function atualizarDatasEtapas(): void
    {
        $dataBase = $this->data_inicio;

        foreach ($this->etapas()->ordenadas()->get() as $etapa) {
            $etapa->update([
                'data_inicio_prevista' => $dataBase,
                'data_fim_prevista' => $dataBase->copy()->addDays($etapa->ordem === 1 ? 
                    $this->template->etapas()->where('ordem', $etapa->ordem)->first()->duracao_dias : 
                    0
                ),
            ]);

            $dataBase = $etapa->data_fim_prevista->copy()->addDay();
        }
    }
}

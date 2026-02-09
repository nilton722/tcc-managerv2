<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CronogramaTccResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'data_inicio' => $this->data_inicio?->format('Y-m-d'),
            'data_fim_prevista' => $this->data_fim_prevista?->format('Y-m-d'),
            'dias_restantes' => $this->getDiasRestantes(),
            'atrasado' => $this->isAtrasado(),
            'total_etapas' => $this->getTotalEtapas(),
            'etapas_concluidas' => $this->getEtapasConcluidas(),
            'etapas_atrasadas' => $this->getEtapasAtrasadas(),
            'progresso_percentual' => $this->getProgressoPercentual(),
            'template' => $this->whenLoaded('template'),
            'etapas' => $this->whenLoaded('etapas'),
        ];
    }
}
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BancaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'tipo_banca' => $this->tipo_banca,
            'tipo_formatado' => $this->getTipoFormatado(),
            'data_agendada' => $this->data_agendada?->toIso8601String(),
            'local' => $this->local,
            'formato' => $this->formato,
            'formato_formatado' => $this->getFormatoFormatado(),
            'link_reuniao' => $this->link_reuniao,
            'status' => $this->status,
            'status_badge' => $this->getStatusBadge(),
            'total_membros' => $this->getTotalMembros(),
            'membros_confirmados' => $this->getTotalMembrosConfirmados(),
            'tem_quorum' => $this->temQuorumMinimo(),
            'media_notas' => $this->getMediaNotas(),
            'membros' => $this->whenLoaded('membros'),
            'avaliacoes' => $this->whenLoaded('avaliacoes'),
        ];
    }
}
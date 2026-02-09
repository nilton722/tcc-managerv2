<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrientadorResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'usuario' => [
                'id' => $this->usuario->id,
                'nome_completo' => $this->usuario->nome_completo,
                'email' => $this->usuario->email,
            ],
            'departamento' => $this->whenLoaded('departamento'),
            'titulacao' => $this->titulacao,
            'titulacao_formatada' => $this->getTitulacaoFormatada(),
            'areas_atuacao' => $this->areas_atuacao,
            'lattes_url' => $this->lattes_url,
            'orcid' => $this->orcid,
            'max_orientandos' => $this->max_orientandos,
            'orientandos_atuais' => $this->orientandos_atuais,
            'vagas_disponiveis' => $this->getVagasDisponiveis(),
            'taxa_ocupacao' => $this->getTaxaOcupacao(),
            'aceita_coorientacao' => $this->aceita_coorientacao,
            'ativo' => $this->ativo,
        ];
    }
}
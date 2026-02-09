<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LinhaPesquisaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'area_conhecimento' => $this->area_conhecimento,
            'palavras_chave' => $this->palavras_chave,
            'ativo' => $this->ativo,
            'curso' => $this->whenLoaded('curso'),
        ];
    }
}
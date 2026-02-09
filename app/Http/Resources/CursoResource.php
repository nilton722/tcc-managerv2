<?php


namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CursoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'codigo' => $this->codigo,
            'nivel' => $this->nivel,
            'nivel_formatado' => $this->getNivelFormatado(),
            'duracao_semestres' => $this->duracao_semestres,
            'ativo' => $this->ativo,
            'departamento' => $this->whenLoaded('departamento'),
            'coordenador' => $this->whenLoaded('coordenador'),
            'linhas_pesquisa' => $this->whenLoaded('linhasPesquisa'),
        ];
    }
}
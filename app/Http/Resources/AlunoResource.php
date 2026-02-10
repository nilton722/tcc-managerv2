<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class AlunoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'usuario' => [
                'id' => $this->usuario->id,
                'nome_completo' => $this->usuario->nome_completo,
                'email' => $this->usuario->email,
                'bi' => $this->usuario->bi,
                'telefone' => $this->usuario->telefone,
                'status' => $this->usuario->status,
            ],
            'curso' => $this->whenLoaded('curso', function () {
                return [
                    'id' => $this->curso->id,
                    'nome' => $this->curso->nome,
                    'codigo' => $this->curso->codigo,
                    'nivel' => $this->curso->nivel,
                ];
            }),
            'matricula' => $this->matricula,
            'data_ingresso' => $this->data_ingresso?->format('Y-m-d'),
            'data_prevista_conclusao' => $this->data_prevista_conclusao?->format('Y-m-d'),
            'lattes_url' => $this->lattes_url,
            'orcid' => $this->orcid,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrientacaoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'tipo_orientacao' => $this->tipo_orientacao,
            'tipo_formatado' => $this->getTipoFormatado(),
            'data_inicio' => $this->data_inicio?->format('Y-m-d'),
            'data_fim' => $this->data_fim?->format('Y-m-d'),
            'duracao_meses' => $this->getDuracaoEmMeses(),
            'ativo' => $this->ativo,
            'orientador' => $this->whenLoaded('orientador', function () {
                return [
                    'id' => $this->orientador->id,
                    'nome' => $this->orientador->usuario->nome_completo,
                    'email' => $this->orientador->usuario->email,
                    'titulacao' => $this->orientador->getTitulacaoFormatada(),
                    'areas_atuacao' => $this->orientador->areas_atuacao,
                ];
            }),
            'tcc' => $this->whenLoaded('tcc'),
        ];
    }
}
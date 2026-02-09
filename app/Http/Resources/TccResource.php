<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TccResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'titulo_ingles' => $this->titulo_ingles,
            'tipo_trabalho' => $this->tipo_trabalho,
            'status' => $this->status,
            'status_badge' => $this->getStatusBadge(),
            
            // Resumo
            'resumo' => $this->resumo,
            'abstract' => $this->abstract,
            'palavras_chave' => $this->palavras_chave,
            'keywords' => $this->keywords,
            
            // Datas
            'data_inicio' => $this->data_inicio?->format('Y-m-d'),
            'data_qualificacao' => $this->data_qualificacao?->format('Y-m-d'),
            'data_defesa' => $this->data_defesa?->format('Y-m-d'),
            'data_entrega_final' => $this->data_entrega_final?->format('Y-m-d'),
            
            // Nota
            'nota_final' => $this->nota_final,
            
            // Metadata
            'metadata' => $this->metadata,
            
            // Informações calculadas
            'progresso_geral' => $this->when(
                $this->relationLoaded('cronograma'),
                fn() => $this->calcularProgresso()
            ),
            'dias_restantes' => $this->getDiasRestantes(),
            'pode_editar' => $this->podeEditar(),
            'pode_aprovar' => $this->podeAprovar(),
            'is_aprovado' => $this->isAprovado(),
            'tem_orientador' => $this->temOrientador(),
            
            // Relacionamentos
            'aluno' => new AlunoResource($this->whenLoaded('aluno')),
            'curso' => new CursoResource($this->whenLoaded('curso')),
            'linha_pesquisa' => new LinhaPesquisaResource($this->whenLoaded('linhaPesquisa')),
            
            'orientacoes' => OrientacaoResource::collection($this->whenLoaded('orientacoes')),
            'orientador' => new OrientadorResource($this->whenLoaded('orientador')),
            'coorientadores' => OrientadorResource::collection($this->whenLoaded('coorientadores')),
            
            'documentos' => DocumentoResource::collection($this->whenLoaded('documentos')),
            'bancas' => BancaResource::collection($this->whenLoaded('bancas')),
            'cronograma' => new CronogramaTccResource($this->whenLoaded('cronograma')),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
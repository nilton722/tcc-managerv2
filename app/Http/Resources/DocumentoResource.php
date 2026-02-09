<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DocumentoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nome_arquivo' => $this->nome_arquivo,
            'tamanho_bytes' => $this->tamanho_bytes,
            'tamanho_formatado' => $this->getTamanhoFormatado(),
            'mime_type' => $this->mime_type,
            'versao' => $this->versao,
            'status' => $this->status,
            'status_badge' => $this->getStatusBadge(),
            'comentarios' => $this->comentarios,
            'upload_em' => $this->upload_em?->toIso8601String(),
            'tipo_documento' => $this->whenLoaded('tipoDocumento'),
            'upload_por' => $this->whenLoaded('uploadPor', function () {
                return ['id' => $this->uploadPor->id, 'nome' => $this->uploadPor->nome_completo];
            }),
            'download_url' => route('documentos.download', $this->id),
        ];
    }
}
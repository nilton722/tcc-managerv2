<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Documento extends BaseModel
{
    protected $table = 'documentos';

    protected $fillable = [
        'tcc_id',
        'tipo_documento_id',
        'versao_anterior_id',
        'upload_por',
        'nome_arquivo',
        'arquivo_url',
        'tamanho_bytes',
        'hash_arquivo',
        'mime_type',
        'versao',
        'status',
        'comentarios',
        'upload_em',
    ];

    protected $casts = [
        'tamanho_bytes' => 'integer',
        'versao' => 'integer',
        'upload_em' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function tcc(): BelongsTo
    {
        return $this->belongsTo(Tcc::class);
    }

    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class);
    }

    public function versaoAnterior(): BelongsTo
    {
        return $this->belongsTo(Documento::class, 'versao_anterior_id');
    }

    public function proximasVersoes()
    {
        return $this->hasMany(Documento::class, 'versao_anterior_id');
    }

    public function uploadPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'upload_por');
    }

    // ==================== SCOPES ====================

    public function scopePorTcc($query, string $tccId)
    {
        return $query->where('tcc_id', $tccId);
    }

    public function scopePorTipo($query, string $tipoDocumentoId)
    {
        return $query->where('tipo_documento_id', $tipoDocumentoId);
    }

    public function scopePorStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAprovados($query)
    {
        return $query->where('status', 'APROVADO');
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'PENDENTE');
    }

    public function scopeRejeitados($query)
    {
        return $query->where('status', 'REJEITADO');
    }

    public function scopeUltimaVersao($query)
    {
        return $query->whereDoesntHave('proximasVersoes');
    }

    // ==================== METHODS ====================

    public function aprovar(string $comentarios = null): void
    {
        $this->update([
            'status' => 'APROVADO',
            'comentarios' => $comentarios,
        ]);
    }

    public function rejeitar(string $comentarios): void
    {
        $this->update([
            'status' => 'REJEITADO',
            'comentarios' => $comentarios,
        ]);
    }

    public function solicitarRevisao(string $comentarios): void
    {
        $this->update([
            'status' => 'REVISAO',
            'comentarios' => $comentarios,
        ]);
    }

    public function isPendente(): bool
    {
        return $this->status === 'PENDENTE';
    }

    public function isAprovado(): bool
    {
        return $this->status === 'APROVADO';
    }

    public function isRejeitado(): bool
    {
        return $this->status === 'REJEITADO';
    }

    public function isEmRevisao(): bool
    {
        return $this->status === 'REVISAO';
    }

    public function getTamanhoFormatado(): string
    {
        $bytes = $this->tamanho_bytes;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function getDownloadUrl(): string
    {
        return Storage::url($this->arquivo_url);
    }

    public function getStatusBadge(): string
    {
        return match ($this->status) {
            'PENDENTE' => '<span class="badge bg-warning">Pendente</span>',
            'APROVADO' => '<span class="badge bg-success">Aprovado</span>',
            'REJEITADO' => '<span class="badge bg-danger">Rejeitado</span>',
            'REVISAO' => '<span class="badge bg-info">Em Revisão</span>',
            default => '<span class="badge bg-secondary">' . $this->status . '</span>',
        };
    }

    public function verificarIntegridade(): bool
    {
        if (!Storage::exists($this->arquivo_url)) {
            return false;
        }

        $hashAtual = hash_file('sha256', Storage::path($this->arquivo_url));
        return $hashAtual === $this->hash_arquivo;
    }
}

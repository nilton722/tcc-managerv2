<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembroBanca extends BaseModel
{
    protected $table = 'membros_banca';

    protected $fillable = [
        'banca_id',
        'usuario_id',
        'tipo_participacao',
        'instituicao_externa',
        'confirmado',
        'presente',
    ];

    protected $casts = [
        'confirmado' => 'boolean',
        'presente' => 'boolean',
    ];

    // ==================== RELATIONSHIPS ====================

    public function banca(): BelongsTo
    {
        return $this->belongsTo(Banca::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function avaliacao()
    {
        return $this->hasOne(Avaliacao::class, 'membro_banca_id');
    }

    // ==================== SCOPES ====================

    public function scopePorBanca($query, string $bancaId)
    {
        return $query->where('banca_id', $bancaId);
    }

    public function scopePorUsuario($query, string $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_participacao', $tipo);
    }

    public function scopeConfirmados($query)
    {
        return $query->where('confirmado', true);
    }

    public function scopePresentes($query)
    {
        return $query->where('presente', true);
    }

    public function scopeExaminadores($query)
    {
        return $query->whereIn('tipo_participacao', ['EXAMINADOR_INTERNO', 'EXAMINADOR_EXTERNO']);
    }

    // ==================== METHODS ====================

    public function confirmar(): void
    {
        $this->update(['confirmado' => true]);
    }

    public function recusar(): void
    {
        $this->update(['confirmado' => false]);
    }

    public function marcarPresenca(): void
    {
        $this->update(['presente' => true]);
    }

    public function marcarFalta(): void
    {
        $this->update(['presente' => false]);
    }

    public function isPresidente(): bool
    {
        return $this->tipo_participacao === 'PRESIDENTE';
    }

    public function isOrientador(): bool
    {
        return $this->tipo_participacao === 'ORIENTADOR';
    }

    public function isExaminadorInterno(): bool
    {
        return $this->tipo_participacao === 'EXAMINADOR_INTERNO';
    }

    public function isExaminadorExterno(): bool
    {
        return $this->tipo_participacao === 'EXAMINADOR_EXTERNO';
    }

    public function isSuplente(): bool
    {
        return $this->tipo_participacao === 'SUPLENTE';
    }

    public function isExterno(): bool
    {
        return !empty($this->instituicao_externa);
    }

    public function getTipoFormatado(): string
    {
        return match ($this->tipo_participacao) {
            'PRESIDENTE' => 'Presidente',
            'ORIENTADOR' => 'Orientador',
            'EXAMINADOR_INTERNO' => 'Examinador Interno',
            'EXAMINADOR_EXTERNO' => 'Examinador Externo',
            'SUPLENTE' => 'Suplente',
            default => $this->tipo_participacao,
        };
    }

    public function getInstituicao(): string
    {
        if ($this->isExterno()) {
            return $this->instituicao_externa;
        }

        return $this->usuario->instituicao->nome ?? 'Não informada';
    }
}

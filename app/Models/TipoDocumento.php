<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoDocumento extends BaseModel
{
    protected $table = 'tipos_documento';

    protected $fillable = [
        'nome',
        'descricao',
        'extensoes_permitidas',
        'tamanho_maximo_mb',
        'obrigatorio',
        'ordem_exibicao',
        'ativo',
    ];

    protected $casts = [
        'extensoes_permitidas' => 'array',
        'tamanho_maximo_mb' => 'integer',
        'obrigatorio' => 'boolean',
        'ordem_exibicao' => 'integer',
        'ativo' => 'boolean',
    ];

    // ==================== RELATIONSHIPS ====================

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    // ==================== SCOPES ====================

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeObrigatorios($query)
    {
        return $query->where('obrigatorio', true);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem_exibicao');
    }

    // ==================== METHODS ====================

    public function ativar(): void
    {
        $this->update(['ativo' => true]);
    }

    public function desativar(): void
    {
        $this->update(['ativo' => false]);
    }

    public function isExtensaoPermitida(string $extensao): bool
    {
        if (empty($this->extensoes_permitidas)) {
            return true;
        }

        return in_array(strtolower($extensao), array_map('strtolower', $this->extensoes_permitidas));
    }

    public function isTamanhoPermitido(int $tamanhoBytes): bool
    {
        $tamanhoMb = $tamanhoBytes / (1024 * 1024);
        return $tamanhoMb <= $this->tamanho_maximo_mb;
    }

    public function getExtensoesFormatadas(): string
    {
        if (empty($this->extensoes_permitidas)) {
            return 'Qualquer formato';
        }

        return implode(', ', $this->extensoes_permitidas);
    }

    public function getTamanhoMaximoFormatado(): string
    {
        return $this->tamanho_maximo_mb . ' MB';
    }
}

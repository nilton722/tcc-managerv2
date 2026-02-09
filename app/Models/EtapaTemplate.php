<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EtapaTemplate extends BaseModel
{
    protected $table = 'etapas_template';

    protected $fillable = [
        'template_cronograma_id',
        'nome',
        'descricao',
        'ordem',
        'duracao_dias',
        'obrigatoria',
        'documentos_exigidos',
    ];

    protected $casts = [
        'ordem' => 'integer',
        'duracao_dias' => 'integer',
        'obrigatoria' => 'boolean',
        'documentos_exigidos' => 'array',
    ];

    // ==================== RELATIONSHIPS ====================

    public function template(): BelongsTo
    {
        return $this->belongsTo(TemplateCronograma::class, 'template_cronograma_id');
    }

    // ==================== SCOPES ====================

    public function scopePorTemplate($query, string $templateId)
    {
        return $query->where('template_cronograma_id', $templateId);
    }

    public function scopeObrigatorias($query)
    {
        return $query->where('obrigatoria', true);
    }

    public function scopeOrdenadas($query)
    {
        return $query->orderBy('ordem');
    }

    // ==================== METHODS ====================

    public function getDocumentosExigidosFormatados(): string
    {
        if (empty($this->documentos_exigidos)) {
            return 'Nenhum documento exigido';
        }

        return implode(', ', $this->documentos_exigidos);
    }

    public function getDuracaoFormatada(): string
    {
        if ($this->duracao_dias === 1) {
            return '1 dia';
        }

        if ($this->duracao_dias < 7) {
            return $this->duracao_dias . ' dias';
        }

        $semanas = floor($this->duracao_dias / 7);
        $dias = $this->duracao_dias % 7;

        $texto = $semanas . ($semanas === 1 ? ' semana' : ' semanas');

        if ($dias > 0) {
            $texto .= ' e ' . $dias . ($dias === 1 ? ' dia' : ' dias');
        }

        return $texto;
    }
}

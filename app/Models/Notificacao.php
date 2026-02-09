<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacao extends BaseModel
{
    protected $table = 'notificacoes';

    protected $fillable = [
        'usuario_id',
        'tipo',
        'titulo',
        'mensagem',
        'link_referencia',
        'entidade_tipo',
        'entidade_id',
        'lida',
        'data_leitura',
        'canal',
        'enviado',
    ];

    protected $casts = [
        'lida' => 'boolean',
        'data_leitura' => 'datetime',
        'enviado' => 'boolean',
    ];

    // ==================== RELATIONSHIPS ====================

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    // ==================== SCOPES ====================

    public function scopePorUsuario($query, string $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeLidas($query)
    {
        return $query->where('lida', true);
    }

    public function scopeNaoLidas($query)
    {
        return $query->where('lida', false);
    }

    public function scopeEnviadas($query)
    {
        return $query->where('enviado', true);
    }

    public function scopePendentes($query)
    {
        return $query->where('enviado', false);
    }

    public function scopeRecentes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopePorCanal($query, string $canal)
    {
        return $query->where('canal', $canal);
    }

    // ==================== METHODS ====================

    public function marcarComoLida(): void
    {
        if (!$this->lida) {
            $this->update([
                'lida' => true,
                'data_leitura' => now(),
            ]);
        }
    }

    public function marcarComoNaoLida(): void
    {
        $this->update([
            'lida' => false,
            'data_leitura' => null,
        ]);
    }

    public function marcarComoEnviada(): void
    {
        $this->update(['enviado' => true]);
    }

    public function isLida(): bool
    {
        return $this->lida === true;
    }

    public function isEnviada(): bool
    {
        return $this->enviado === true;
    }

    public function getTipoFormatado(): string
    {
        return match ($this->tipo) {
            'INFO' => 'Informação',
            'ALERTA' => 'Alerta',
            'PRAZO' => 'Prazo',
            'APROVACAO' => 'Aprovação',
            'REJEICAO' => 'Rejeição',
            'CONVITE' => 'Convite',
            'LEMBRETE' => 'Lembrete',
            default => $this->tipo,
        };
    }

    public function getCanalFormatado(): string
    {
        return match ($this->canal) {
            'SISTEMA' => 'Sistema',
            'EMAIL' => 'E-mail',
            'SMS' => 'SMS',
            'PUSH' => 'Push Notification',
            default => $this->canal,
        };
    }

    public function getTipoIcone(): string
    {
        return match ($this->tipo) {
            'INFO' => 'info-circle',
            'ALERTA' => 'exclamation-triangle',
            'PRAZO' => 'clock',
            'APROVACAO' => 'check-circle',
            'REJEICAO' => 'times-circle',
            'CONVITE' => 'envelope',
            'LEMBRETE' => 'bell',
            default => 'circle',
        };
    }

    public function getTipoCor(): string
    {
        return match ($this->tipo) {
            'INFO' => 'info',
            'ALERTA' => 'warning',
            'PRAZO' => 'warning',
            'APROVACAO' => 'success',
            'REJEICAO' => 'danger',
            'CONVITE' => 'primary',
            'LEMBRETE' => 'secondary',
            default => 'light',
        };
    }

    public function getTempoDecorrido(): string
    {
        $diff = now()->diffInSeconds($this->created_at);

        if ($diff < 60) {
            return 'agora mesmo';
        }

        if ($diff < 3600) {
            $minutos = floor($diff / 60);
            return $minutos . ' min atrás';
        }

        if ($diff < 86400) {
            $horas = floor($diff / 3600);
            return $horas . ($horas === 1 ? ' hora atrás' : ' horas atrás');
        }

        if ($diff < 604800) {
            $dias = floor($diff / 86400);
            return $dias . ($dias === 1 ? ' dia atrás' : ' dias atrás');
        }

        return $this->created_at->format('d/m/Y H:i');
    }
}

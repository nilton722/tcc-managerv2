<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends BaseModel
{
    protected $table = 'auditorias';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'acao',
        'entidade',
        'entidade_id',
        'dados_anteriores',
        'dados_novos',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'dados_anteriores' => 'array',
        'dados_novos' => 'array',
        'created_at' => 'datetime',
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

    public function scopePorAcao($query, string $acao)
    {
        return $query->where('acao', $acao);
    }

    public function scopePorEntidade($query, string $entidade)
    {
        return $query->where('entidade', $entidade);
    }

    public function scopePorEntidadeId($query, string $entidadeId)
    {
        return $query->where('entidade_id', $entidadeId);
    }

    public function scopeRecentes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopePorPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('created_at', [$dataInicio, $dataFim]);
    }

    public function scopeAcoesCreate($query)
    {
        return $query->where('acao', 'CREATE');
    }

    public function scopeAcoesUpdate($query)
    {
        return $query->where('acao', 'UPDATE');
    }

    public function scopeAcoesDelete($query)
    {
        return $query->where('acao', 'DELETE');
    }

    // ==================== METHODS ====================

    public function getAcaoFormatada(): string
    {
        return match ($this->acao) {
            'CREATE' => 'Criou',
            'UPDATE' => 'Atualizou',
            'DELETE' => 'Excluiu',
            'RESTORE' => 'Restaurou',
            'LOGIN' => 'Fez login',
            'LOGOUT' => 'Fez logout',
            'APPROVE' => 'Aprovou',
            'REJECT' => 'Rejeitou',
            'SUBMIT' => 'Submeteu',
            'CANCEL' => 'Cancelou',
            default => $this->acao,
        };
    }

    public function getEntidadeFormatada(): string
    {
        return match ($this->entidade) {
            'Tcc' => 'TCC',
            'Usuario' => 'Usuário',
            'Documento' => 'Documento',
            'Banca' => 'Banca',
            'Orientacao' => 'Orientação',
            'Avaliacao' => 'Avaliação',
            default => $this->entidade,
        };
    }

    public function getDescricao(): string
    {
        return sprintf(
            '%s %s #%s',
            $this->getAcaoFormatada(),
            $this->getEntidadeFormatada(),
            substr($this->entidade_id, 0, 8)
        );
    }

    public function getAlteracoes(): array
    {
        if (empty($this->dados_anteriores) || empty($this->dados_novos)) {
            return [];
        }

        $alteracoes = [];

        foreach ($this->dados_novos as $campo => $valorNovo) {
            $valorAntigo = $this->dados_anteriores[$campo] ?? null;

            if ($valorAntigo !== $valorNovo) {
                $alteracoes[$campo] = [
                    'anterior' => $valorAntigo,
                    'novo' => $valorNovo,
                ];
            }
        }

        return $alteracoes;
    }

    public function getAlteracoesFormatadas(): string
    {
        $alteracoes = $this->getAlteracoes();

        if (empty($alteracoes)) {
            return 'Nenhuma alteração registrada';
        }

        $texto = [];

        foreach ($alteracoes as $campo => $valores) {
            $texto[] = sprintf(
                '%s: "%s" → "%s"',
                ucfirst($campo),
                $valores['anterior'] ?? 'vazio',
                $valores['novo'] ?? 'vazio'
            );
        }

        return implode('; ', $texto);
    }

    public function getTempoDecorrido(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getNavegadorInfo(): array
    {
        $userAgent = $this->user_agent;

        return [
            'navegador' => $this->detectarNavegador($userAgent),
            'sistema' => $this->detectarSistema($userAgent),
            'dispositivo' => $this->detectarDispositivo($userAgent),
        ];
    }

    private function detectarNavegador(string $userAgent): string
    {
        if (str_contains($userAgent, 'Chrome')) return 'Chrome';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Safari')) return 'Safari';
        if (str_contains($userAgent, 'Edge')) return 'Edge';
        if (str_contains($userAgent, 'Opera')) return 'Opera';
        return 'Desconhecido';
    }

    private function detectarSistema(string $userAgent): string
    {
        if (str_contains($userAgent, 'Windows')) return 'Windows';
        if (str_contains($userAgent, 'Mac')) return 'MacOS';
        if (str_contains($userAgent, 'Linux')) return 'Linux';
        if (str_contains($userAgent, 'Android')) return 'Android';
        if (str_contains($userAgent, 'iOS')) return 'iOS';
        return 'Desconhecido';
    }

    private function detectarDispositivo(string $userAgent): string
    {
        if (str_contains($userAgent, 'Mobile')) return 'Mobile';
        if (str_contains($userAgent, 'Tablet')) return 'Tablet';
        return 'Desktop';
    }

    /**
     * Registrar uma auditoria
     */
    public static function registrar(
        string $acao,
        string $entidade,
        string $entidadeId,
        ?array $dadosAnteriores = null,
        ?array $dadosNovos = null
    ): self {
        return self::create([
            'usuario_id' => auth()->id(),
            'acao' => $acao,
            'entidade' => $entidade,
            'entidade_id' => $entidadeId,
            'dados_anteriores' => $dadosAnteriores,
            'dados_novos' => $dadosNovos,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}

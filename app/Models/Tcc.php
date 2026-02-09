<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Tcc extends BaseModel
{
    protected $table = 'tccs';

    protected $fillable = [
        'aluno_id',
        'curso_id',
        'linha_pesquisa_id',
        'titulo',
        'titulo_ingles',
        'tipo_trabalho',
        'resumo',
        'abstract',
        'palavras_chave',
        'keywords',
        'status',
        'data_inicio',
        'data_qualificacao',
        'data_defesa',
        'data_entrega_final',
        'nota_final',
        'metadata',
    ];

    protected $casts = [
        'palavras_chave' => 'array',
        'keywords' => 'array',
        'metadata' => 'array',
        'data_inicio' => 'date',
        'data_qualificacao' => 'date',
        'data_defesa' => 'date',
        'data_entrega_final' => 'date',
        'nota_final' => 'decimal:2',
    ];

    // Relacionamentos
    public function aluno()
    {
        return $this->belongsTo(Aluno::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function linhaPesquisa()
    {
        return $this->belongsTo(LinhaPesquisa::class);
    }

    public function orientacoes()
    {
        return $this->hasMany(Orientacao::class);
    }

    public function orientador()
    {
        return $this->hasOneThrough(
            Orientador::class,
            Orientacao::class,
            'tcc_id',
            'id',
            'id',
            'orientador_id'
        )->where('orientacoes.tipo_orientacao', 'ORIENTADOR')
         ->where('orientacoes.ativo', true);
    }

    public function coorientadores()
    {
        return $this->hasManyThrough(
            Orientador::class,
            Orientacao::class,
            'tcc_id',
            'id',
            'id',
            'orientador_id'
        )->where('orientacoes.tipo_orientacao', 'COORIENTADOR')
         ->where('orientacoes.ativo', true);
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }

    public function bancas()
    {
        return $this->hasMany(Banca::class);
    }

    public function cronograma()
    {
        return $this->hasOne(CronogramaTcc::class);
    }

    // Scopes
    public function scopePorStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeEmAndamento($query)
    {
        return $query->whereIn('status', [
            'EM_ORIENTACAO',
            'AGUARDANDO_BANCA',
            'BANCA_AGENDADA',
            'EM_AVALIACAO'
        ]);
    }

    public function scopeConcluidos($query)
    {
        return $query->whereIn('status', [
            'APROVADO',
            'APROVADO_COM_RESSALVAS'
        ]);
    }

    public function scopePorCurso($query, string $cursoId)
    {
        return $query->where('curso_id', $cursoId);
    }

    public function scopePorAluno($query, string $alunoId)
    {
        return $query->where('aluno_id', $alunoId);
    }

    public function scopePorOrientador($query, string $orientadorId)
    {
        return $query->whereHas('orientacoes', function (Builder $q) use ($orientadorId) {
            $q->where('orientador_id', $orientadorId)
              ->where('ativo', true);
        });
    }

    public function scopeComAtrasos($query)
    {
        return $query->whereHas('cronograma.etapas', function (Builder $q) {
            $q->where('status', 'ATRASADA');
        });
    }

    public function scopeBuscarTitulo($query, string $termo)
    {
        return $query->where('titulo', 'LIKE', "%{$termo}%")
                     ->orWhere('titulo_ingles', 'LIKE', "%{$termo}%");
    }

    // Methods
    public function podeEditar(): bool
    {
        return in_array($this->status, ['RASCUNHO', 'EM_ORIENTACAO']);
    }

    public function podeAprovar(): bool
    {
        return in_array($this->status, ['EM_AVALIACAO']);
    }

    public function isAprovado(): bool
    {
        return in_array($this->status, ['APROVADO', 'APROVADO_COM_RESSALVAS']);
    }

    public function isCancelado(): bool
    {
        return $this->status === 'CANCELADO';
    }

    public function temOrientador(): bool
    {
        return $this->orientacoes()
            ->where('tipo_orientacao', 'ORIENTADOR')
            ->where('ativo', true)
            ->exists();
    }

    public function iniciarOrientacao(): void
    {
        $this->update([
            'status' => 'EM_ORIENTACAO',
            'data_inicio' => now(),
        ]);
    }

    public function submeterParaBanca(): void
    {
        if (!$this->temOrientador()) {
            throw new \Exception('TCC precisa ter um orientador antes da submissão.');
        }

        $this->update(['status' => 'AGUARDANDO_BANCA']);
    }

    public function agendarBanca(): void
    {
        $this->update(['status' => 'BANCA_AGENDADA']);
    }

    public function iniciarAvaliacao(): void
    {
        $this->update(['status' => 'EM_AVALIACAO']);
    }

    public function aprovar(float $nota, bool $comRessalvas = false): void
    {
        $this->update([
            'status' => $comRessalvas ? 'APROVADO_COM_RESSALVAS' : 'APROVADO',
            'nota_final' => $nota,
            'data_entrega_final' => now(),
        ]);
    }

    public function reprovar(float $nota): void
    {
        $this->update([
            'status' => 'REPROVADO',
            'nota_final' => $nota,
        ]);
    }

    public function cancelar(string $motivo = null): void
    {
        $metadata = $this->metadata ?? [];
        $metadata['motivo_cancelamento'] = $motivo;
        $metadata['data_cancelamento'] = now()->toDateTimeString();

        $this->update([
            'status' => 'CANCELADO',
            'metadata' => $metadata,
        ]);
    }

    public function calcularProgresso(): int
    {
        if (!$this->cronograma) {
            return 0;
        }

        $etapas = $this->cronograma->etapas;
        if ($etapas->isEmpty()) {
            return 0;
        }

        $totalProgresso = $etapas->sum('progresso_percentual');
        return (int) ($totalProgresso / $etapas->count());
    }

    public function getDiasRestantes(): ?int
    {
        if (!$this->data_defesa) {
            return null;
        }

        return now()->diffInDays($this->data_defesa, false);
    }

    public function getStatusBadge(): array
    {
        $badges = [
            'RASCUNHO' => ['color' => 'gray', 'label' => 'Rascunho'],
            'EM_ORIENTACAO' => ['color' => 'blue', 'label' => 'Em Orientação'],
            'AGUARDANDO_BANCA' => ['color' => 'yellow', 'label' => 'Aguardando Banca'],
            'BANCA_AGENDADA' => ['color' => 'orange', 'label' => 'Banca Agendada'],
            'EM_AVALIACAO' => ['color' => 'purple', 'label' => 'Em Avaliação'],
            'APROVADO' => ['color' => 'green', 'label' => 'Aprovado'],
            'APROVADO_COM_RESSALVAS' => ['color' => 'lime', 'label' => 'Aprovado c/ Ressalvas'],
            'REPROVADO' => ['color' => 'red', 'label' => 'Reprovado'],
            'CANCELADO' => ['color' => 'dark', 'label' => 'Cancelado'],
        ];

        return $badges[$this->status] ?? ['color' => 'gray', 'label' => $this->status];
    }
}

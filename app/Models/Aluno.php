<?php

namespace App\Models;

class Aluno extends BaseModel
{
    protected $table = 'alunos';

    protected $fillable = [
        'usuario_id',
        'curso_id',
        'matricula',
        'data_ingresso',
        'data_prevista_conclusao',
        'lattes_url',
        'orcid',
    ];

    protected $casts = [
        'data_ingresso' => 'date',
        'data_prevista_conclusao' => 'date',
    ];

    // Relacionamentos
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function tccs()
    {
        return $this->hasMany(Tcc::class);
    }

    public function tccAtivo()
    {
        return $this->hasOne(Tcc::class)
            ->whereNotIn('status', ['APROVADO', 'REPROVADO', 'CANCELADO'])
            ->latest();
    }

    // Scopes
    public function scopePorCurso($query, string $cursoId)
    {
        return $query->where('curso_id', $cursoId);
    }

    public function scopeComTccAtivo($query)
    {
        return $query->whereHas('tccAtivo');
    }

    // Methods
    public function getNomeCompleto(): string
    {
        return $this->usuario->nome_completo;
    }

    public function getEmail(): string
    {
        return $this->usuario->email;
    }

    public function temTccAtivo(): bool
    {
        return $this->tccAtivo()->exists();
    }

    public function getTccEmAndamento(): ?Tcc
    {
        return $this->tccs()
            ->whereIn('status', [
                'RASCUNHO',
                'EM_ORIENTACAO',
                'AGUARDANDO_BANCA',
                'BANCA_AGENDADA',
                'EM_AVALIACAO'
            ])
            ->first();
    }

    public function getTotalTccsAprovados(): int
    {
        return $this->tccs()
            ->whereIn('status', ['APROVADO', 'APROVADO_COM_RESSALVAS'])
            ->count();
    }

    public function getMediaNotas(): ?float
    {
        return $this->tccs()
            ->whereNotNull('nota_final')
            ->avg('nota_final');
    }
}

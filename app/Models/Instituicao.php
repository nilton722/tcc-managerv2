<?php

namespace App\Models;

class Instituicao extends BaseModel
{
    protected $table = 'instituicoes';

    protected $fillable = [
        'nome',
        'sigla',
        'nif',
        'endereco',
        'logo_url',
        'ativo',
    ];

    protected $casts = [
        'endereco' => 'array',
        'ativo' => 'boolean',
    ];

    // Relacionamentos
    public function usuarios()
    {
        return $this->hasMany(Usuario::class);
    }

    public function departamentos()
    {
        return $this->hasMany(Departamento::class);
    }

    // Scopes
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    // Methods
    public function ativar(): void
    {
        $this->update(['ativo' => true]);
    }

    public function desativar(): void
    {
        $this->update(['ativo' => false]);
    }

    public function getTotalUsuarios(): int
    {
        return $this->usuarios()->count();
    }

    public function getTotalCursos(): int
    {
        return Curso::whereHas('departamento', function ($query) {
            $query->where('instituicao_id', $this->id);
        })->count();
    }
}

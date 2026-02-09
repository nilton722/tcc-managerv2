<?php

namespace App\Policies;

use App\Models\Aluno;
use App\Models\Usuario;

class AlunoPolicy
{
    public function viewAny(Usuario $user): bool
    {
        return $user->hasAnyRole(['admin', 'coordenador', 'orientador']);
    }

    public function view(Usuario $user, Aluno $aluno): bool
    {
        // Admin e coordenador podem ver todos
        if ($user->hasAnyRole(['admin', 'coordenador'])) {
            return true;
        }

        // Aluno pode ver apenas próprio perfil
        if ($user->isAluno() && $user->aluno->id === $aluno->id) {
            return true;
        }

        // Orientador pode ver seus orientandos
        if ($user->isOrientador()) {
            return $aluno->tccs()
                ->whereHas('orientacoes', function ($query) use ($user) {
                    $query->where('orientador_id', $user->orientador->id)
                          ->where('ativo', true);
                })->exists();
        }

        return false;
    }

    public function create(Usuario $user): bool
    {
        return $user->hasAnyRole(['admin', 'coordenador']);
    }

    public function update(Usuario $user, Aluno $aluno): bool
    {
        // Admin e coordenador podem atualizar todos
        if ($user->hasAnyRole(['admin', 'coordenador'])) {
            return true;
        }

        // Aluno pode atualizar próprio perfil (dados limitados)
        if ($user->isAluno() && $user->aluno->id === $aluno->id) {
            return true;
        }

        return false;
    }

    public function delete(Usuario $user, Aluno $aluno): bool
    {
        return $user->hasAnyRole(['admin', 'coordenador']);
    }
}

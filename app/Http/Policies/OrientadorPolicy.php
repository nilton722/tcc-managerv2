<?php

namespace App\Policies;

use App\Models\Orientador;
use App\Models\Usuario;

class OrientadorPolicy
{
    public function viewAny(Usuario $user): bool
    {
        return true; // Todos podem listar orientadores
    }

    public function view(Usuario $user, Orientador $orientador): bool
    {
        return true; // Todos podem ver perfil de orientador
    }

    public function create(Usuario $user): bool
    {
        return $user->hasAnyRole(['admin', 'coordenador']);
    }

    public function update(Usuario $user, Orientador $orientador): bool
    {
        // Admin e coordenador podem atualizar todos
        if ($user->hasAnyRole(['admin', 'coordenador'])) {
            return true;
        }

        // Orientador pode atualizar próprio perfil
        if ($user->isOrientador() && $user->orientador->id === $orientador->id) {
            return true;
        }

        return false;
    }

    public function delete(Usuario $user, Orientador $orientador): bool
    {
        return $user->hasRole('admin');
    }
}

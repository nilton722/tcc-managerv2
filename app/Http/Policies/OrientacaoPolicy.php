<?php

namespace App\Policies;

use App\Models\Orientacao;
use App\Models\Tcc;
use App\Models\Usuario;

class OrientacaoPolicy
{
    public function viewAny(Usuario $user, Tcc $tcc): bool
    {
        return app(TccPolicy::class)->view($user, $tcc);
    }

    public function view(Usuario $user, Orientacao $orientacao): bool
    {
        $tcc = $orientacao->tcc;
        return app(TccPolicy::class)->view($user, $tcc);
    }

    public function create(Usuario $user, Tcc $tcc): bool
    {
        // Admin pode sempre
        if ($user->hasRole('admin')) {
            return true;
        }

        // Coordenador pode criar
        if ($user->isCoordenador()) {
            return true;
        }

        // Aluno do TCC pode criar orientação (escolher orientador)
        if ($user->isAluno() && $user->aluno->id === $tcc->aluno_id) {
            // Apenas se TCC está em RASCUNHO
            return $tcc->status === 'RASCUNHO';
        }

        return false;
    }

    public function delete(Usuario $user, Orientacao $orientacao): bool
    {
        $tcc = $orientacao->tcc;

        // Admin pode sempre
        if ($user->hasRole('admin')) {
            return true;
        }

        // Coordenador pode remover
        if ($user->isCoordenador()) {
            return true;
        }

        // Aluno pode remover se TCC em RASCUNHO
        if ($user->isAluno() && $user->aluno->id === $tcc->aluno_id) {
            return $tcc->status === 'RASCUNHO';
        }

        return false;
    }
}

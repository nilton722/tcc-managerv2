<?php

namespace App\Policies;

use App\Models\Banca;
use App\Models\Tcc;
use App\Models\Usuario;

class BancaPolicy
{
    public function viewAny(Usuario $user, Tcc $tcc): bool
    {
        return app(TccPolicy::class)->view($user, $tcc);
    }

    public function view(Usuario $user, Banca $banca): bool
    {
        $tcc = $banca->tcc;

        // Admin e coordenador podem ver todas
        if ($user->hasAnyRole(['admin', 'coordenador'])) {
            return true;
        }

        // Aluno do TCC pode ver suas bancas
        if ($user->isAluno() && $user->aluno->id === $tcc->aluno_id) {
            return true;
        }

        // Orientador do TCC pode ver
        if ($user->isOrientador()) {
            $isOrientador = $tcc->orientacoes()
                ->where('orientador_id', $user->orientador->id)
                ->where('ativo', true)
                ->exists();

            if ($isOrientador) {
                return true;
            }
        }

        // Membro da banca pode ver
        $isMembro = $banca->membros()
            ->where('usuario_id', $user->id)
            ->exists();

        return $isMembro;
    }

    public function create(Usuario $user, Tcc $tcc): bool
    {
        // Admin e coordenador podem criar
        if ($user->hasAnyRole(['admin', 'coordenador'])) {
            return true;
        }

        // Orientador do TCC pode criar
        if ($user->isOrientador()) {
            return $tcc->orientacoes()
                ->where('orientador_id', $user->orientador->id)
                ->where('tipo_orientacao', 'ORIENTADOR')
                ->where('ativo', true)
                ->exists();
        }

        return false;
    }

    public function update(Usuario $user, Banca $banca): bool
    {
        // Não pode atualizar banca concluída ou cancelada
        if (in_array($banca->status, ['CONCLUIDA', 'CANCELADA'])) {
            return false;
        }

        return $this->create($user, $banca->tcc);
    }

    public function delete(Usuario $user, Banca $banca): bool
    {
        // Admin pode deletar
        if ($user->hasRole('admin')) {
            return true;
        }

        // Coordenador pode deletar se não estiver concluída
        if ($user->isCoordenador() && $banca->status !== 'CONCLUIDA') {
            return true;
        }

        return false;
    }

    public function manage(Usuario $user, Banca $banca): bool
    {
        return $this->create($user, $banca->tcc);
    }

    public function evaluate(Usuario $user, Banca $banca): bool
    {
        // Apenas membros da banca podem avaliar
        return $banca->membros()
            ->where('usuario_id', $user->id)
            ->where('confirmado', true)
            ->exists();
    }
}

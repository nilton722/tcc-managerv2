<?php

namespace App\Policies;

use App\Models\Documento;
use App\Models\Tcc;
use App\Models\Usuario;

class DocumentoPolicy
{
    public function viewAny(Usuario $user, Tcc $tcc): bool
    {
        // Usa a mesma lógica do TccPolicy
        return app(TccPolicy::class)->view($user, $tcc);
    }

    public function view(Usuario $user, Documento $documento): bool
    {
        $tcc = $documento->tcc;
        return app(TccPolicy::class)->view($user, $tcc);
    }

    public function create(Usuario $user, Tcc $tcc): bool
    {
        // Admin pode sempre
        if ($user->hasRole('admin')) {
            return true;
        }

        // Aluno do TCC pode fazer upload
        if ($user->isAluno() && $user->aluno->id === $tcc->aluno_id) {
            return true;
        }

        // Orientador do TCC pode fazer upload
        if ($user->isOrientador()) {
            return $tcc->orientacoes()
                ->where('orientador_id', $user->orientador->id)
                ->where('ativo', true)
                ->exists();
        }

        // Coordenador pode fazer upload
        if ($user->isCoordenador()) {
            return true;
        }

        return false;
    }

    public function update(Usuario $user, Documento $documento): bool
    {
        // Apenas quem fez upload ou admin/coordenador
        if ($user->hasAnyRole(['admin', 'coordenador'])) {
            return true;
        }

        return $documento->upload_por === $user->id;
    }

    public function delete(Usuario $user, Documento $documento): bool
    {
        // Admin e coordenador podem deletar
        if ($user->hasAnyRole(['admin', 'coordenador'])) {
            return true;
        }

        // Quem fez upload pode deletar se documento ainda não foi aprovado
        if ($documento->upload_por === $user->id && $documento->status !== 'APROVADO') {
            return true;
        }

        return false;
    }

    public function approve(Usuario $user, Documento $documento): bool
    {
        $tcc = $documento->tcc;

        // Admin pode sempre
        if ($user->hasRole('admin')) {
            return true;
        }

        // Coordenador pode aprovar
        if ($user->isCoordenador()) {
            return true;
        }

        // Orientador do TCC pode aprovar
        if ($user->isOrientador()) {
            return $tcc->orientacoes()
                ->where('orientador_id', $user->orientador->id)
                ->where('tipo_orientacao', 'ORIENTADOR')
                ->where('ativo', true)
                ->exists();
        }

        return false;
    }

    public function reject(Usuario $user, Documento $documento): bool
    {
        return $this->approve($user, $documento);
    }
}

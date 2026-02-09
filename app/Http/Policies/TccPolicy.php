<?php

namespace App\Policies;

use App\Models\Tcc;
use App\Models\Usuario;

class TccPolicy
{
    /**
     * Determinar se o usuário pode ver qualquer TCC
     */
    public function viewAny(Usuario $usuario): bool
    {
        // Todos os usuários autenticados podem listar TCCs (com filtros aplicados)
        return true;
    }

    /**
     * Determinar se o usuário pode ver um TCC específico
     */
    public function view(Usuario $usuario, Tcc $tcc): bool
    {
        // Admin pode ver todos
        if ($usuario->isAdmin()) {
            return true;
        }

        // Aluno pode ver seu próprio TCC
        if ($usuario->isAluno() && $tcc->aluno->usuario_id === $usuario->id) {
            return true;
        }

        // Orientador pode ver TCCs que orienta
        if ($usuario->isOrientador()) {
            $orientadorId = $usuario->orientador->id;
            $isOrientador = $tcc->orientacoes()
                ->where('orientador_id', $orientadorId)
                ->where('ativo', true)
                ->exists();
                
            if ($isOrientador) {
                return true;
            }
        }

        // Coordenador pode ver TCCs do seu curso
        if ($usuario->isCoordenador()) {
            $cursosIds = $usuario->cursos()->pluck('id')->toArray();
            if (in_array($tcc->curso_id, $cursosIds)) {
                return true;
            }
        }

        // Membros de banca podem ver o TCC
        $isMembro = $tcc->bancas()
            ->whereHas('membros', function ($query) use ($usuario) {
                $query->where('usuario_id', $usuario->id);
            })
            ->exists();

        return $isMembro;
    }

    /**
     * Determinar se o usuário pode criar TCCs
     */
    public function create(Usuario $usuario): bool
    {
        // Alunos podem criar TCC
        if ($usuario->isAluno()) {
            // Verificar se já não tem TCC ativo
            return !$usuario->aluno->temTccAtivo();
        }

        // Admins e coordenadores podem criar em nome de alunos
        return $usuario->isAdmin() || $usuario->isCoordenador();
    }

    /**
     * Determinar se o usuário pode atualizar um TCC
     */
    public function update(Usuario $usuario, Tcc $tcc): bool
    {
        // TCC precisa estar editável
        if (!$tcc->podeEditar()) {
            // Exceção: Admins e coordenadores podem editar mesmo após submissão
            if (!($usuario->isAdmin() || $usuario->isCoordenador())) {
                return false;
            }
        }

        // Aluno pode editar seu próprio TCC
        if ($usuario->isAluno() && $tcc->aluno->usuario_id === $usuario->id) {
            return true;
        }

        // Orientador pode editar TCCs que orienta
        if ($usuario->isOrientador()) {
            $orientadorId = $usuario->orientador->id;
            return $tcc->orientacoes()
                ->where('orientador_id', $orientadorId)
                ->where('tipo_orientacao', 'ORIENTADOR')
                ->where('ativo', true)
                ->exists();
        }

        // Admin e coordenador podem editar
        return $usuario->isAdmin() || $usuario->isCoordenador();
    }

    /**
     * Determinar se o usuário pode excluir um TCC
     */
    public function delete(Usuario $usuario, Tcc $tcc): bool
    {
        // Apenas rascunhos podem ser excluídos por alunos
        if ($usuario->isAluno() && $tcc->aluno->usuario_id === $usuario->id) {
            return $tcc->status === 'RASCUNHO';
        }

        // Admin e coordenador podem excluir qualquer TCC
        return $usuario->isAdmin() || $usuario->isCoordenador();
    }

    /**
     * Determinar se o usuário pode submeter TCC para banca
     */
    public function submit(Usuario $usuario, Tcc $tcc): bool
    {
        // Aluno pode submeter seu próprio TCC
        if ($usuario->isAluno() && $tcc->aluno->usuario_id === $usuario->id) {
            return in_array($tcc->status, ['RASCUNHO', 'EM_ORIENTACAO']);
        }

        // Orientador pode submeter TCCs que orienta
        if ($usuario->isOrientador()) {
            $orientadorId = $usuario->orientador->id;
            $isOrientador = $tcc->orientacoes()
                ->where('orientador_id', $orientadorId)
                ->where('tipo_orientacao', 'ORIENTADOR')
                ->where('ativo', true)
                ->exists();
                
            if ($isOrientador) {
                return in_array($tcc->status, ['RASCUNHO', 'EM_ORIENTACAO']);
            }
        }

        return false;
    }

    /**
     * Determinar se o usuário pode aprovar/reprovar TCC
     */
    public function approve(Usuario $usuario, Tcc $tcc): bool
    {
        // Apenas membros da banca podem aprovar
        if (!$tcc->podeAprovar()) {
            return false;
        }

        // Verificar se é membro da banca ativa
        $bancaAtiva = $tcc->bancas()
            ->whereIn('status', ['EM_ANDAMENTO', 'CONCLUIDA'])
            ->latest()
            ->first();

        if (!$bancaAtiva) {
            return false;
        }

        return $bancaAtiva->membros()
            ->where('usuario_id', $usuario->id)
            ->exists();
    }

    /**
     * Determinar se o usuário pode cancelar um TCC
     */
    public function cancel(Usuario $usuario, Tcc $tcc): bool
    {
        // Aluno pode cancelar seu próprio TCC antes da defesa
        if ($usuario->isAluno() && $tcc->aluno->usuario_id === $usuario->id) {
            return in_array($tcc->status, [
                'RASCUNHO',
                'EM_ORIENTACAO',
                'AGUARDANDO_BANCA'
            ]);
        }

        // Admin e coordenador podem cancelar qualquer TCC
        return $usuario->isAdmin() || $usuario->isCoordenador();
    }

    /**
     * Determinar se o usuário pode gerenciar orientações
     */
    public function manageOrientacoes(Usuario $usuario, Tcc $tcc): bool
    {
        // Aluno pode adicionar orientador ao seu TCC
        if ($usuario->isAluno() && $tcc->aluno->usuario_id === $usuario->id) {
            return $tcc->status === 'RASCUNHO';
        }

        // Coordenadores e admins podem gerenciar orientações
        return $usuario->isAdmin() || $usuario->isCoordenador();
    }

    /**
     * Determinar se o usuário pode gerenciar bancas
     */
    public function manageBancas(Usuario $usuario, Tcc $tcc): bool
    {
        // Orientador pode criar banca para seus orientandos
        if ($usuario->isOrientador()) {
            $orientadorId = $usuario->orientador->id;
            $isOrientador = $tcc->orientacoes()
                ->where('orientador_id', $orientadorId)
                ->where('tipo_orientacao', 'ORIENTADOR')
                ->where('ativo', true)
                ->exists();
                
            if ($isOrientador) {
                return true;
            }
        }

        // Coordenadores e admins podem gerenciar bancas
        return $usuario->isAdmin() || $usuario->isCoordenador();
    }
}

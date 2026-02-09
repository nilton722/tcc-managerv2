<?php

namespace App\Services;

use App\Models\Tcc;
use App\Models\Aluno;
use App\Models\Orientacao;
use App\Notifications\TccSubmitidoParaBancaNotification;
use Illuminate\Support\Facades\DB;

class TccService
{
    /**
     * Criar novo TCC
     */
    public function create(array $data): Tcc
    {
        return DB::transaction(function () use ($data) {
            // Verificar se aluno já tem TCC ativo
            $aluno = Aluno::findOrFail($data['aluno_id']);
            
            if ($aluno->temTccAtivo()) {
                throw new \Exception('Aluno já possui um TCC ativo.');
            }

            // Criar TCC
            $tcc = Tcc::create([
                'aluno_id' => $data['aluno_id'],
                'curso_id' => $data['curso_id'],
                'linha_pesquisa_id' => $data['linha_pesquisa_id'] ?? null,
                'titulo' => $data['titulo'],
                'titulo_ingles' => $data['titulo_ingles'] ?? null,
                'tipo_trabalho' => $data['tipo_trabalho'],
                'resumo' => $data['resumo'] ?? null,
                'abstract' => $data['abstract'] ?? null,
                'palavras_chave' => $data['palavras_chave'] ?? null,
                'keywords' => $data['keywords'] ?? null,
                'status' => 'RASCUNHO',
            ]);

            // Se houver orientador, criar orientação
            if (isset($data['orientador_id'])) {
                $this->adicionarOrientador($tcc, $data['orientador_id'], 'ORIENTADOR');
                $tcc->iniciarOrientacao();
            }

            // Criar cronograma padrão se template fornecido
            if (isset($data['template_cronograma_id'])) {
                app(CronogramaService::class)->criarPorTemplate($tcc, $data['template_cronograma_id']);
            }

            return $tcc;
        });
    }

    /**
     * Atualizar TCC
     */
    public function update(Tcc $tcc, array $data): Tcc
    {
        if (!$tcc->podeEditar()) {
            throw new \Exception('TCC não pode ser editado no status atual.');
        }

        $tcc->update($data);

        return $tcc->fresh();
    }

    /**
     * Adicionar orientador ao TCC
     */
    public function adicionarOrientador(
        Tcc $tcc,
        string $orientadorId,
        string $tipo = 'ORIENTADOR'
    ): Orientacao {
        return DB::transaction(function () use ($tcc, $orientadorId, $tipo) {
            // Verificar se orientador já está vinculado
            $orientacaoExistente = $tcc->orientacoes()
                ->where('orientador_id', $orientadorId)
                ->where('ativo', true)
                ->first();

            if ($orientacaoExistente) {
                throw new \Exception('Orientador já está vinculado a este TCC.');
            }

            // Se for orientador principal, desativar outros orientadores principais
            if ($tipo === 'ORIENTADOR') {
                $tcc->orientacoes()
                    ->where('tipo_orientacao', 'ORIENTADOR')
                    ->update(['ativo' => false, 'data_fim' => now()]);
            }

            // Criar nova orientação
            $orientacao = Orientacao::create([
                'tcc_id' => $tcc->id,
                'orientador_id' => $orientadorId,
                'tipo_orientacao' => $tipo,
                'data_inicio' => now(),
                'ativo' => true,
            ]);

            return $orientacao;
        });
    }

    /**
     * Remover orientador do TCC
     */
    public function removerOrientador(Tcc $tcc, string $orientadorId): void
    {
        DB::transaction(function () use ($tcc, $orientadorId) {
            $orientacao = $tcc->orientacoes()
                ->where('orientador_id', $orientadorId)
                ->where('ativo', true)
                ->firstOrFail();

            $orientacao->update([
                'ativo' => false,
                'data_fim' => now(),
            ]);
        });
    }

    /**
     * Submeter TCC para banca
     */
    public function submeterParaBanca(Tcc $tcc): void
    {
        DB::transaction(function () use ($tcc) {
            // Validar pré-requisitos
            if (!$tcc->temOrientador()) {
                throw new \Exception('TCC precisa ter um orientador para ser submetido.');
            }

            // Verificar documentos obrigatórios
            $documentosObrigatorios = $this->verificarDocumentosObrigatorios($tcc);
            if (!$documentosObrigatorios) {
                throw new \Exception('Todos os documentos obrigatórios devem estar aprovados.');
            }

            // Atualizar status
            $tcc->submeterParaBanca();

            // Notificar orientador e coordenador
            $this->notificarSubmissao($tcc);
        });
    }

    /**
     * Verificar se documentos obrigatórios estão aprovados
     */
    private function verificarDocumentosObrigatorios(Tcc $tcc): bool
    {
        $tiposObrigatorios = \App\Models\TipoDocumento::where('obrigatorio', true)
            ->pluck('id');

        foreach ($tiposObrigatorios as $tipoId) {
            $documentoAprovado = $tcc->documentos()
                ->where('tipo_documento_id', $tipoId)
                ->where('status', 'APROVADO')
                ->exists();

            if (!$documentoAprovado) {
                return false;
            }
        }

        return true;
    }

    /**
     * Notificar sobre submissão
     */
    private function notificarSubmissao(Tcc $tcc): void
    {
        $orientador = $tcc->orientador()->first();
        
        if ($orientador) {
            $orientador->usuario->notify(new TccSubmitidoParaBancaNotification($tcc));
        }

        // Notificar coordenador do curso
        $coordenador = $tcc->curso->coordenador;
        if ($coordenador) {
            $coordenador->notify(new TccSubmitidoParaBancaNotification($tcc));
        }
    }

    /**
     * Aprovar TCC após defesa
     */
    public function aprovarAposDefesa(
        Tcc $tcc,
        float $notaFinal,
        bool $comRessalvas = false
    ): void {
        DB::transaction(function () use ($tcc, $notaFinal, $comRessalvas) {
            if ($notaFinal < 5) {
                $tcc->reprovar($notaFinal);
            } else {
                $tcc->aprovar($notaFinal, $comRessalvas);
            }

            // Atualizar cronograma
            if ($tcc->cronograma) {
                $tcc->cronograma->etapas()
                    ->where('status', '!=', 'CONCLUIDA')
                    ->update(['status' => 'CONCLUIDA', 'progresso_percentual' => 100]);
            }
        });
    }

    /**
     * Gerar relatório de TCCs
     */
    public function gerarRelatorio(array $filtros = []): array
    {
        $query = Tcc::query();

        if (isset($filtros['curso_id'])) {
            $query->where('curso_id', $filtros['curso_id']);
        }

        if (isset($filtros['data_inicio'])) {
            $query->whereDate('created_at', '>=', $filtros['data_inicio']);
        }

        if (isset($filtros['data_fim'])) {
            $query->whereDate('created_at', '<=', $filtros['data_fim']);
        }

        $total = $query->count();
        $porStatus = $query->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $mediaNotas = $query->whereNotNull('nota_final')->avg('nota_final');

        $aprovados = $query->whereIn('status', ['APROVADO', 'APROVADO_COM_RESSALVAS'])->count();
        $reprovados = $query->where('status', 'REPROVADO')->count();
        $emAndamento = $query->whereIn('status', [
            'RASCUNHO',
            'EM_ORIENTACAO',
            'AGUARDANDO_BANCA',
            'BANCA_AGENDADA',
            'EM_AVALIACAO'
        ])->count();

        return [
            'total' => $total,
            'por_status' => $porStatus,
            'media_notas' => round($mediaNotas, 2),
            'aprovados' => $aprovados,
            'reprovados' => $reprovados,
            'em_andamento' => $emAndamento,
            'taxa_aprovacao' => $total > 0 ? round(($aprovados / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Buscar TCCs por palavra-chave
     */
    public function buscar(string $termo): \Illuminate\Database\Eloquent\Collection
    {
        return Tcc::where(function ($query) use ($termo) {
            $query->where('titulo', 'LIKE', "%{$termo}%")
                ->orWhere('titulo_ingles', 'LIKE', "%{$termo}%")
                ->orWhere('resumo', 'LIKE', "%{$termo}%")
                ->orWhere('abstract', 'LIKE', "%{$termo}%");
        })
            ->with(['aluno.usuario', 'curso'])
            ->get();
    }
}

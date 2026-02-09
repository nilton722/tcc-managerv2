<?php

namespace App\Services;

use App\Models\CronogramaTcc;
use App\Models\EtapaTcc;
use App\Models\TemplateCronograma;
use Illuminate\Support\Facades\DB;

class CronogramaService
{
    public function criarPorTemplate(string $tccId, string $templateId)
    {
        $template = TemplateCronograma::with('etapas')->findOrFail($templateId);

        DB::beginTransaction();

        try {
            $dataInicio = now();
            $duracaoTotal = $template->etapas->sum('duracao_dias');
            $dataFim = $dataInicio->copy()->addDays($duracaoTotal);

            $cronograma = CronogramaTcc::create([
                'tcc_id' => $tccId,
                'template_cronograma_id' => $templateId,
                'data_inicio' => $dataInicio,
                'data_fim_prevista' => $dataFim,
            ]);

            $dataAtual = $dataInicio->copy();

            foreach ($template->etapas()->ordenadas()->get() as $etapaTemplate) {
                $dataFimEtapa = $dataAtual->copy()->addDays($etapaTemplate->duracao_dias);

                EtapaTcc::create([
                    'cronograma_tcc_id' => $cronograma->id,
                    'etapa_template_id' => $etapaTemplate->id,
                    'nome' => $etapaTemplate->nome,
                    'ordem' => $etapaTemplate->ordem,
                    'data_inicio_prevista' => $dataAtual,
                    'data_fim_prevista' => $dataFimEtapa,
                    'status' => 'PENDENTE',
                    'progresso_percentual' => 0,
                ]);

                $dataAtual = $dataFimEtapa->copy()->addDay();
            }

            DB::commit();

            return $cronograma;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function verificarAtrasos(string $cronogramaId)
    {
        $cronograma = CronogramaTcc::findOrFail($cronogramaId);

        foreach ($cronograma->etapas as $etapa) {
            $etapa->verificarAtraso();
        }

        return $cronograma->fresh('etapas');
    }

    public function atualizarProgresso(string $etapaId, int $percentual)
    {
        $etapa = EtapaTcc::findOrFail($etapaId);
        $etapa->atualizarProgresso($percentual);

        return $etapa;
    }
}
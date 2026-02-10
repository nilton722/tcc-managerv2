<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use App\Models\Tcc;
use App\Models\TipoDocumento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentoController extends Controller
{
    public function index(Request $request, string $tccId): JsonResponse
    {
        $tcc = Tcc::findOrFail($tccId);

        // $this->authorize('view', $tcc);

        $documentos = Documento::where('tcc_id', $tccId)
            ->with(['tipoDocumento', 'uploadPor', 'versaoAnterior'])
            ->when($request->tipo_documento_id, function ($query, $tipoId) {
                $query->where('tipo_documento_id', $tipoId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->ultima_versao === 'true', function ($query) {
                $query->ultimaVersao();
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $documentos,
        ]);
    }

    public function store(Request $request, string $tccId): JsonResponse
    {
        $tcc = Tcc::findOrFail($tccId);

        // // $this->authorize('update', $tcc);

        $validated = $request->validate([
            'tipo_documento_id' => 'required|uuid|exists:tipos_documento,id',
            'arquivo' => 'required|file|max:102400', // 100MB
            'comentarios' => 'nullable|string',
        ]);

        
        $tipoDocumento = TipoDocumento::findOrFail($validated['tipo_documento_id']);

        
        // Validar extensão
        $extensao = '.' . $request->file('arquivo')->getClientOriginalExtension();

        if (!$tipoDocumento->isExtensaoPermitida($extensao)) {
            return response()->json([
                'success' => false,
                'message' => 'Extensão de arquivo não permitida. Extensões aceitas: ' . 
                            $tipoDocumento->getExtensoesFormatadas(),
            ], 422);
        }


        // Validar tamanho
        $tamanhoBytes = $request->file('arquivo')->getSize();
        if (!$tipoDocumento->isTamanhoPermitido($tamanhoBytes)) {
            return response()->json([
                'success' => false,
                'message' => 'Arquivo excede o tamanho máximo permitido de ' . 
                            $tipoDocumento->getTamanhoMaximoFormatado(),
            ], 422);
        }

        // Verificar se já existe documento do mesmo tipo (para criar nova versão)
        $documentoAnterior = Documento::where('tcc_id', $tccId)
            ->where('tipo_documento_id', $validated['tipo_documento_id'])
            ->ultimaVersao()
            ->first();

        $versao = $documentoAnterior ? $documentoAnterior->versao + 1 : 1;

        // Upload do arquivo
        $arquivo = $request->file('arquivo');
        $nomeOriginal = $arquivo->getClientOriginalName();
        $nomeArquivo = Str::slug(pathinfo($nomeOriginal, PATHINFO_FILENAME)) . '-' . 
                       time() . $extensao;

        $path = $arquivo->storeAs(
            "tccs/{$tccId}/documentos",
            $nomeArquivo,
            'public'
        );

        // Calcular hash do arquivo
        $hashArquivo = hash_file('sha256', $arquivo->getRealPath());

        // Criar documento
        $documento = Documento::create([
            'tcc_id' => $tccId,
            'tipo_documento_id' => $validated['tipo_documento_id'],
            'versao_anterior_id' => $documentoAnterior?->id,
            'upload_por' => auth()->id(),
            'nome_arquivo' => $nomeOriginal,
            'arquivo_url' => $path,
            'tamanho_bytes' => $tamanhoBytes,
            'hash_arquivo' => $hashArquivo,
            'mime_type' => $arquivo->getMimeType(),
            'versao' => $versao,
            'status' => 'PENDENTE',
            'comentarios' => $validated['comentarios'] ?? null,
            'upload_em' => now(),
        ]);

        $documento->load(['tipoDocumento', 'uploadPor']);

        return response()->json([
            'success' => true,
            'message' => 'Documento enviado com sucesso',
            'data' => $documento,
        ], 201);
    }

    public function show(string $tccId, string $id): JsonResponse
    {
        $documento = Documento::with(['tipoDocumento', 'tcc', 'uploadPor', 'versaoAnterior'])
        ->findOrFail($id);
        // $this->authorize('view', $documento->tcc);

        return response()->json([
            'success' => true,
            'data' => $documento,
        ]);
    }

    public function update(Request $request,  string $tccId, string $id): JsonResponse
    {
        $documento = Documento::findOrFail($id);

        // $this->authorize('update', $documento->tcc);

        $validated = $request->validate([
            'comentarios' => 'nullable|string',
        ]);

        dd('comentarios', $validated);

        $documento->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Documento atualizado com sucesso',
            'data' => $documento,
        ]);
    }

    public function destroy(string $tccId, string $id): JsonResponse
    {
        $documento = Documento::findOrFail($id);

        // $this->authorize('delete', $documento->tcc);

        // Deletar arquivo físico
        if (Storage::disk('public')->exists($documento->arquivo_url)) {
            Storage::disk('public')->delete($documento->arquivo_url);
        }

        $documento->delete();

        return response()->json([
            'success' => true,
            'message' => 'Documento excluído com sucesso',
        ]);
    }

    public function aprovar(Request $request, string $tccId, string $id): JsonResponse
    {
        $documento = Documento::findOrFail($id);

        // $this->authorize('submit', $documento->tcc);

        $validated = $request->validate([
            'comentarios' => 'nullable|string',
        ]);

        $documento->aprovar($validated['comentarios'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Documento aprovado com sucesso',
            'data' => $documento,
        ]);
    }

    public function rejeitar(Request $request, string $tccId, string $id): JsonResponse
    {
        $documento = Documento::findOrFail($id);

        // $this->authorize('submit', $documento->tcc);

        $validated = $request->validate([
            'comentarios' => 'required|string',
        ]);

        $documento->rejeitar($validated['comentarios']);

        return response()->json([
            'success' => true,
            'message' => 'Documento rejeitado',
            'data' => $documento,
        ]);
    }

    public function download (string $tccId, string $id)
    {
        $documento = Documento::findOrFail($id);

        // $this->authorize('view', $documento->tcc);

        if (!Storage::disk('public')->exists($documento->arquivo_url)) {
            return response()->json([
                'success' => false,
                'message' => 'Arquivo não encontrado',
            ], 404);
        }

        return Storage::disk('public')->download(
            $documento->arquivo_url,
            $documento->nome_arquivo
        );
    }

    public function verificarIntegridade(string $tccId, string $id): JsonResponse
    {
        $documento = Documento::findOrFail($id);

        // $this->authorize('view', $documento->tcc);

        $integro = $documento->verificarIntegridade();

        return response()->json([
            'success' => true,
            'data' => [
                'integro' => $integro,
                'hash_registrado' => $documento->hash_arquivo,
                'verificado_em' => now(),
            ],
        ]);
    }
}

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Comprovante de Submissão de TCC</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; margin: 30px; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 3px solid #059669; }
        .header h1 { margin: 0; font-size: 18pt; color: #059669; }
        .logo { max-width: 100px; margin-bottom: 10px; }
        .comprovante-numero { background: #d1fae5; padding: 10px; margin: 20px 0; text-align: center; font-size: 12pt; font-weight: bold; border: 2px solid #059669; }
        .info-box { border: 2px solid #333; padding: 20px; margin: 20px 0; background: #f9fafb; }
        .info-row { margin: 12px 0; padding: 8px; background: white; border-left: 4px solid #059669; }
        .label { font-weight: bold; color: #065f46; display: inline-block; width: 180px; }
        .valor { color: #1f2937; }
        .hash-box { background: #fef3c7; border: 2px dashed #f59e0b; padding: 15px; margin: 20px 0; }
        .hash-label { font-weight: bold; color: #92400e; margin-bottom: 5px; }
        .hash-value { font-family: 'Courier New', monospace; font-size: 9pt; word-break: break-all; color: #451a03; }
        .aviso { background: #dbeafe; border-left: 5px solid #2563eb; padding: 15px; margin: 20px 0; }
        .aviso-titulo { font-weight: bold; color: #1e40af; margin-bottom: 5px; }
        .status-box { text-align: center; padding: 15px; margin: 20px 0; border: 2px solid #059669; background: #d1fae5; }
        .status-icone { font-size: 36pt; color: #059669; }
        .assinatura { margin-top: 60px; text-align: center; }
        .linha-assinatura { border-top: 2px solid #000; width: 50%; margin: 0 auto; padding-top: 5px; }
        .rodape { margin-top: 40px; text-align: center; font-size: 9pt; color: #666; border-top: 1px solid #ddd; padding-top: 15px; }
        .qrcode-placeholder { width: 120px; height: 120px; border: 2px dashed #999; margin: 20px auto; display: flex; align-items: center; justify-content: center; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        @if($tcc->curso->departamento->instituicao->logo_url)
            <img src="{{ $tcc->curso->departamento->instituicao->logo_url }}" class="logo" alt="Logo">
        @endif
        <h1>COMPROVANTE DE SUBMISSÃO</h1>
        <p>{{ $tcc->curso->departamento->instituicao->nome }}</p>
        <p>{{ $tcc->curso->nome }}</p>
    </div>

    <div class="comprovante-numero">
        PROTOCOLO Nº {{ str_pad($tcc->id, 8, '0', STR_PAD_LEFT) }}/{{ now()->format('Y') }}
    </div>

    <div class="status-box">
        <div class="status-icone">✓</div>
        <div style="font-size: 14pt; font-weight: bold; color: #065f46; margin-top: 10px;">
            SUBMISSÃO REALIZADA COM SUCESSO
        </div>
    </div>

    <div class="info-box">
        <h3 style="margin-top: 0; color: #065f46; border-bottom: 2px solid #059669; padding-bottom: 10px;">
            DADOS DO TRABALHO
        </h3>
        
        <div class="info-row">
            <span class="label">Aluno:</span>
            <span class="valor">{{ $tcc->aluno->usuario->nome_completo }}</span>
        </div>
        
        <div class="info-row">
            <span class="label">Matrícula:</span>
            <span class="valor">{{ $tcc->aluno->matricula }}</span>
        </div>
        
        <div class="info-row">
            <span class="label">Curso:</span>
            <span class="valor">{{ $tcc->curso->nome }} ({{ $tcc->curso->codigo }})</span>
        </div>
        
        <div class="info-row">
            <span class="label">Tipo de Trabalho:</span>
            <span class="valor">{{ $tcc->getTipoFormatado() }}</span>
        </div>
        
        <div class="info-row">
            <span class="label">Título:</span>
            <span class="valor">{{ $tcc->titulo }}</span>
        </div>
        
        @if($tcc->orientador)
        <div class="info-row">
            <span class="label">Orientador:</span>
            <span class="valor">{{ $tcc->orientador->usuario->nome_completo }}</span>
        </div>
        @endif
        
        @if($tcc->linhaPesquisa)
        <div class="info-row">
            <span class="label">Linha de Pesquisa:</span>
            <span class="valor">{{ $tcc->linhaPesquisa->nome }}</span>
        </div>
        @endif
    </div>

    <div class="info-box">
        <h3 style="margin-top: 0; color: #065f46; border-bottom: 2px solid #059669; padding-bottom: 10px;">
            DADOS DA SUBMISSÃO
        </h3>
        
        <div class="info-row">
            <span class="label">Data de Submissão:</span>
            <span class="valor">{{ $data_geracao->format('d/m/Y') }}</span>
        </div>
        
        <div class="info-row">
            <span class="label">Hora de Submissão:</span>
            <span class="valor">{{ $data_geracao->format('H:i:s') }}</span>
        </div>
        
        <div class="info-row">
            <span class="label">Status Atual:</span>
            <span class="valor">{{ $tcc->status }}</span>
        </div>
        
        <div class="info-row">
            <span class="label">Total de Documentos:</span>
            <span class="valor">{{ $tcc->documentos->count() }} arquivo(s)</span>
        </div>
    </div>

    <div class="hash-box">
        <div class="hash-label">🔒 CÓDIGO DE AUTENTICAÇÃO (Hash SHA-256)</div>
        <div class="hash-value">
            {{ strtoupper(hash('sha256', $tcc->id . $tcc->aluno->matricula . $data_geracao->timestamp)) }}
        </div>
        <div style="margin-top: 10px; font-size: 9pt; color: #92400e;">
            Este código garante a autenticidade deste comprovante
        </div>
    </div>

    <div class="aviso">
        <div class="aviso-titulo">📌 INFORMAÇÕES IMPORTANTES</div>
        <ul style="margin: 10px 0; padding-left: 20px;">
            <li>Este comprovante confirma o recebimento do trabalho pelo sistema</li>
            <li>Guarde este documento para consultas futuras</li>
            <li>O número de protocolo poderá ser solicitado para acompanhamento</li>
            <li>A aprovação final depende da avaliação da banca examinadora</li>
            <li>Alterações no trabalho após a submissão requerem nova submissão</li>
        </ul>
    </div>

    <!-- Placeholder para QR Code futuro -->
    <div style="text-align: center; margin: 30px 0;">
        <div class="qrcode-placeholder">
            QR CODE<br>
            <small style="font-size: 8pt;">(verificação)</small>
        </div>
        <small style="color: #666;">Código para verificação rápida do comprovante</small>
    </div>

    <div class="assinatura">
        <p style="margin: 40px 0 5px 0;">
            _____________________________________, {{ $data_geracao->format('d') }} de 
            {{ ucfirst($data_geracao->locale('pt_BR')->translatedFormat('F')) }} de 
            {{ $data_geracao->format('Y') }}
        </p>
    </div>

    <div class="rodape">
        <p><strong>SISTEMA DE GESTÃO DE TCCs</strong></p>
        <p>Documento gerado eletronicamente em {{ $data_geracao->format('d/m/Y H:i:s') }}</p>
        <p style="font-size: 8pt; margin-top: 10px;">
            Protocolo: {{ str_pad($tcc->id, 8, '0', STR_PAD_LEFT) }}/{{ now()->format('Y') }} | 
            Validade: Indeterminada | 
            Emissão: Sistema Automatizado
        </p>
    </div>
</body>
</html>

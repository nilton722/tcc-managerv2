# VIEWS DE RELATÓRIOS - BLADE TEMPLATES

## Views Criadas (3 principais)

1. **calendario-defesas.blade.php** - Calendário de defesas agendadas
2. **matriz-defesa.blade.php** - Ficha de avaliação da banca
3. **certificado.blade.php** - Certificado de conclusão

## Views Adicionais (criar conforme necessário)

### 4. tccs-recomendacoes.blade.php
Lista de TCCs com recomendações de correções das bancas

### 5. ata-defesa.blade.php
Ata oficial da defesa com resultado final

### 6. lista-presenca.blade.php  
Lista de presença dos membros da banca

### 7. estatisticas-curso.blade.php
Relatório estatístico do curso

### 8. orientacoes-orientador.blade.php
Relatório de orientações do orientador

### 9. declaracao-orientacao.blade.php
Declaração de orientação

### 10. relatorio-geral.blade.php
Relatório geral do sistema

### 11. comprovante-submissao.blade.php
Comprovante de submissão do TCC

## Como Usar

### Instalar DomPDF

```bash
composer require barryvdh/laravel-dompdf
```

### Publicar configuração

```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProviderLaravel5"
```

### Copiar views

Copie os arquivos .blade.php para:
```
resources/views/relatorios/
```

### Usar no controller

```php
use Barryvdh\DomPDF\Facade\Pdf;

$pdf = Pdf::loadView('relatorios.certificado', $data);
return $pdf->download('certificado.pdf');
```

## Rotas API para Relatórios

Adicione em `routes/api.php`:

```php
// Relatórios e Documentos
Route::prefix('relatorios')->middleware('auth:sanctum')->group(function () {
    // Calendário de Defesas
    Route::get('/calendario-defesas', [RelatorioController::class, 'calendarioDefesas']);
    
    // TCCs com Recomendações
    Route::get('/tccs-recomendacoes', [RelatorioController::class, 'tccsComRecomendacoes']);
    
    // Matriz de Defesa
    Route::get('/matriz-defesa/{bancaId}', [RelatorioController::class, 'matrizDefesa']);
    
    // Certificado
    Route::get('/certificado/{tccId}', [RelatorioController::class, 'certificado']);
    
    // Ata de Defesa
    Route::get('/ata-defesa/{bancaId}', [RelatorioController::class, 'ataDefesa']);
    
    // Lista de Presença
    Route::get('/lista-presenca/{bancaId}', [RelatorioController::class, 'listaPresenca']);
    
    // Estatísticas por Curso
    Route::get('/estatisticas-curso/{cursoId}', [RelatorioController::class, 'estatisticasCurso']);
    
    // Relatório de Orientações
    Route::get('/orientacoes', [RelatorioController::class, 'relatorioOrientacoes']);
    
    // Declaração de Orientação
    Route::get('/declaracao-orientacao/{orientacaoId}', [RelatorioController::class, 'declaracaoOrientacao']);
    
    // Relatório Geral (Admin)
    Route::get('/geral', [RelatorioController::class, 'relatorioGeral']);
    
    // Comprovante de Submissão
    Route::get('/comprovante-submissao/{tccId}', [RelatorioController::class, 'comprovanteSubmissao']);
});
```

## Exemplos de Uso

### 1. Gerar Calendário de Defesas (PDF)

```bash
GET /api/v1/relatorios/calendario-defesas?formato=pdf&curso_id=xxx&data_inicio=2025-01-01
```

### 2. Gerar Calendário (Excel)

```bash
GET /api/v1/relatorios/calendario-defesas?formato=excel
```

### 3. Gerar Matriz de Defesa

```bash
GET /api/v1/relatorios/matriz-defesa/{bancaId}
```

### 4. Gerar Certificado

```bash
GET /api/v1/relatorios/certificado/{tccId}
```

### 5. TCCs com Recomendações

```bash
GET /api/v1/relatorios/tccs-recomendacoes?formato=pdf&curso_id=xxx
```

## Personalização

### Alterar logo no certificado

Edite a view `certificado.blade.php` e adicione logo:

```html
<img src="{{ asset('images/logo.png') }}" class="brasao">
```

### Alterar cores/layout

Modifique a seção `<style>` de cada view.

### Adicionar campos

Adicione campos nos arrays passados pelo controller e use nas views com `{{ $campo }}`.

## Exportação para Excel

### Criar Export Classes

```php
// app/Exports/CalendarioDefesasExport.php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CalendarioDefesasExport implements FromCollection, WithHeadings
{
    protected $bancas;

    public function __construct($bancas)
    {
        $this->bancas = $bancas;
    }

    public function collection()
    {
        return $this->bancas->map(function ($banca, $index) {
            return [
                'Nº' => $index + 1,
                'Candidato' => $banca->tcc->aluno->usuario->nome_completo,
                'Curso' => $banca->tcc->curso->codigo,
                'Data' => $banca->data_agendada->format('d/m/Y'),
                'Hora' => $banca->data_agendada->format('H:i'),
                'Tipo' => $banca->getTipoFormatado(),
                'Sala' => $banca->local,
                'Tema' => $banca->tcc->titulo,
            ];
        });
    }

    public function headings(): array
    {
        return ['Nº', 'Candidato', 'Curso', 'Data', 'Hora', 'Tipo', 'Sala', 'Tema'];
    }
}
```

## Observações Importantes

1. **Permissões**: Todas as rotas verificam autorização via Policies
2. **Formatos**: Suporta PDF, Excel e JSON
3. **Filtros**: Aceita filtros por curso, data, status, etc.
4. **Layouts**: Certificado é landscape (horizontal), demais portrait (vertical)
5. **Fontes**: Usa Georgia para certificados (mais formal), Arial para relatórios


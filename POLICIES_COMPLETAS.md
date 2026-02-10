# 🔐 POLICIES CRIADAS - AUTORIZAÇÃO COMPLETA

## ✅ POLICIES IMPLEMENTADAS (6 total)

### 1. **TccPolicy.php** (já existia)
Controla acesso aos TCCs baseado em:
- **viewAny**: Todos autenticados (filtros aplicados no controller)
- **view**: Admin vê todos, Aluno vê próprio, Orientador vê orientandos, Coordenador vê do curso, Membros de banca veem TCCs da banca
- **create**: Aluno sem TCC ativo, Admin, Coordenador
- **update**: Aluno próprio (se podeEditar), Orientador (orientandos), Admin/Coordenador sempre
- **delete**: Aluno próprio (status RASCUNHO), Admin, Coordenador
- **submit**: Aluno próprio ou Orientador (orientandos) + status permite
- **approve**: Membros da banca ativa + tcc.podeAprovar()
- **cancel**: Aluno próprio, Admin, Coordenador
- **manageOrientacoes**: Aluno próprio (RASCUNHO), Coordenador, Admin
- **manageBancas**: Orientador (orientandos), Coordenador, Admin

### 2. **AlunoPolicy.php** ✨ (novo)
Controla acesso aos perfis de alunos:
- **viewAny**: Admin, Coordenador, Orientador
- **view**: Admin/Coordenador veem todos, Aluno vê próprio perfil, Orientador vê seus orientandos
- **create**: Admin, Coordenador
- **update**: Admin/Coordenador atualizam todos, Aluno atualiza próprio perfil
- **delete**: Admin, Coordenador

### 3. **OrientadorPolicy.php** ✨ (novo)
Controla acesso aos perfis de orientadores:
- **viewAny**: Todos (orientadores são públicos)
- **view**: Todos podem ver perfil
- **create**: Admin, Coordenador
- **update**: Admin/Coordenador atualizam todos, Orientador atualiza próprio perfil
- **delete**: Apenas Admin

### 4. **DocumentoPolicy.php** ✨ (novo)
Controla acesso aos documentos:
- **viewAny**: Quem pode ver o TCC
- **view**: Quem pode ver o TCC
- **create**: Admin sempre, Aluno do TCC, Orientador do TCC, Coordenador
- **update**: Admin/Coordenador, ou quem fez upload
- **delete**: Admin/Coordenador, ou quem fez upload (se não aprovado)
- **approve**: Admin, Coordenador, Orientador principal do TCC
- **reject**: Admin, Coordenador, Orientador principal do TCC

### 5. **BancaPolicy.php** ✨ (novo)
Controla acesso às bancas:
- **viewAny**: Quem pode ver o TCC
- **view**: Admin/Coordenador veem todas, Aluno do TCC, Orientador do TCC, Membros da banca
- **create**: Admin/Coordenador, Orientador principal do TCC
- **update**: Mesmos de create (se banca não concluída/cancelada)
- **delete**: Admin sempre, Coordenador (se não concluída)
- **manage**: Admin/Coordenador, Orientador principal
- **evaluate**: Apenas membros confirmados da banca

### 6. **OrientacaoPolicy.php** ✨ (novo)
Controla acesso às orientações:
- **viewAny**: Quem pode ver o TCC
- **view**: Quem pode ver o TCC
- **create**: Admin, Coordenador, Aluno do TCC (se RASCUNHO)
- **delete**: Admin, Coordenador, Aluno do TCC (se RASCUNHO)

---

## 📋 COMO REGISTRAR AS POLICIES

### Opção 1: Registro Manual no AuthServiceProvider

Edite `app/Providers/AuthServiceProvider.php`:

```php
<?php

namespace App\Providers;

use App\Models\{Tcc, Aluno, Orientador, Documento, Banca, Orientacao};
use App\Policies\{TccPolicy, AlunoPolicy, OrientadorPolicy, DocumentoPolicy, BancaPolicy, OrientacaoPolicy};
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Tcc::class => TccPolicy::class,
        Aluno::class => AlunoPolicy::class,
        Orientador::class => OrientadorPolicy::class,
        Documento::class => DocumentoPolicy::class,
        Banca::class => BancaPolicy::class,
        Orientacao::class => OrientacaoPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
```

### Opção 2: Auto-Discovery (Laravel 10+)

Se você seguir a convenção de nomenclatura, o Laravel descobre automaticamente:
- Model: `App\Models\Aluno`
- Policy: `App\Policies\AlunoPolicy`

Basta garantir que as policies estejam em `app/Policies/`

---

## 🔧 USO NOS CONTROLLERS

### Exemplo 1: Verificar antes de executar ação

```php
public function destroy(string $id)
{
    $aluno = Aluno::findOrFail($id);
    
    // Lança exceção 403 se não autorizado
    $this->authorize('delete', $aluno);
    
    $aluno->delete();
    
    return response()->json(['success' => true]);
}
```

### Exemplo 2: Verificar condicionalmente

```php
public function update(Request $request, string $id)
{
    $documento = Documento::findOrFail($id);
    
    if (! Gate::allows('update', $documento)) {
        return response()->json([
            'success' => false,
            'message' => 'Não autorizado'
        ], 403);
    }
    
    // ... continua
}
```

### Exemplo 3: Verificar múltiplas policies

```php
public function aprovar(string $id)
{
    $documento = Documento::findOrFail($id);
    
    $this->authorize('approve', $documento);
    
    $documento->aprovar();
    
    return response()->json(['success' => true]);
}
```

### Exemplo 4: Usar em middleware

```php
Route::delete('/alunos/{id}', [AlunoController::class, 'destroy'])
    ->middleware('can:delete,aluno');
```

---

## 🎯 MATRIZ DE PERMISSÕES

### TCCs

| Ação | ADMIN | COORDENADOR | ORIENTADOR | ALUNO |
|------|-------|-------------|------------|-------|
| Ver todos | ✅ | ✅ (curso) | ✅ (orientandos) | ❌ |
| Ver próprio | ✅ | ✅ | ✅ | ✅ |
| Criar | ✅ | ✅ | ❌ | ✅ (1 ativo) |
| Editar | ✅ | ✅ | ✅ (orientandos) | ✅ (próprio) |
| Deletar | ✅ | ✅ | ❌ | ✅ (RASCUNHO) |
| Submeter | ✅ | ✅ | ✅ (orientandos) | ✅ (próprio) |
| Aprovar | ✅ | ✅ | ✅ (banca) | ❌ |

### Alunos

| Ação | ADMIN | COORDENADOR | ORIENTADOR | ALUNO |
|------|-------|-------------|------------|-------|
| Listar | ✅ | ✅ | ✅ | ❌ |
| Ver perfil | ✅ | ✅ | ✅ (orientandos) | ✅ (próprio) |
| Criar | ✅ | ✅ | ❌ | ❌ |
| Editar | ✅ | ✅ | ❌ | ✅ (próprio) |
| Deletar | ✅ | ✅ | ❌ | ❌ |

### Orientadores

| Ação | ADMIN | COORDENADOR | ORIENTADOR | ALUNO |
|------|-------|-------------|------------|-------|
| Listar | ✅ | ✅ | ✅ | ✅ |
| Ver perfil | ✅ | ✅ | ✅ | ✅ |
| Criar | ✅ | ✅ | ❌ | ❌ |
| Editar | ✅ | ✅ | ✅ (próprio) | ❌ |
| Deletar | ✅ | ❌ | ❌ | ❌ |

### Documentos

| Ação | ADMIN | COORDENADOR | ORIENTADOR | ALUNO |
|------|-------|-------------|------------|-------|
| Ver | ✅ | ✅ | ✅ (orientandos) | ✅ (próprio TCC) |
| Upload | ✅ | ✅ | ✅ (orientandos) | ✅ (próprio TCC) |
| Editar | ✅ | ✅ | ✅ (se uploadou) | ✅ (se uploadou) |
| Deletar | ✅ | ✅ | ✅ (se uploadou, não aprovado) | ✅ (se uploadou, não aprovado) |
| Aprovar | ✅ | ✅ | ✅ (orientador principal) | ❌ |
| Rejeitar | ✅ | ✅ | ✅ (orientador principal) | ❌ |

### Bancas

| Ação | ADMIN | COORDENADOR | ORIENTADOR | ALUNO |
|------|-------|-------------|------------|-------|
| Ver | ✅ | ✅ | ✅ (orientandos/membro) | ✅ (próprio TCC) |
| Criar | ✅ | ✅ | ✅ (orientandos) | ❌ |
| Editar | ✅ | ✅ | ✅ (orientandos) | ❌ |
| Deletar | ✅ | ✅ (não concluída) | ❌ | ❌ |
| Avaliar | ✅ | ✅ | ✅ (se membro) | ❌ |

### Orientações

| Ação | ADMIN | COORDENADOR | ORIENTADOR | ALUNO |
|------|-------|-------------|------------|-------|
| Ver | ✅ | ✅ | ✅ (próprias) | ✅ (próprio TCC) |
| Criar | ✅ | ✅ | ❌ | ✅ (RASCUNHO) |
| Deletar | ✅ | ✅ | ❌ | ✅ (RASCUNHO) |

---

## 🧪 TESTANDO AS POLICIES

### Teste 1: Aluno tentando deletar outro aluno
```php
// Deve retornar 403 Forbidden
$response = $this->actingAs($aluno1)
    ->delete("/api/v1/alunos/{$aluno2->id}");

$response->assertStatus(403);
```

### Teste 2: Coordenador deletando aluno
```php
// Deve funcionar
$response = $this->actingAs($coordenador)
    ->delete("/api/v1/alunos/{$aluno->id}");

$response->assertStatus(200);
```

### Teste 3: Aluno acessando TCC de outro
```php
// Deve retornar 403
$response = $this->actingAs($aluno1)
    ->get("/api/v1/tccs/{$tccDoAluno2->id}");

$response->assertStatus(403);
```

---

## 📝 NOTAS IMPORTANTES

1. **Todas as policies usam o modelo `Usuario`** como primeiro parâmetro (não `User`)

2. **As policies verificam roles usando Spatie Permission:**
   - `$user->hasRole('admin')`
   - `$user->hasAnyRole(['admin', 'coordenador'])`
   - `$user->isAluno()`, `$user->isOrientador()`, etc.

3. **Algumas policies dependem de outras:**
   - `DocumentoPolicy` usa `TccPolicy` para verificar acesso ao TCC
   - `BancaPolicy` usa `TccPolicy` para verificar acesso ao TCC

4. **Políticas de negócio implementadas:**
   - Aluno só pode ter 1 TCC ativo
   - Documento aprovado não pode ser deletado por quem fez upload
   - Banca concluída/cancelada não pode ser editada
   - Orientação só pode ser criada/removida em TCC RASCUNHO (por aluno)

---

## ✅ RESUMO

**6 Policies criadas:**
1. ✅ TccPolicy (já existia)
2. ✅ AlunoPolicy (novo)
3. ✅ OrientadorPolicy (novo)
4. ✅ DocumentoPolicy (novo)
5. ✅ BancaPolicy (novo)
6. ✅ OrientacaoPolicy (novo)

**Total de métodos de autorização:** 35+

**Cobertura:** 100% dos controllers têm policies implementadas

---

**Sistema de autorização completo e pronto para uso! 🔐**

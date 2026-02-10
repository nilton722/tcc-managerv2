# 🚀 GUIA RÁPIDO DE INSTALAÇÃO - TCC MANAGER

## ⚡ Instalação em 5 Minutos

### 1️⃣ Pré-requisitos

Certifique-se de ter instalado:
- ✅ PHP 8.3+
- ✅ Composer 2.x
- ✅ MySQL 8.0+
- ✅ Laravel 12 (projeto novo ou existente)

### 2️⃣ Criar Projeto Laravel (se ainda não tiver)

```bash
composer create-project laravel/laravel tcc-manager
cd tcc-manager
```

### 3️⃣ Extrair Arquivos

```bash
# Extrair o arquivo baixado
tar -xzf tcc-manager-backend.tar.gz

# OU usar os arquivos diretamente
cd tcc-manager-backend
```

### 4️⃣ Copiar Arquivos para o Laravel

```bash
# A partir da pasta tcc-manager-backend:

# Migrations
cp tcc-manager-migrations/*.php ../tcc-manager/database/migrations/

# Models
cp tcc-manager-models/*.php ../tcc-manager/app/Models/

# Controllers
mkdir -p ../tcc-manager/app/Http/Controllers/Api
cp tcc-manager-controllers/*.php ../tcc-manager/app/Http/Controllers/Api/

# Seeders
cp tcc-manager-seeders/*.php ../tcc-manager/database/seeders/

# Services
mkdir -p ../tcc-manager/app/Services
cp tcc-manager-services/*.php ../tcc-manager/app/Services/

# Policies
cp tcc-manager-policies/*.php ../tcc-manager/app/Policies/

# Requests
mkdir -p ../tcc-manager/app/Http/Requests
cp tcc-manager-requests/*.php ../tcc-manager/app/Http/Requests/

# Resources
mkdir -p ../tcc-manager/app/Http/Resources
cp tcc-manager-resources/*.php ../tcc-manager/app/Http/Resources/

# Rotas (adicionar ao final do arquivo)
cat tcc-manager-routes/api.php >> ../tcc-manager/routes/api.php
```

### 5️⃣ Instalar Dependências do Composer

```bash
cd ../tcc-manager

composer require laravel/sanctum
composer require spatie/laravel-permission
composer require spatie/laravel-medialibrary
composer require spatie/laravel-query-builder
composer require maatwebsite/excel
```

### 6️⃣ Configurar Banco de Dados

Edite o arquivo `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tcc_manager
DB_USERNAME=root
DB_PASSWORD=sua_senha_aqui

FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
```

### 7️⃣ Criar Banco de Dados

```bash
# No MySQL
mysql -u root -p

CREATE DATABASE tcc_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

### 8️⃣ Executar Migrations e Seeders

```bash
# Executar migrations
php artisan migrate

# Executar seeders (dados iniciais)
php artisan db:seed

# OU tudo de uma vez (limpa e recria)
php artisan migrate:fresh --seed
```

### 9️⃣ Configurar Storage

```bash
php artisan storage:link
```

### 🔟 Iniciar Servidor

```bash
php artisan serve
```

Acesse: `http://localhost:8000`

## 🧪 TESTAR A API

### Opção 1: Postman

1. Abra o Postman
2. Importe: `tcc-manager-docs/TCC_Manager_API.postman_collection.json`
3. Configure `base_url` = `http://localhost:8000/api/v1`
4. Execute o request de Login
5. Teste os outros endpoints

### Opção 2: cURL

**Login:**
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "aluno@tccmanager.com",
    "password": "password123"
  }'
```

**Pegar o Token da Resposta e Usar:**
```bash
# Substitua YOUR_TOKEN pelo token recebido
curl -X GET http://localhost:8000/api/v1/tccs \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 👥 CREDENCIAIS PADRÃO

| Tipo | Email | Senha |
|------|-------|-------|
| Admin | admin@tccmanager.com | password123 |
| Coordenador | coordenador@tccmanager.com | password123 |
| Orientador | orientador@tccmanager.com | password123 |
| Aluno | aluno@tccmanager.com | password123 |

## 🎯 PRIMEIROS PASSOS

### 1. Fazer Login
```bash
POST /api/v1/auth/login
{
  "email": "aluno@tccmanager.com",
  "password": "password123"
}
```

### 2. Criar um TCC
```bash
POST /api/v1/tccs
Authorization: Bearer {token}
{
  "titulo": "Meu Primeiro TCC",
  "tipo_trabalho": "TCC",
  "resumo": "Descrição do trabalho..."
}
```

### 3. Upload de Documento
```bash
POST /api/v1/documentos/{tccId}
Authorization: Bearer {token}
Content-Type: multipart/form-data

tipo_documento_id: {uuid}
arquivo: {file}
```

### 4. Listar TCCs
```bash
GET /api/v1/tccs?status=RASCUNHO&include=aluno,curso
Authorization: Bearer {token}
```

## 🐛 TROUBLESHOOTING

### Erro: "Class not found"
```bash
composer dump-autoload
```

### Erro: "SQLSTATE[42000]"
```bash
# Verifique as credenciais no .env
# Certifique-se que o banco existe
```

### Erro: "Storage link not found"
```bash
php artisan storage:link
```

### Limpar Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Recriar Banco do Zero
```bash
php artisan migrate:fresh --seed
```

## 📚 DOCUMENTAÇÃO COMPLETA

- **INDEX.md** - Índice completo do projeto
- **README.md** - Documentação detalhada
- **COMPONENTES_ADICIONAIS.md** - Componentes futuros
- **Postman Collection** - Testes de API

## 🎓 ESTRUTURA DO BANCO

**20 Tabelas:**
- instituicoes, usuarios, departamentos
- cursos, linhas_pesquisa
- alunos, orientadores, orientacoes
- tccs, tipos_documento, documentos
- bancas, membros_banca, avaliacoes
- templates_cronograma, etapas_template
- cronogramas_tcc, etapas_tcc
- notificacoes, auditorias

## 🔐 SEGURANÇA

- ✅ Autenticação via Laravel Sanctum
- ✅ RBAC com Spatie Permission
- ✅ Policies para autorização
- ✅ Validação em todos endpoints
- ✅ Hash SHA-256 para integridade de arquivos
- ✅ Auditoria completa de ações

## ⚙️ FUNCIONALIDADES PRINCIPAIS

1. ✅ Gestão de Usuários (CRUD + Auth)
2. ✅ Gestão de TCCs (CRUD + Workflow)
3. ✅ Gestão de Documentos (Upload + Versionamento)
4. ✅ Sistema de Permissões (RBAC)
5. ✅ Cronogramas e Etapas
6. ✅ Auditoria de Ações

## 📊 PRÓXIMOS PASSOS

1. Implementar controllers de Banca e Orientação
2. Criar sistema de Notificações
3. Desenvolver Dashboard com estatísticas
4. Implementar frontend React + TypeScript
5. Adicionar testes automatizados
6. Configurar CI/CD

## 🆘 PRECISA DE AJUDA?

1. Consulte a documentação em `INDEX.md`
2. Verifique os exemplos na Postman Collection
3. Analise os seeders para ver estrutura de dados
4. Revise as policies para entender permissões

## ✅ CHECKLIST DE INSTALAÇÃO

- [ ] PHP 8.3+ instalado
- [ ] Composer instalado
- [ ] MySQL rodando
- [ ] Projeto Laravel criado
- [ ] Arquivos copiados
- [ ] Dependências instaladas
- [ ] .env configurado
- [ ] Banco de dados criado
- [ ] Migrations executadas
- [ ] Seeders executados
- [ ] Storage link criado
- [ ] Servidor iniciado
- [ ] API testada com sucesso

---

**🎉 Instalação Concluída!**

Acesse: `http://localhost:8000`
API: `http://localhost:8000/api/v1`

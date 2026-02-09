# TCC Manager - Sistema de Gerenciamento de TCCs

Sistema completo para gerenciamento de Trabalhos de Conclusão de Curso (TCCs), Monografias, Dissertações e Teses desenvolvido com Laravel 12 e MySQL.

## 📋 Índice

- [Funcionalidades](#funcionalidades)
- [Tecnologias Utilizadas](#tecnologias-utilizadas)
- [Pré-requisitos](#pré-requisitos)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Uso](#uso)
- [API Endpoints](#api-endpoints)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Testes](#testes)

## ✨ Funcionalidades

### Módulos Principais

1. **Gestão de Usuários**
   - Cadastro e autenticação (Sanctum)
   - Perfis: Aluno, Orientador, Coordenador, Administrador
   - Controle de permissões (RBAC)
   - Verificação de email

2. **Gestão de TCCs**
   - Criação e edição de trabalhos
   - Fluxo de status (Rascunho → Aprovado)
   - Vinculação com orientadores
   - Palavras-chave e resumos

3. **Sistema de Orientação**
   - Orientadores e coorientadores
   - Controle de carga de orientação
   - Histórico de orientações

4. **Gestão de Documentos**
   - Upload de múltiplas versões
   - Aprovação/rejeição por orientador
   - Controle de tipos de documentos
   - Validação de formatos e tamanhos

5. **Bancas Examinadoras**
   - Agendamento de bancas
   - Convite a membros
   - Registro de avaliações
   - Cálculo automático de notas

6. **Cronogramas e Etapas**
   - Templates personalizáveis
   - Acompanhamento de progresso
   - Alertas de prazos

7. **Notificações**
   - Sistema de notificações in-app
   - Emails automáticos
   - Lembretes de prazos

8. **Relatórios e Dashboard**
   - Estatísticas por curso
   - Desempenho de orientadores
   - Taxa de aprovação

9. **Auditoria**
   - Log de todas as ações
   - Rastreabilidade completa

## 🛠 Tecnologias Utilizadas

- **Laravel 12** - Framework PHP
- **MySQL 8.0+** - Banco de dados
- **Laravel Sanctum** - Autenticação API
- **Spatie Laravel Permission** - Gerenciamento de permissões
- **Spatie Query Builder** - Query builder para APIs
- **Spatie Laravel Media Library** - Gestão de arquivos
- **Laravel Telescope** - Debug e monitoramento

## 📦 Pré-requisitos

- PHP 8.3+
- Composer 2.x
- MySQL 8.0+
- Node.js 18+ (para frontend)
- Git

## 🚀 Instalação

### 1. Clonar o repositório

```bash
git clone https://github.com/sua-organizacao/tcc-manager.git
cd tcc-manager
```

### 2. Instalar dependências

```bash
composer install
```

### 3. Configurar ambiente

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configurar banco de dados

Edite o arquivo `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tcc_manager
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

### 5. Criar banco de dados

```bash
mysql -u root -p
CREATE DATABASE tcc_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

### 6. Executar migrations e seeders

```bash
php artisan migrate --seed
```

### 7. Criar link de storage

```bash
php artisan storage:link
```

### 8. Instalar Telescope (desenvolvimento)

```bash
php artisan telescope:install
php artisan migrate
```

### 9. Iniciar servidor

```bash
php artisan serve
```

A aplicação estará disponível em: `http://localhost:8000`

## ⚙️ Configuração

### Configurar Email

Edite `.env` para configurar o servidor de email:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=seu_username
MAIL_PASSWORD=sua_senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tccmanager.com
MAIL_FROM_NAME="TCC Manager"
```

### Configurar Storage (S3 - Opcional)

Para produção, configure Amazon S3:

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=sua_key
AWS_SECRET_ACCESS_KEY=sua_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=tcc-manager-files
```

### Configurar Filas

Para processar jobs em background:

```bash
# .env
QUEUE_CONNECTION=database

# Executar worker
php artisan queue:work
```

## 📖 Uso

### Primeiro Acesso

Após seeders, usuários padrão são criados:

- **Admin**: admin@tccmanager.com / password123
- **Coordenador**: coordenador@tccmanager.com / password123
- **Orientador**: orientador@tccmanager.com / password123
- **Aluno**: aluno@tccmanager.com / password123

### Comandos Artisan Úteis

```bash
# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Gerar documentação API
php artisan route:list

# Executar testes
php artisan test

# Criar usuário admin
php artisan make:admin

# Backup do banco
php artisan backup:run

# Enviar notificações pendentes
php artisan notifications:send
```

## 🔌 API Endpoints

### Autenticação

```http
POST   /api/v1/auth/register          - Registrar usuário
POST   /api/v1/auth/login              - Login
POST   /api/v1/auth/logout             - Logout
GET    /api/v1/auth/me                 - Dados do usuário
PUT    /api/v1/auth/profile            - Atualizar perfil
POST   /api/v1/auth/change-password    - Alterar senha
```

### TCCs

```http
GET    /api/v1/tccs                    - Listar TCCs
POST   /api/v1/tccs                    - Criar TCC
GET    /api/v1/tccs/{id}               - Ver TCC
PUT    /api/v1/tccs/{id}               - Atualizar TCC
DELETE /api/v1/tccs/{id}               - Excluir TCC

POST   /api/v1/tccs/{id}/submeter      - Submeter para banca
POST   /api/v1/tccs/{id}/cancelar      - Cancelar TCC
GET    /api/v1/tccs/{id}/dashboard     - Dashboard do TCC
```

### Documentos

```http
GET    /api/v1/tccs/{tccId}/documentos              - Listar documentos
POST   /api/v1/tccs/{tccId}/documentos              - Upload documento
GET    /api/v1/tccs/{tccId}/documentos/{id}         - Ver documento
DELETE /api/v1/tccs/{tccId}/documentos/{id}         - Excluir documento

POST   /api/v1/tccs/{tccId}/documentos/{id}/aprovar  - Aprovar documento
POST   /api/v1/tccs/{tccId}/documentos/{id}/rejeitar - Rejeitar documento
POST   /api/v1/tccs/{tccId}/documentos/{id}/download - Download documento
```

### Bancas

```http
GET    /api/v1/tccs/{tccId}/bancas                  - Listar bancas
POST   /api/v1/tccs/{tccId}/bancas                  - Criar banca
GET    /api/v1/tccs/{tccId}/bancas/{id}             - Ver banca
PUT    /api/v1/tccs/{tccId}/bancas/{id}             - Atualizar banca

POST   /api/v1/tccs/{tccId}/bancas/{id}/membros     - Adicionar membro
POST   /api/v1/tccs/{tccId}/bancas/{id}/avaliacoes  - Avaliar TCC
```

### Notificações

```http
GET    /api/v1/notificacoes                   - Listar notificações
GET    /api/v1/notificacoes/nao-lidas         - Não lidas
POST   /api/v1/notificacoes/{id}/marcar-lida  - Marcar como lida
POST   /api/v1/notificacoes/marcar-todas-lidas - Marcar todas
```

### Exemplos de Requisições

#### Login

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "aluno@tccmanager.com",
    "password": "password123"
  }'
```

#### Criar TCC

```bash
curl -X POST http://localhost:8000/api/v1/tccs \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "titulo": "Sistema de Recomendação com IA",
    "tipo_trabalho": "TCC",
    "curso_id": "uuid-do-curso",
    "linha_pesquisa_id": "uuid-da-linha",
    "resumo": "Este trabalho propõe...",
    "palavras_chave": ["IA", "Machine Learning", "Recomendação"]
  }'
```

## 📁 Estrutura do Projeto

```
tcc-manager/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       ├── TccController.php
│   │   │       ├── DocumentoController.php
│   │   │       └── BancaController.php
│   │   ├── Requests/
│   │   ├── Resources/
│   │   └── Middleware/
│   ├── Models/
│   │   ├── Usuario.php
│   │   ├── Tcc.php
│   │   ├── Aluno.php
│   │   ├── Orientador.php
│   │   └── ...
│   ├── Services/
│   │   ├── TccService.php
│   │   ├── AuthService.php
│   │   └── NotificationService.php
│   ├── Policies/
│   │   └── TccPolicy.php
│   └── Notifications/
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── api.php
│   └── web.php
├── tests/
│   ├── Feature/
│   └── Unit/
└── storage/
    └── app/
        └── uploads/
```

## 🧪 Testes

### Executar todos os testes

```bash
php artisan test
```

### Executar testes específicos

```bash
php artisan test --filter=TccTest
php artisan test --testsuite=Feature
```

### Cobertura de código

```bash
php artisan test --coverage
```

## 📊 Monitoramento

### Telescope

Acesse o Telescope para debug:

```
http://localhost:8000/telescope
```

### Logs

Logs são armazenados em `storage/logs/laravel.log`

## 🔐 Segurança

- Autenticação via Sanctum (tokens JWT)
- Validação de inputs em todas as rotas
- Proteção CSRF
- Políticas de autorização (Policies)
- Hash de senhas (bcrypt)
- Rate limiting nas APIs
- Auditoria completa de ações

## 📝 Licença

Este projeto está sob licença MIT.

## 👥 Contribuindo

1. Fork o projeto
2. Crie uma branch (`git checkout -b feature/nova-funcionalidade`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova funcionalidade'`)
4. Push para a branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

## 📞 Suporte

Para suporte, envie email para: suporte@tccmanager.com

---

Desenvolvido com ❤️ usando Laravel# tcc-managerv2

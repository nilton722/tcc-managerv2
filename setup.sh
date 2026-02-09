#!/bin/bash

# TCC Manager - Script de Instalação
# Execute este script para configurar o projeto

echo "========================================="
echo "  TCC Manager - Instalação Automática"
echo "========================================="
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Verificar PHP
echo -e "${YELLOW}Verificando PHP...${NC}"
if ! command -v php &> /dev/null; then
    echo -e "${RED}PHP não encontrado. Instale PHP 8.3+ primeiro.${NC}"
    exit 1
fi

PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
echo -e "${GREEN}✓ PHP $PHP_VERSION encontrado${NC}"

# Verificar Composer
echo -e "${YELLOW}Verificando Composer...${NC}"
if ! command -v composer &> /dev/null; then
    echo -e "${RED}Composer não encontrado. Instale primeiro.${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Composer encontrado${NC}"

# Verificar MySQL
echo -e "${YELLOW}Verificando MySQL...${NC}"
if ! command -v mysql &> /dev/null; then
    echo -e "${RED}MySQL não encontrado. Instale MySQL 8.0+ primeiro.${NC}"
    exit 1
fi
echo -e "${GREEN}✓ MySQL encontrado${NC}"

echo ""
echo "========================================="
echo "  Iniciando instalação..."
echo "========================================="
echo ""

# 1. Instalar dependências
echo -e "${YELLOW}1. Instalando dependências do Composer...${NC}"
composer install --no-interaction --prefer-dist --optimize-autoloader
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Dependências instaladas${NC}"
else
    echo -e "${RED}✗ Erro ao instalar dependências${NC}"
    exit 1
fi

# 2. Copiar .env
if [ ! -f .env ]; then
    echo -e "${YELLOW}2. Criando arquivo .env...${NC}"
    cp .env.example .env
    echo -e "${GREEN}✓ Arquivo .env criado${NC}"
else
    echo -e "${YELLOW}2. Arquivo .env já existe${NC}"
fi

# 3. Gerar chave da aplicação
echo -e "${YELLOW}3. Gerando chave da aplicação...${NC}"
php artisan key:generate --force
echo -e "${GREEN}✓ Chave gerada${NC}"

# 4. Perguntar credenciais do banco
echo ""
echo -e "${YELLOW}4. Configuração do Banco de Dados${NC}"
read -p "Nome do banco de dados (padrão: tcc_manager): " DB_NAME
DB_NAME=${DB_NAME:-tcc_manager}

read -p "Usuário MySQL (padrão: root): " DB_USER
DB_USER=${DB_USER:-root}

read -sp "Senha MySQL: " DB_PASS
echo ""

read -p "Host MySQL (padrão: 127.0.0.1): " DB_HOST
DB_HOST=${DB_HOST:-127.0.0.1}

# Atualizar .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env
sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env

echo -e "${GREEN}✓ Configurações do banco atualizadas${NC}"

# 5. Criar banco de dados
echo -e "${YELLOW}5. Criando banco de dados...${NC}"
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Banco de dados criado/verificado${NC}"
else
    echo -e "${RED}✗ Erro ao criar banco de dados${NC}"
    echo "Verifique suas credenciais e tente novamente."
    exit 1
fi

# 6. Executar migrations
echo -e "${YELLOW}6. Executando migrations...${NC}"
php artisan migrate --force
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Migrations executadas${NC}"
else
    echo -e "${RED}✗ Erro nas migrations${NC}"
    exit 1
fi

# 7. Executar seeders
echo -e "${YELLOW}7. Populando banco de dados (seeders)...${NC}"
read -p "Deseja executar os seeders? (s/n): " RUN_SEEDERS
if [ "$RUN_SEEDERS" = "s" ] || [ "$RUN_SEEDERS" = "S" ]; then
    php artisan db:seed --force
    echo -e "${GREEN}✓ Seeders executados${NC}"
fi

# 8. Criar link de storage
echo -e "${YELLOW}8. Criando link simbólico de storage...${NC}"
php artisan storage:link
echo -e "${GREEN}✓ Link criado${NC}"

# 9. Instalar Telescope (opcional)
echo -e "${YELLOW}9. Instalar Laravel Telescope?${NC}"
read -p "Telescope é útil para debug (desenvolvimento). Instalar? (s/n): " INSTALL_TELESCOPE
if [ "$INSTALL_TELESCOPE" = "s" ] || [ "$INSTALL_TELESCOPE" = "S" ]; then
    composer require laravel/telescope --dev
    php artisan telescope:install
    php artisan migrate
    echo -e "${GREEN}✓ Telescope instalado${NC}"
fi

# 10. Limpar caches
echo -e "${YELLOW}10. Limpando caches...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo -e "${GREEN}✓ Caches limpos${NC}"

# 11. Criar pastas necessárias
echo -e "${YELLOW}11. Criando estrutura de pastas...${NC}"
mkdir -p storage/app/uploads
mkdir -p storage/app/public/documentos
mkdir -p storage/logs
chmod -R 775 storage bootstrap/cache
echo -e "${GREEN}✓ Pastas criadas${NC}"

echo ""
echo "========================================="
echo -e "${GREEN}  ✓ Instalação concluída!${NC}"
echo "========================================="
echo ""
echo "Para iniciar o servidor de desenvolvimento:"
echo -e "${YELLOW}php artisan serve${NC}"
echo ""
echo "Acesse: http://localhost:8000"
echo ""

if [ "$RUN_SEEDERS" = "s" ] || [ "$RUN_SEEDERS" = "S" ]; then
    echo "Usuários de teste criados:"
    echo "  Admin:       admin@tccmanager.com / password123"
    echo "  Coordenador: coordenador@tccmanager.com / password123"
    echo "  Orientador:  orientador@tccmanager.com / password123"
    echo "  Aluno:       aluno@tccmanager.com / password123"
    echo ""
fi

if [ "$INSTALL_TELESCOPE" = "s" ] || [ "$INSTALL_TELESCOPE" = "S" ]; then
    echo "Telescope disponível em: http://localhost:8000/telescope"
    echo ""
fi

echo -e "${GREEN}Bom desenvolvimento! 🚀${NC}"
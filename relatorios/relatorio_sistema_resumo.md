# Relatório — Visão Geral do Sistema VeiGest

Este documento oferece uma visão de alto nível da aplicação VeiGest: arquitetura, componentes principais, fluxo de execução e comandos essenciais para developers/ops.

## 1. Arquitetura
- Modelo: Yii2 Advanced Template (apps: `frontend`, `backend`, `console`, pasta `common`).
- Padrão: MVC com ActiveRecord (MySQL).
- Containers: Docker Compose (serviços `frontend`, `backend`, `mysql`, opcional `phpmyadmin`).

## 2. Componentes Principais
- `frontend/`: UI pública e painel (layouts, assets, controllers/views/models de frontend).
- `backend/`: painel administrativo e APIs internas.
- `common/`: models reutilizáveis, widgets, mail, fixtures.
- `console/`: comandos CLI, migrations em `console/migrations`.
- `vendor/`: dependências Composer.

## 3. Base de Dados
- Schema de referência em `console/migrations/m251121_000000_veigest_consolidated_migration.php` e/ou `database.sql`.
- Tabelas chave: `users`, `companies`, `vehicles`, `maintenances`, `fuel_logs`, `alerts`, `documents`.

## 4. Fluxo de Request
1. Entrada: `frontend/web/index.php` (ou `backend/web/index.php`).
2. Router: parâmetro `?r=controller/action` (pretty URLs por padrão comentadas).
3. Controller -> Model (ActiveRecord) -> View.

## 5. Autenticação e Permissões
- Login via `SiteController` (frontend). Roles/permissões implementadas via RBAC (se habilitado nas migrations).

## 6. Deploy Local (rápido)
1. Copiar `.env.example` → `.env` e ajustar credenciais.
2. Docker: `docker-compose up --build` (na raiz do projecto) para levantar `frontend`, `backend`, `mysql`.
3. Executar migrations se necessário (via container ou `yii migrate` no `console/`).

## 7. Comandos úteis
- Iniciar containers: `docker-compose up -d --build`
- Entrar no container PHP: `docker exec -it <container> bash`
- Rodar migrations: `php yii migrate --interactive=0` (no ambiente console configurado)
- Linter PHP: `php -l <file.php>`

## 8. Onde começar para novos colaboradores
- Ler `README.md` na raiz e `veigest/README.md`.
- Examinar `console/migrations` (schema) e `frontend/config/main.php` (configuração app).

---
Arquivo de referência: `console/migrations/m251121_000000_veigest_consolidated_migration.php`.

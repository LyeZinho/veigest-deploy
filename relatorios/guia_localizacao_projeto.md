# Guia de Localização — Onde encontrar cada parte do código

Este documento ajuda a localizar rapidamente ficheiros/funcionalidades dentro do projeto VeiGest.

## Estrutura de topo
- `veigest/` — aplicação Yii2 (frontend, backend, console, common).
- `relatorios/` — documentação e relatórios (este lugar).
- `docker-compose.yml` — orquestração local.

## Mapas rápidos
- Rotas e controllers:
  - `frontend/controllers/` — interface pública e dashboard.
  - `backend/controllers/` — administração.
- Views:
  - `frontend/views/<controller>/` — templates de UI.
  - `frontend/views/layouts/` — layouts disponíveis (main, dashboard, login).
- Models / Lógica de domínio:
  - `common/models/` — models partilhados (User, Vehicle, Maintenance, FuelLog, Alert, Document).
  - `frontend/models/` — search models, formulários específicos.
- Migrations / Schema:
  - `console/migrations/` — arquivo de migrações (ex: m251121_000000_veigest_consolidated_migration.php).
  - `database.sql` — dump / referência.
- Assets / CSS / JS:
  - `frontend/assets/` e `template/css/` e `template/public/images`.
- Configurações:
  - `frontend/config/main.php` e `frontend/config/main-local.php` — configurações da app frontend.
  - `common/config/` — configurações partilhadas.

## Localizar funcionalidades chave
- Login: `frontend/controllers/SiteController.php` e `frontend/views/site/login.php`.
- Dashboard: `frontend/controllers/DashboardController.php` e `frontend/views/dashboard/index.php`.
- Relatórios: `frontend/controllers/ReportController.php` e `frontend/views/report/`.
- Migrations: `veigest/console/migrations/`.

## Debug rápido
- Logs: `frontend/runtime/logs/` e `backend/runtime/logs/`.
- Verificar dependências: `composer.json`.

---
Use este guia como índice rápido quando estiver a navegar no repositório.

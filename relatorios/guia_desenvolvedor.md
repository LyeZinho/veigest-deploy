# Guia do Desenvolvedor — Como contribuir e expandir o VeiGest

Este guia descreve passos práticos para desenvolver novas funcionalidades no VeiGest, seguindo as convenções do projecto.

## 1. Preparação do ambiente
- Instalar dependências: `composer install` na pasta `veigest/`.
- Configurar `.env` (copiar `.env.example`), ajustar credenciais DB.
- Levantar serviços: `docker-compose up -d --build` (na raiz do projecto).

## 2. Fluxo de trabalho comum
1. Criar branch: `git checkout -b feat/minha-funcionalidade`.
2. Implementar backend (model + migration + controller).
3. Implementar frontend (view + assets) ou backend UI conforme necessário.
4. Escrever testes (Codeception para integrações, PHPUnit para unit).
5. Fazer commit e abrir PR com descrição e screenshots.

## 3. Adicionar Model (ActiveRecord)
1. Criar migration em `console/migrations`: `yii migrate/create create_table_xxx`.
2. Executar migration localmente: `php yii migrate` (ou via container).
3. Criar model em `common/models` ou `frontend/models` com `extends \yii\db\ActiveRecord`.
4. Definir `rules()`, `attributeLabels()` e relações `getRelations()`.

## 4. Adicionar Controller + Actions
- Controllers de frontend: `frontend/controllers/`. Backend: `backend/controllers/`.
- Ações públicas/dependentes de autenticação devem usar `AccessControl` em `behaviors()`.
- Para respostas JSON: use `Yii::$app->response->format = Response::FORMAT_JSON;`.

## 5. Views e Assets
- Views em `frontend/views/<controller>/` — usar `Yii` helpers (`Html`, `Url`, `ActiveForm`).
- Assets: registrar no `assets` folder e usar `AssetBundle` se precisar incluir CSS/JS.

## 6. Migrations e DB
- Todas as alterações de schema passam por `console/migrations`.
- Use tipagem coerente com collation utf8mb4 e índices para FK.

## 7. Testes
- Tests de API e integração em `api-tests/` e `veigest/frontend/tests/`.
- Rodar Codeception: `vendor/bin/codecept run` (ou via container).

## 8. Exemplo rápido — CRUD de `Device`
1. Criar migration: `php yii migrate/create create_device_table`
2. Model: `common/models/Device.php` com relações a `company_id`.
3. Controller: `frontend/controllers/DeviceController.php` com ações `index, view, create, update, delete`.
4. Views: `frontend/views/device/` com GridView e ActiveForm.

## 9. Convenções e boas práticas
- Não alterar esquema em produção sem migrations.
- Evitar consultas N+1: usar `with()` ou `joinWith()`.
- Validar e escapar saída nas Views.

---
Referências: `frontend/controllers`, `common/models`, `console/migrations`.

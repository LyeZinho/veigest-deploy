# Guia Prático — Como Implementar Funcionalidades no VeiGest

Passo-a-passo prático com exemplos para adicionar uma nova funcionalidade (ex.: CRUD de `Device`).

## 1. Planeamento
- Defina entidade e campos necessários.
- Verifique se similar já existe em `common/models`.

## 2. Criar Migration
1. Criar: `php yii migrate/create create_device_table` (gera arquivo em `console/migrations`).
2. Editar up()/down(): definir colunas, índices e FKs.
3. Aplicar: `php yii migrate` (ou via container).

## 3. Model (ActiveRecord)
- Criar `common/models/Device.php`:
  - extends `\yii\db\ActiveRecord`.
  - rules(), attributeLabels(), relations `getCompany()`.

Exemplo mínimo:
```
class Device extends \yii\db\ActiveRecord {
  public static function tableName(){ return 'device'; }
  public function rules(){ return [['name','required']]; }
}
```

## 4. Controller
- Criar `frontend/controllers/DeviceController.php` com ações:
  - `actionIndex()` — lista com `ActiveDataProvider`.
  - `actionView($id)` — detalhe.
  - `actionCreate()` / `actionUpdate()` — forms com `load()` + `save()`.
  - `actionDelete($id)` — `findModel($id)->delete()`.

## 5. Views (CRUD)
- Gerar views: `frontend/views/device/index.php`, `view.php`, `_form.php`.
- Usar `GridView` para index e `ActiveForm` para create/update.

## 6. Rotas e Navegação
- Adicionar link no menu/layout: editar `frontend/views/layouts/dashboard.php` ou `_navbar.php`.

## 7. Persistência e Validação
- Regras de validação em `rules()` e validação adicional em `beforeSave()` se necessário.

## 8. Testes
- Unit tests para `Device` model.
- Acceptance/integration para páginas CRUD com Codeception.

## 9. CI / Deploy
- Executar migrations no ambiente de staging antes de production.
- Atualizar containers e executar `composer install --no-dev` em produção.

## 10. Checklist antes do PR
- Cobrir com testes básicos.
- Documentar nova migration e endpoints.
- Incluir screenshots e instruções de roll-back se necessário.

---
Exemplo de referência: ver `common/models/Maintenance.php` e `console/migrations/...`.

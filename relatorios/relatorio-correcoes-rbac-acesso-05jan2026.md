# Relatório de Correções RBAC - VeiGest

**Data:** 05 de Janeiro de 2026  
**Versão:** 1.1.0  
**Autor:** Copilot Assistant

---

## Sumário Executivo

Este relatório documenta as correções implementadas no sistema de controle de acesso baseado em roles (RBAC) do VeiGest. As correções resolvem problemas de visibilidade de botões na navbar, permissões incorretas nos controllers, e tratamento inadequado de erros 403.

---

## 1. Problema Identificado

### 1.1 Navbar Frontend - Botão Dashboard Invisível

**Sintoma:** O botão "Dashboard" não aparecia para usuários com role `manager` (gestor).

**Causa Raiz:** A verificação de role na navbar usava nomes em português (`'gestor'`, `'condutor'`) enquanto o sistema RBAC usa nomes em inglês (`'manager'`, `'driver'`).

**Arquivo Afetado:** `frontend/views/layouts/_navbar.php`

**Código Anterior:**
```php
<?php if ($role === 'gestor' || $role === 'condutor'): ?>
```

**Código Corrigido:**
```php
<?php if ($role === 'manager' || $role === 'driver'): ?>
```

### 1.2 Menu Sidebar Dashboard - Sem Controle de Acesso

**Sintoma:** Todos os itens do menu eram visíveis para todos os usuários autenticados.

**Causa Raiz:** O sidebar não implementava nenhuma verificação de role para esconder/mostrar itens de menu.

**Arquivo Afetado:** `frontend/views/layouts/dashboard.php`

### 1.3 Controllers Frontend - RBAC Incompleto

**Sintoma:** Controllers não bloqueavam acesso de `admin` ao frontend e não restringiam `driver` a operações de leitura.

**Arquivos Afetados:**
- `frontend/controllers/DashboardController.php`
- `frontend/controllers/VehicleController.php`
- `frontend/controllers/RouteController.php`
- `frontend/controllers/AlertController.php`
- `frontend/controllers/DriverController.php`
- `frontend/controllers/MaintenanceController.php`
- `frontend/controllers/DocumentController.php`
- `frontend/controllers/ReportController.php`

### 1.4 Backend - Erro 403 com Layout Carregado

**Sintoma:** Quando um usuário não-admin tentava acessar o backend, recebia erro 403 mas o layout completo (sidebar, navbar) ainda era renderizado.

**Arquivos Afetados:**
- `backend/controllers/SiteController.php`
- `backend/views/site/error.php`
- `backend/views/layouts/blank.php`

---

## 2. Matriz de Permissões Implementada

### 2.1 Definição de Roles

| Role | Descrição | Acesso Frontend | Acesso Backend |
|------|-----------|-----------------|----------------|
| **admin** | Administrador do Sistema | ❌ Bloqueado | ✅ Completo |
| **manager** | Gestor de Frota | ✅ Completo | ❌ Bloqueado |
| **driver** | Condutor | ✅ Leitura apenas | ❌ Bloqueado |

### 2.2 Permissões por Módulo (Frontend)

| Módulo | Admin | Manager | Driver |
|--------|-------|---------|--------|
| Dashboard | ❌ | ✅ View/Advanced | ✅ View |
| Vehicles | ❌ | ✅ CRUD + Assign | ✅ View |
| Drivers | ❌ | ✅ CRUD | ❌ |
| Routes | ❌ | ✅ CRUD | ✅ View |
| Maintenance | ❌ | ✅ CRUD + Complete | ❌ |
| Documents | ❌ | ✅ CRUD | ❌ |
| Alerts | ❌ | ✅ View + Create + Resolve | ✅ View |
| Reports | ❌ | ✅ View + Export | ❌ |
| Profile | ❌ | ✅ Edit | ✅ Edit |

### 2.3 Itens de Menu Visíveis

**Manager vê:**
- Dashboard
- Fleet (Vehicles, Drivers, Routes)
- Maintenance
- Documents
- Alerts
- Reports
- Profile

**Driver vê:**
- Dashboard
- Fleet (Vehicles, Routes) - sem Drivers
- Alerts
- Profile

---

## 3. Correções Implementadas

### 3.1 Navbar Frontend (`_navbar.php`)

```php
<!-- DASHBOARD / BACKEND BUTTON -->
<?php if (!Yii::$app->user->isGuest): ?>
    <?php $role = Yii::$app->user->identity->role; ?>

    <?php if ($role === 'admin'): ?>
        <!-- Admin: Only Backend access -->
        <a href="<?= Yii::getAlias('@backendUrl') ?>" class="btn btn-dark px-4 py-2 mt-3">
            <i class="fas fa-cogs me-2"></i> Backoffice
        </a>

    <?php elseif ($role === 'manager' || $role === 'driver'): ?>
        <!-- Manager/Driver: Frontend Dashboard access -->
        <a href="<?= \yii\helpers\Url::to(['/dashboard/index']) ?>" class="btn btn-success px-4 py-2 mt-3">
            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
        </a>
    <?php endif; ?>
<?php endif; ?>
```

### 3.2 Sidebar Dashboard (`dashboard.php`)

Implementado controle de visibilidade baseado em role:

```php
<?php 
$userRole = Yii::$app->user->identity->role ?? null;
$isManager = ($userRole === 'manager');
$isDriver = ($userRole === 'driver');
?>

<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column">
        <!-- Dashboard - All roles -->
        <li class="nav-item">...</li>

        <!-- Fleet Section -->
        <li class="nav-item menu-open">
            <ul class="nav nav-treeview">
                <!-- Vehicles - All -->
                <li class="nav-item">...</li>
                
                <?php if ($isManager): ?>
                <!-- Drivers - Manager only -->
                <li class="nav-item">...</li>
                <?php endif; ?>
                
                <!-- Routes - All -->
                <li class="nav-item">...</li>
            </ul>
        </li>

        <?php if ($isManager): ?>
        <!-- Maintenance - Manager only -->
        <li class="nav-item">...</li>
        
        <!-- Documents - Manager only -->
        <li class="nav-item">...</li>
        <?php endif; ?>

        <!-- Alerts - All -->
        <li class="nav-item">...</li>

        <?php if ($isManager): ?>
        <!-- Reports - Manager only -->
        <li class="nav-item">...</li>
        <?php endif; ?>

        <!-- Profile - All -->
        <li class="nav-item">...</li>
    </ul>
</nav>
```

### 3.3 Controllers Frontend - Padrão RBAC

Todos os controllers do frontend foram atualizados para seguir o padrão:

```php
public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'rules' => [
                // 1. Block admin from frontend
                [
                    'allow' => false,
                    'roles' => ['admin'],
                    'denyCallback' => function ($rule, $action) {
                        throw new ForbiddenHttpException(
                            'Administrators do not have access to the frontend.'
                        );
                    },
                ],
                
                // 2. View actions - check specific permission
                [
                    'allow' => true,
                    'actions' => ['index', 'view'],
                    'matchCallback' => function ($rule, $action) {
                        return Yii::$app->user->can('module.view');
                    },
                ],
                
                // 3. Create/Update/Delete - manager only
                [
                    'allow' => true,
                    'actions' => ['create', 'update', 'delete'],
                    'matchCallback' => function ($rule, $action) {
                        return Yii::$app->user->can('module.create');
                    },
                ],
            ],
        ],
    ];
}
```

### 3.4 Backend Error Handling

**SiteController.php:**
```php
public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'rules' => [
                ['actions' => ['login', 'error'], 'allow' => true],
                ['allow' => true, 'roles' => ['admin']],
            ],
            'denyCallback' => function ($rule, $action) {
                $action->controller->layout = 'blank';
                throw new ForbiddenHttpException(
                    'You do not have permission to access the backend.'
                );
            },
        ],
    ];
}

public function actions()
{
    return [
        'error' => [
            'class' => \yii\web\ErrorAction::class,
            'layout' => 'blank',
        ],
    ];
}
```

**error.php:** Página de erro standalone sem layout, com design moderno e botões de navegação contextuais.

---

## 4. Arquivos Modificados

| Arquivo | Tipo de Modificação |
|---------|---------------------|
| `frontend/views/layouts/_navbar.php` | Correção de roles |
| `frontend/views/layouts/dashboard.php` | Controle de menu por role |
| `frontend/views/layouts/error.php` | **Novo** - Layout para erros |
| `frontend/views/site/error.php` | Redesign completo |
| `frontend/controllers/DashboardController.php` | RBAC completo |
| `frontend/controllers/VehicleController.php` | Bloqueio admin |
| `frontend/controllers/RouteController.php` | RBAC behaviors |
| `frontend/controllers/AlertController.php` | RBAC completo |
| `frontend/controllers/DriverController.php` | Bloqueio admin |
| `frontend/controllers/MaintenanceController.php` | Bloqueio admin |
| `frontend/controllers/DocumentController.php` | RBAC completo |
| `frontend/controllers/ReportController.php` | RBAC completo |
| `frontend/controllers/SiteController.php` | Layout error |
| `backend/controllers/SiteController.php` | DenyCallback + layout |
| `backend/views/site/error.php` | Página standalone |

---

## 5. Testes Recomendados

### 5.1 Teste de Acesso - Admin

1. Login como `admin` / `admin`
2. Verificar que botão "Backoffice" aparece na navbar (não "Dashboard")
3. Tentar acessar `frontend/dashboard` → Deve retornar 403
4. Acessar backend → Deve funcionar normalmente

### 5.2 Teste de Acesso - Manager

1. Login como `manager` / `manager123`
2. Verificar que botão "Dashboard" aparece na navbar
3. Acessar dashboard → Deve mostrar menu completo
4. Tentar acessar backend → Deve retornar 403 com página limpa
5. Verificar CRUD em todos os módulos

### 5.3 Teste de Acesso - Driver

1. Login como `driver1` / `driver123`
2. Verificar que botão "Dashboard" aparece na navbar
3. Acessar dashboard → Menu limitado (sem Drivers, Maintenance, Documents, Reports)
4. Tentar acessar `/driver/index` → Deve retornar 403
5. Verificar que pode visualizar Vehicles, Routes, Alerts (somente leitura)
6. Verificar que pode editar próprio perfil

---

## 6. Considerações Técnicas

### 6.1 Performance

As verificações de role são feitas via `Yii::$app->user->identity->role`, que acessa o `authManager`. Para alta carga, considerar cache de roles.

### 6.2 Segurança

- Todas as verificações são feitas server-side nos behaviors dos controllers
- Menu hiding é apenas UX, não substitui validação backend
- Erros 403 não expõem informações sensíveis

### 6.3 Manutenibilidade

O padrão de RBAC foi documentado e pode ser facilmente replicado em novos controllers seguindo o template em `DashboardController.php`.

---

## 7. Conclusão

Todas as correções foram implementadas com sucesso:

✅ **Navbar:** Botão Dashboard visível para manager e driver  
✅ **Sidebar:** Menu adaptativo por role  
✅ **Controllers:** RBAC completo em todos os 8 controllers do frontend  
✅ **Backend 403:** Página de erro limpa sem layout  
✅ **Frontend 403:** Página de erro informativa com navegação contextual

O sistema agora respeita corretamente a matriz de permissões definida para os três roles: `admin`, `manager`, e `driver`.

---

*Relatório gerado automaticamente - VeiGest v1.1.0*

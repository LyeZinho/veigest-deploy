# Migrações VeiGest Database

Este diretório contém as migrações para criar a estrutura completa da base de dados do sistema VeiGest.

## Migrações Criadas

### Estrutura das Tabelas
1. **m251118_000001_create_companies_table.php** - Cria a tabela de empresas
2. **m251118_000002_create_rbac_tables.php** - Cria as tabelas RBAC do Yii2
3. **m251118_000003_create_users_table.php** - Cria a tabela de utilizadores (com perfil condutor integrado)
4. **m251118_000004_create_files_table.php** - Cria a tabela de ficheiros
5. **m251118_000005_create_vehicles_table.php** - Cria a tabela de veículos
6. **m251118_000006_create_maintenances_table.php** - Cria a tabela de manutenções
7. **m251118_000007_create_documents_table.php** - Cria a tabela de documentos
8. **m251118_000008_create_fuel_logs_table.php** - Cria a tabela de registos de combustível
9. **m251118_000009_create_alerts_table.php** - Cria a tabela de alertas
10. **m251118_000010_create_activity_logs_table.php** - Cria a tabela de logs de atividade

### Views e Dados Iniciais
11. **m251118_000011_create_views.php** - Cria as views úteis do sistema
12. **m251118_000012_insert_rbac_data.php** - Insere roles e permissões RBAC
13. **m251118_000013_assign_rbac_permissions.php** - Associa permissões aos roles

## Como Executar as Migrações

### 1. Configurar a Base de Dados
Primeiro, certifique-se de que a configuração da base de dados está correta no ficheiro `common/config/main.php`:

```php
'db' => [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=veigest',
    'username' => 'seu_usuario',
    'password' => 'sua_password',
    'charset' => 'utf8mb4',
],
```

### 2. Executar as Migrações
Navegue até o diretório raiz do projeto Yii2 e execute:

```bash
# Windows
php yii migrate

# Ou se estiver usando o arquivo .bat
yii migrate
```

### 3. Verificar Execução
As migrações irão:
- Criar todas as tabelas necessárias
- Configurar índices e chaves estrangeiras
- Inserir dados iniciais (empresa demo e utilizador admin)
- Configurar o sistema RBAC completo
- Criar views úteis para relatórios

## Dados Iniciais Criados

### Empresa Demo
- Nome: "VeiGest - Empresa Demo"
- NIF: 999999990
- Email: admin@veigest.com
- Plano: Enterprise

### Utilizador Admin
- Email: admin@veigest.com
- Password: admin (hash: $2a$12$/piK/Am/.6Wau7PpIzvO5ergX4AG17Xzk5RicS1Yom6YSsE5sSlgG)
- Role: admin

### Roles RBAC Criados
- **admin**: Administrador (todas as permissões exceto configurações críticas)
- **gestor**: Gestor de frota (gestão de veículos, utilizadores, relatórios)
- **gestor-manutencao**: Gestor de manutenção (foco em manutenções e documentos)
- **condutor-senior**: Condutor com permissões adicionais
- **condutor**: Condutor básico

## Rollback das Migrações
Para reverter as migrações (CUIDADO - isto apagará todos os dados):

```bash
php yii migrate/down all
```

## Notas Importantes
1. As migrações foram criadas seguindo as boas práticas do Yii2
2. Todas as tabelas usam `utf8mb4` para suporte completo a caracteres Unicode
3. As chaves estrangeiras estão configuradas com as ações apropriadas (CASCADE, SET NULL, RESTRICT)
4. O sistema RBAC está completamente configurado e pronto para uso
5. As views facilitam a criação de relatórios e dashboards

## Estrutura das Tabelas Principais

- **companies**: Gestão multi-tenant (cada empresa é independente)
- **users**: Utilizadores com perfil de condutor integrado
- **vehicles**: Veículos da frota
- **maintenances**: Historial de manutenções
- **documents**: Documentos ligados a veículos ou condutores
- **fuel_logs**: Registos de abastecimento
- **files**: Gestão de ficheiros uploadados
- **alerts**: Sistema de alertas automáticos
- **activity_logs**: Auditoria de ações do sistema
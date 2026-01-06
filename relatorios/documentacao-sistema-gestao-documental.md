# Documentação do Sistema de Gestão Documental - VeiGest

## Índice

1. [Visão Geral](#visão-geral)
2. [Arquitetura do Sistema](#arquitetura-do-sistema)
3. [Estrutura de Ficheiros](#estrutura-de-ficheiros)
4. [Models (Modelos)](#models-modelos)
5. [Controllers (Controladores)](#controllers-controladores)
6. [Views (Vistas)](#views-vistas)
7. [Fluxo de Upload](#fluxo-de-upload)
8. [Base de Dados](#base-de-dados)
9. [Guia de Alterações](#guia-de-alterações)
10. [Resolução de Problemas](#resolução-de-problemas)

---

## Visão Geral

O Sistema de Gestão Documental do VeiGest permite:

- **Upload de documentos** com associação a veículos e/ou motoristas
- **Categorização** por tipo (Seguro, Inspeção, Licença, etc.)
- **Controlo de validade** com alertas automáticos
- **Multi-tenancy** - cada empresa vê apenas os seus documentos
- **Download seguro** dos ficheiros

### Funcionalidades Principais

| Funcionalidade | Descrição |
|----------------|-----------|
| Listar documentos | Visualização com filtros e paginação |
| Upload | Suporte a PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, GIF |
| Editar | Atualizar metadados (tipo, validade, associações) |
| Eliminar | Remoção do documento e ficheiro físico |
| Download | Download direto do ficheiro |
| Estatísticas | Contadores de documentos válidos/expirados |

---

## Arquitetura do Sistema

```
Frontend (Yii2)
     │
     ├── DocumentController (CRUD)
     │        │
     │        ├── actionIndex()     → Lista documentos
     │        ├── actionCreate()    → Upload novo documento
     │        ├── actionUpdate()    → Editar documento
     │        ├── actionView()      → Ver detalhes
     │        ├── actionDelete()    → Eliminar documento
     │        └── actionDownload()  → Download do ficheiro
     │
     ├── Models (common/models/)
     │        ├── Document.php      → Metadados do documento
     │        ├── File.php          → Gestão de ficheiros
     │        ├── Vehicle.php       → Veículos
     │        └── User.php          → Utilizadores/Motoristas
     │
     └── Views (frontend/views/document/)
              ├── index.php         → Lista com GridView
              ├── create.php        → Formulário de upload
              ├── update.php        → Formulário de edição
              ├── view.php          → Detalhes do documento
              └── _form.php         → Formulário parcial
```

---

## Estrutura de Ficheiros

```
veigest/
├── common/
│   └── models/
│       ├── Company.php          # Modelo de empresas
│       ├── Document.php         # Modelo de documentos
│       ├── File.php             # Modelo de ficheiros
│       ├── Vehicle.php          # Modelo de veículos
│       └── User.php             # Modelo de utilizadores
│
├── frontend/
│   ├── controllers/
│   │   ├── DashboardController.php  # Redireciona para document/index
│   │   └── DocumentController.php   # Controller principal CRUD
│   │
│   ├── models/
│   │   ├── DocumentSearch.php       # Modelo de pesquisa/filtros
│   │   └── DocumentUploadForm.php   # Formulário de upload
│   │
│   ├── views/
│   │   └── document/
│   │       ├── index.php            # Lista de documentos
│   │       ├── create.php           # Página de upload
│   │       ├── update.php           # Página de edição
│   │       ├── view.php             # Detalhes do documento
│   │       └── _form.php            # Formulário reutilizável
│   │
│   └── web/
│       └── uploads/
│           └── documents/           # Ficheiros de upload
│               ├── .htaccess        # Segurança
│               ├── .gitkeep         # Manter estrutura no Git
│               └── {company_id}/    # Subpastas por empresa
```

---

## Models (Modelos)

### Document.php (`common/models/Document.php`)

Representa um documento no sistema.

#### Atributos

| Atributo | Tipo | Descrição |
|----------|------|-----------|
| `id` | int | ID único |
| `company_id` | int | Empresa proprietária |
| `file_id` | int | FK para tabela files |
| `vehicle_id` | int\|null | Veículo associado |
| `driver_id` | int\|null | Motorista associado |
| `type` | enum | registration, insurance, inspection, license, other |
| `expiry_date` | date\|null | Data de validade |
| `status` | enum | valid, expired |
| `notes` | text\|null | Observações |
| `created_at` | datetime | Data de criação |
| `updated_at` | datetime | Data de atualização |

#### Métodos Principais

```php
// Obter lista de tipos de documento
Document::getTypesList();

// Obter label do tipo
$document->getTypeLabel();

// Verificar se está próximo do vencimento (30 dias)
$document->isExpiringSoon($days = 30);

// Verificar se está expirado
$document->isExpired();

// Obter estatísticas por empresa
Document::getStatsByCompany($companyId);

// Obter dias até expirar
$document->getDaysUntilExpiry();

// Obter classe CSS do badge de status
$document->getStatusBadgeClass();
```

### File.php (`common/models/File.php`)

Representa um ficheiro físico no sistema.

#### Atributos

| Atributo | Tipo | Descrição |
|----------|------|-----------|
| `id` | int | ID único |
| `company_id` | int | Empresa proprietária |
| `original_name` | string | Nome original do ficheiro |
| `size` | bigint | Tamanho em bytes |
| `path` | string | Caminho relativo do ficheiro |
| `uploaded_by` | int | ID do utilizador que fez upload |
| `created_at` | datetime | Data de upload |

#### Métodos Principais

```php
// Processar upload
$file->upload($companyId, $userId);

// Obter URL de download
$file->getDownloadUrl();

// Obter caminho absoluto
$file->getAbsolutePath();

// Obter tamanho formatado (ex: "2.5 MB")
$file->getFormattedSize();

// Obter extensão
$file->getExtension();

// Obter ícone do tipo de ficheiro
$file->getFileIcon();
```

### DocumentSearch.php (`frontend/models/DocumentSearch.php`)

Modelo para pesquisa e filtros.

#### Atributos de Pesquisa

| Atributo | Tipo | Descrição |
|----------|------|-----------|
| `searchText` | string | Pesquisa textual |
| `statusFilter` | string | Filtro visual (valid, expiring, expired) |
| `type` | string | Tipo de documento |
| `vehicle_id` | int | Filtrar por veículo |
| `driver_id` | int | Filtrar por motorista |

### DocumentUploadForm.php (`frontend/models/DocumentUploadForm.php`)

Formulário para upload de novos documentos.

#### Atributos

| Atributo | Tipo | Descrição |
|----------|------|-----------|
| `uploadedFile` | UploadedFile | Ficheiro a enviar |
| `type` | string | Tipo de documento |
| `expiry_date` | date | Data de validade |
| `vehicle_id` | int\|null | Veículo associado |
| `driver_id` | int\|null | Motorista associado |
| `notes` | string\|null | Observações |

#### Método Principal

```php
// Processar upload e criar documento
$document = $form->upload();
```

---

## Controllers (Controladores)

### DocumentController.php

Localização: `frontend/controllers/DocumentController.php`

#### Actions (Ações)

| Action | URL | Método | Descrição |
|--------|-----|--------|-----------|
| `actionIndex` | `/document/index` | GET | Lista documentos |
| `actionView` | `/document/view?id=X` | GET | Ver documento |
| `actionCreate` | `/document/create` | GET/POST | Upload novo |
| `actionUpdate` | `/document/update?id=X` | GET/POST | Editar |
| `actionDelete` | `/document/delete?id=X` | POST | Eliminar |
| `actionDownload` | `/document/download?id=X` | GET | Download |

#### Segurança

- Apenas utilizadores autenticados (`'roles' => ['@']`)
- Multi-tenancy via `company_id` do utilizador logado
- Verificação de propriedade em todas as ações

---

## Views (Vistas)

### index.php - Lista de Documentos

Componentes:
- Cards de estatísticas (total, válidos, próximos vencimento, expirados)
- Formulário de filtros (pesquisa, tipo, status)
- GridView com colunas: Documento, Tipo, Veículo/Pessoa, Validade, Estado, Ações
- Paginação

### create.php - Upload de Documento

Componentes:
- Formulário de upload com enctype multipart
- Campos: ficheiro, tipo, validade, veículo, motorista, observações
- Painel lateral com informações (formatos aceites, tamanho máximo)

### update.php - Editar Documento

Componentes:
- Informação do ficheiro atual
- Formulário de edição (tipo, validade, associações, observações)
- Painel de estado atual
- Zona de perigo (eliminar)

### view.php - Detalhes do Documento

Componentes:
- DetailView com todos os atributos
- Preview de imagem (se aplicável)
- Informação do ficheiro
- Botões de ação (download, editar, eliminar)

### _form.php - Formulário Parcial

Formulário reutilizável para upload de documentos.

---

## Fluxo de Upload

```
1. Utilizador acede a /document/create
           │
           ▼
2. Preenche formulário e seleciona ficheiro
           │
           ▼
3. Submit do formulário (POST)
           │
           ▼
4. DocumentController::actionCreate()
           │
           ├── Cria DocumentUploadForm
           ├── Carrega dados do POST
           └── Obtém UploadedFile
           │
           ▼
5. DocumentUploadForm::upload()
           │
           ├── Validação (tipo, tamanho, extensão)
           ├── Criar diretório se necessário
           ├── Gerar nome único (hash + timestamp)
           ├── Salvar ficheiro físico
           ├── Iniciar transação DB
           │   ├── Criar registo em 'files'
           │   └── Criar registo em 'documents'
           └── Commit ou Rollback
           │
           ▼
6. Redirect para /document/index
   com mensagem de sucesso/erro
```

---

## Base de Dados

### Tabela `files`

```sql
CREATE TABLE files (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    size BIGINT NOT NULL,
    path VARCHAR(500) NOT NULL,
    uploaded_by INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT
);
```

### Tabela `documents`

```sql
CREATE TABLE documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    file_id INT NOT NULL,
    vehicle_id INT NULL,
    driver_id INT NULL,
    type ENUM('registration','insurance','inspection','license','other') NOT NULL,
    expiry_date DATE NULL,
    status ENUM('valid','expired') NOT NULL DEFAULT 'valid',
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (file_id) REFERENCES files(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## Guia de Alterações

### Adicionar Novo Tipo de Documento

1. **Editar `Document.php`**:

```php
// Adicionar constante
const TYPE_NEW_TYPE = 'newtype';

// Atualizar regra de validação
['type', 'in', 'range' => [
    self::TYPE_REGISTRATION, 
    self::TYPE_INSURANCE, 
    // ... outros tipos
    self::TYPE_NEW_TYPE, // Novo tipo
]],

// Atualizar getTypesList()
public static function getTypesList()
{
    return [
        // ... tipos existentes
        self::TYPE_NEW_TYPE => 'Novo Tipo',
    ];
}
```

2. **Executar migration** (se necessário alterar ENUM na BD):

```php
$this->alterColumn('documents', 'type', 
    "ENUM('registration','insurance','inspection','license','other','newtype') NOT NULL"
);
```

### Adicionar Novo Campo ao Documento

1. **Criar migration**:

```php
public function up()
{
    $this->addColumn('documents', 'new_field', $this->string(100));
}
```

2. **Atualizar `Document.php`**:
   - Adicionar regra de validação
   - Adicionar label em `attributeLabels()`

3. **Atualizar `DocumentUploadForm.php`**:
   - Adicionar propriedade pública
   - Adicionar regra de validação
   - Atualizar método `upload()`

4. **Atualizar views**:
   - `_form.php`: Adicionar campo do formulário
   - `view.php`: Adicionar ao DetailView

### Alterar Extensões Permitidas

Editar em dois locais:

1. **`File.php`** (linha ~55):
```php
[['uploadedFile'], 'file', 
    'extensions' => 'pdf, doc, docx, xls, xlsx, jpg, jpeg, png, gif, NEW_EXT',
    // ...
],
```

2. **`DocumentUploadForm.php`** (linha ~55):
```php
[['uploadedFile'], 'file', 
    'extensions' => 'pdf, doc, docx, xls, xlsx, jpg, jpeg, png, gif, NEW_EXT',
    // ...
],
```

### Alterar Tamanho Máximo de Upload

1. **Nos modelos** (`File.php` e `DocumentUploadForm.php`):
```php
'maxSize' => 20 * 1024 * 1024, // 20MB
```

2. **No php.ini**:
```ini
upload_max_filesize = 20M
post_max_size = 25M
```

### Adicionar Novo Filtro na Listagem

1. **Editar `DocumentSearch.php`**:
   - Adicionar propriedade pública
   - Adicionar regra safe
   - Atualizar método `search()`

2. **Editar `index.php`**:
   - Adicionar campo no formulário de filtros

### Personalizar Cores dos Badges

Editar em `Document.php`, método `getStatusBadgeClass()`:

```php
public function getStatusBadgeClass()
{
    if ($this->status === self::STATUS_EXPIRED) {
        return 'badge-danger';
    }
    
    if ($this->isExpiringSoon()) {
        return 'badge-warning';
    }
    
    return 'badge-success';
}
```

### Alterar Período de Alerta de Vencimento

Editar em `Document.php`, método `isExpiringSoon()`:

```php
public function isExpiringSoon($days = 30) // Alterar valor default
{
    // ...
}
```

E em `DocumentSearch.php`, método `search()`:

```php
// Alterar de 30 para o novo valor
->andWhere(['<=', 'expiry_date', 
    new \yii\db\Expression('DATE_ADD(CURDATE(), INTERVAL 60 DAY)')])
```

---

## Resolução de Problemas

### Erro: "Permission denied" no upload

**Causa**: O diretório de uploads não tem permissões de escrita.

**Solução**:
```bash
sudo chown -R www-data:www-data /path/to/veigest/frontend/web/uploads
sudo chmod -R 755 /path/to/veigest/frontend/web/uploads
```

### Erro: "Ficheiro muito grande"

**Causa**: Limite do PHP ou do modelo.

**Soluções**:
1. Verificar `php.ini`:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 12M
   ```
2. Verificar `maxSize` nos modelos

### Erro: "Extensão não permitida"

**Causa**: O tipo MIME não corresponde à extensão.

**Solução**: Definir `checkExtensionByMimeType => false` no validador de ficheiro.

### Documentos não aparecem na listagem

**Causas possíveis**:
1. O `company_id` do utilizador não corresponde aos documentos
2. O utilizador não está autenticado

**Verificação**:
```php
// Em DocumentController
Yii::debug('Company ID: ' . Yii::$app->user->identity->company_id);
```

### Erro ao eliminar documento

**Causa**: Constraint de chave estrangeira ou ficheiro bloqueado.

**Solução**: Verificar que o `beforeDelete()` do Document elimina primeiro o ficheiro.

---

## Contacto e Suporte

Para dúvidas ou sugestões sobre esta documentação, contactar a equipa de desenvolvimento.

**Última atualização**: Dezembro 2025  
**Versão**: 1.0

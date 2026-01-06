# 🔍 Técnicas de Debug

## Visão Geral

Este guia apresenta técnicas para debug do VeiGest em diferentes níveis.

---

## Debug no Yii2

### Debug Toolbar

A Debug Toolbar mostra informações detalhadas sobre cada request.

**Activar:**
```php
// config/main-local.php
if (!YII_ENV_TEST) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['127.0.0.1', '::1', '192.168.*'],
    ];
}
```

**Informações disponíveis:**
- Queries SQL executadas
- Tempo de execução
- Memória usada
- Logs
- Requests e responses

---

### Logging

```php
// Níveis de log
Yii::debug('Mensagem de debug', 'categoria');
Yii::info('Informação', 'categoria');
Yii::warning('Aviso', 'categoria');
Yii::error('Erro', 'categoria');

// Exemplo prático
public function actionCreate()
{
    Yii::debug('Iniciando criação de veículo', 'api');
    
    $model = new Vehicle();
    $data = Yii::$app->request->getBodyParams();
    
    Yii::debug('Dados recebidos: ' . json_encode($data), 'api');
    
    if (!$model->save()) {
        Yii::warning('Validação falhou: ' . json_encode($model->errors), 'api');
    }
}
```

**Configurar targets de log:**
```php
// config/main.php
'log' => [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        [
            'class' => 'yii\log\FileTarget',
            'levels' => ['error', 'warning'],
            'logFile' => '@runtime/logs/app.log',
        ],
        [
            'class' => 'yii\log\FileTarget',
            'levels' => ['info', 'trace'],
            'categories' => ['api'],
            'logFile' => '@runtime/logs/api.log',
        ],
        // Log para BD (opcional)
        [
            'class' => 'yii\log\DbTarget',
            'levels' => ['error', 'warning'],
            'logTable' => '{{%log}}',
        ],
    ],
],
```

**Ver logs:**
```bash
# Tempo real
tail -f runtime/logs/app.log

# Filtrar por categoria
grep "api" runtime/logs/app.log

# Últimos erros
tail -100 runtime/logs/app.log | grep "\[error\]"
```

---

### Profiling

```php
// Medir tempo de execução
Yii::beginProfile('operation_name');
// ... código a medir
Yii::endProfile('operation_name');

// Exemplo
public function actionReport()
{
    Yii::beginProfile('report_generation');
    
    Yii::beginProfile('fetch_vehicles');
    $vehicles = Vehicle::find()->all();
    Yii::endProfile('fetch_vehicles');
    
    Yii::beginProfile('fetch_maintenance');
    $maintenance = Maintenance::find()->all();
    Yii::endProfile('fetch_maintenance');
    
    Yii::beginProfile('process_data');
    $result = $this->processData($vehicles, $maintenance);
    Yii::endProfile('process_data');
    
    Yii::endProfile('report_generation');
    
    return $result;
}
```

---

## Debug de Queries SQL

### Ver SQL gerado

```php
// Ver query sem executar
$query = Vehicle::find()
    ->where(['company_id' => 1])
    ->andWhere(['status' => 'active']);

$sql = $query->createCommand()->getRawSql();
Yii::debug($sql);
// SELECT * FROM vehicle WHERE company_id = 1 AND status = 'active'
```

### Analisar queries com EXPLAIN

```php
$sql = $query->createCommand()->getRawSql();
$explain = Yii::$app->db->createCommand("EXPLAIN " . $sql)->queryAll();
Yii::debug($explain);
```

### Log de todas as queries

```php
// config/main-local.php
'db' => [
    'class' => 'yii\db\Connection',
    'enableLogging' => true,
    'enableProfiling' => true,
],
```

---

## Debug de API

### Adicionar debug info na resposta

```php
// BaseApiController
protected function formatResponse($data, $success = true, $message = null)
{
    $response = [
        'success' => $success,
        'data' => $data,
    ];
    
    if ($message) {
        $response['message'] = $message;
    }
    
    // Debug info apenas em development
    if (YII_DEBUG) {
        $response['_debug'] = [
            'time' => round(microtime(true) - YII_BEGIN_TIME, 3) . 's',
            'memory' => round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB',
            'queries' => Yii::$app->db->queryCount ?? 0,
        ];
    }
    
    return $response;
}
```

### Testar com verbose cURL

```bash
# Ver headers completos
curl -v -X GET "http://localhost/api/vehicles" \
    -H "Authorization: Bearer $TOKEN"

# Apenas response headers
curl -I "http://localhost/api/vehicles" \
    -H "Authorization: Bearer $TOKEN"

# Tempo de request
curl -w "\n\nTime: %{time_total}s\n" -X GET "http://localhost/api/vehicles" \
    -H "Authorization: Bearer $TOKEN"
```

---

## Debug no Browser

### Console JavaScript

```javascript
// Debug de requests
fetch('/api/vehicles', {
    headers: { 'Authorization': 'Bearer ' + token }
})
.then(response => {
    console.log('Status:', response.status);
    console.log('Headers:', response.headers);
    return response.json();
})
.then(data => {
    console.log('Data:', data);
    console.table(data.data); // Tabela formatada
})
.catch(error => {
    console.error('Erro:', error);
});
```

### Network Tab

1. Abrir DevTools (F12)
2. Tab "Network"
3. Filtrar por "XHR" ou "Fetch"
4. Ver:
   - Request Headers
   - Request Payload
   - Response Headers
   - Response Body
   - Timing

---

## Debug de Views

### Dump de variáveis

```php
<?php
// Ver todas as variáveis disponíveis
echo '<pre>';
var_dump(get_defined_vars());
echo '</pre>';

// Usar VarDumper do Yii
use yii\helpers\VarDumper;
echo VarDumper::dumpAsString($model, 10, true);

// Debug die
Yii::debug($data);
VarDumper::dump($data, 10, true);
die();
?>
```

### Debug com Xdebug

**Configurar php.ini:**
```ini
[xdebug]
xdebug.mode = debug
xdebug.start_with_request = yes
xdebug.client_host = localhost
xdebug.client_port = 9003
xdebug.idekey = VSCODE
```

**Configurar VS Code (launch.json):**
```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www/html": "${workspaceFolder}/veigest"
            }
        }
    ]
}
```

---

## Debug de Performance

### Identificar gargalos

```php
// Medir tempo manualmente
$start = microtime(true);

// ... código
$vehicles = Vehicle::find()->with(['maintenances', 'fuelLogs'])->all();

$end = microtime(true);
Yii::debug('Tempo: ' . ($end - $start) . 's', 'performance');
```

### Problema N+1

```php
// ❌ N+1 Problem (1 query + N queries)
$vehicles = Vehicle::find()->all();
foreach ($vehicles as $vehicle) {
    echo $vehicle->company->name;  // Query adicional por veículo
}

// ✅ Eager Loading (2 queries total)
$vehicles = Vehicle::find()->with('company')->all();
foreach ($vehicles as $vehicle) {
    echo $vehicle->company->name;  // Já carregado
}
```

### Analisar queries lentas

```sql
-- MySQL slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;
SET GLOBAL slow_query_log_file = '/var/log/mysql/slow.log';

-- Ver queries lentas
SELECT * FROM mysql.slow_log ORDER BY query_time DESC LIMIT 10;
```

---

## Debug de Docker

### Ver logs dos containers

```bash
# Todos os containers
docker-compose logs -f

# Container específico
docker-compose logs -f web
docker-compose logs -f db

# Últimas 100 linhas
docker-compose logs --tail=100 web
```

### Entrar no container

```bash
# Shell interativo
docker-compose exec web bash

# Verificar processos
docker-compose exec web ps aux

# Ver ficheiros de log
docker-compose exec web tail -f /var/log/nginx/error.log
```

### Verificar recursos

```bash
# Uso de recursos
docker stats

# Verificar estado
docker-compose ps

# Inspecionar container
docker inspect veigest_web
```

---

## Ferramentas Úteis

### Postman

1. Criar collection "VeiGest API"
2. Configurar variável `{{base_url}}`
3. Configurar variável `{{token}}` (do login)
4. Testar endpoints
5. Ver response time e tamanho

### PHPStorm Database Tools

1. Conectar à BD
2. Ver estrutura das tabelas
3. Executar queries
4. Ver plano de execução

### MySQL Workbench

1. Performance Dashboard
2. Query Analyzer
3. Schema Design

---

## Checklist de Debug

### Quando algo não funciona:

1. **Verificar logs**
   ```bash
   tail -f runtime/logs/app.log
   ```

2. **Verificar resposta HTTP**
   ```bash
   curl -v http://localhost/api/endpoint
   ```

3. **Verificar BD**
   ```sql
   SELECT * FROM tabela WHERE id = X;
   ```

4. **Verificar código**
   - Breakpoints com Xdebug
   - `VarDumper::dump()`
   - `Yii::debug()`

5. **Verificar configuração**
   - `config/main.php`
   - `config/params.php`
   - `.env`

6. **Limpar cache**
   ```bash
   php yii cache/flush-all
   ```

---

## Próximos Passos

- [Erros Comuns](erros-comuns.md)
- [Guia de Testes](../guias/testes.md)

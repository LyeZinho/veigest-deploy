# 📚 Documentação VeiGest

Documentação técnica completa do sistema de gestão de frotas VeiGest.

## 📁 Estrutura da Documentação

### 🏗️ [Arquitetura](arquitetura/)
- [Visão Geral do Sistema](arquitetura/visao-geral.md)
- [Estrutura do Projeto](arquitetura/estrutura-projeto.md)
- [Fluxo de Requisições](arquitetura/fluxo-requisicoes.md)

### 🔧 [Backend / API](backend/)
- [Controllers da API](backend/api-controllers.md)
- [Models da API](backend/api-models.md)
- [Autenticação](backend/autenticacao.md)
- [Endpoints Completos](backend/endpoints.md)

### 🎨 [Frontend](frontend/)
- [Controllers](frontend/controllers.md)
- [Views e Templates](frontend/views.md)
- [Assets e CSS](frontend/assets.md)
- [Layouts](frontend/layouts.md)
- [Sistema de Perfil](frontend/profile.md) ⭐ **Novo**

### 🗄️ [Base de Dados](database/)
- [Schema e Tabelas](database/schema.md)
- [Migrations](database/migrations.md)
- [Models ActiveRecord](database/models.md)

### 📖 [Guias Práticos](guias/)
- [Adicionar CRUD Completo](guias/adicionar-crud.md)
- [Adicionar Endpoint API](guias/adicionar-endpoint-api.md)
- [Escrever Testes](guias/testes.md)

### 🔧 [Troubleshooting](troubleshooting/)
- [Erros Comuns](troubleshooting/erros-comuns.md)
- [Técnicas de Debug](troubleshooting/debug.md)

---

## 🚀 Início Rápido

```bash
# 1. Clonar e entrar no projeto
cd website-VeiGest

# 2. Copiar configuração
cp .env.example .env

# 3. Levantar containers
docker-compose up -d --build

# 4. Executar migrations (dentro do container)
docker exec -it veigest_backend php yii migrate --interactive=0

# 5. Aceder ao sistema
# Frontend: http://localhost:8001
# Backend API: http://localhost:8002/api
```

## 📋 Credenciais de Teste

| Utilizador | Password | Papel |
|------------|----------|-------|
| admin | admin123 | Administrador |
| gestor | gestor123 | Gestor |
| driver1 | driver123 | Condutor |

---

**Versão:** 1.0  
**Última atualização:** Janeiro 2026

/**
 * VeiGest API - Testes de Usuários
 * Testa endpoints de gestão de usuários com multi-tenancy e RBAC
 */

const { apiRequest, formatTestResult } = require('../utils/http-client.js');

/**
 * Executa todos os testes de usuários
 * @param {string} token - Token de autenticação
 * @param {number} companyId - ID da empresa para multi-tenancy
 */
async function runUserTests(token, companyId) {
    console.log('\n👥 INICIANDO TESTES DE USUÁRIOS\n');
    console.log('=' .repeat(80));
    console.log(`Token: ${token.substring(0, 30)}...`);
    console.log(`Company ID: ${companyId}`);
    console.log('='.repeat(80));
    
    const results = {
        total: 0,
        success: 0,
        failed: 0,
        tests: [],
        createdUserId: null
    };

    // Teste 1: Listar todos os usuários
    console.log('\n📝 Teste 1: Listar todos os usuários');
    const listResult = await apiRequest('GET', '/users', {
        token: token
    });
    
    results.total++;
    if (listResult.success) {
        results.success++;
        console.log(formatTestResult('Listar Usuários - Sucesso', listResult));
        
        const users = listResult.response.body?.data || [];
        console.log(`\n📊 Total de usuários retornados: ${users.length}`);
        
        // Verificar se todos os usuários pertencem à empresa correta
        const wrongCompany = users.find(u => u.company_id !== companyId);
        if (wrongCompany) {
            console.log(`⚠️  AVISO: Usuário ${wrongCompany.id} pertence a empresa ${wrongCompany.company_id}, esperado ${companyId}`);
        }
        
        // Mostrar alguns detalhes
        if (users.length > 0) {
            console.log('\n📋 Primeiros usuários:');
            users.slice(0, 3).forEach(user => {
                console.log(`   - ${user.username} (${user.name || 'Sem nome'}) - Status: ${user.status}`);
            });
        }
        
        results.tests.push({
            name: 'Listar Usuários',
            status: 'SUCESSO',
            count: users.length
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Listar Usuários - FALHA', listResult));
        results.tests.push({
            name: 'Listar Usuários',
            status: 'FALHA',
            error: listResult.error
        });
    }

    // Teste 2: Criar novo usuário
    console.log('\n📝 Teste 2: Criar novo usuário');
    const newUser = {
        username: `test_user_${Date.now().toString().slice(-6)}`,
        email: `test${Date.now()}@veigest.com`,
        name: 'Usuário Teste API',
        phone: '+351987654321',
        password: 'test123',
        status: 'active'
    };
    
    const createResult = await apiRequest('POST', '/users', {
        token: token,
        body: newUser
    });
    
    results.total++;
    // Yii2 REST retorna o objeto diretamente, não wrapped em 'data'
    const userData = createResult.response.body?.data || createResult.response.body;
    if (createResult.success && userData?.id) {
        results.success++;
        results.createdUserId = userData.id;
        console.log(formatTestResult('Criar Usuário - Sucesso', createResult));
        console.log(`\n✅ Usuário criado com ID: ${results.createdUserId}`);
        
        if (userData.company_id === companyId) {
            console.log(`✅ Company ID correto: ${userData.company_id}`);
        } else {
            console.log(`⚠️  Company ID incorreto: ${userData.company_id}, esperado: ${companyId}`);
        }
        
        results.tests.push({
            name: 'Criar Usuário',
            status: 'SUCESSO',
            userId: results.createdUserId
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Criar Usuário - FALHA', createResult));
        results.tests.push({
            name: 'Criar Usuário',
            status: 'FALHA',
            error: createResult.error
        });
    }

    // Teste 3: Visualizar usuário específico
    if (results.createdUserId) {
        console.log('\n📝 Teste 3: Visualizar usuário específico');
        const viewResult = await apiRequest('GET', `/user/${results.createdUserId}`, {
            token: token
        });
        
        results.total++;
        if (viewResult.success) {
            results.success++;
            console.log(formatTestResult('Visualizar Usuário - Sucesso', viewResult));
            
            const user = viewResult.response.body?.data;
            if (user) {
                console.log(`\n📊 Detalhes do usuário:`);
                console.log(`   Username: ${user.username}`);
                console.log(`   Email: ${user.email}`);
                console.log(`   Nome: ${user.name}`);
                console.log(`   Tipo: ${user.tipo}`);
                console.log(`   Status: ${user.status}`);
            }
            
            results.tests.push({
                name: 'Visualizar Usuário',
                status: 'SUCESSO'
            });
        } else {
            results.failed++;
            console.log(formatTestResult('Visualizar Usuário - FALHA', viewResult));
            results.tests.push({
                name: 'Visualizar Usuário',
                status: 'FALHA',
                error: viewResult.error
            });
        }
    }

    // Teste 4: Atualizar usuário
    if (results.createdUserId) {
        console.log('\n📝 Teste 4: Atualizar usuário');
        const updateData = {
            name: 'Usuário Atualizado',
            telefone: '+351999888777',
            tipo: 'gestor'
        };
        
        const updateResult = await apiRequest('PUT', `/user/${results.createdUserId}`, {
            token: token,
            body: updateData
        });
        
        results.total++;
        if (updateResult.success) {
            results.success++;
            console.log(formatTestResult('Atualizar Usuário - Sucesso', updateResult));
            
            const updatedUser = updateResult.response.body?.data;
            if (updatedUser) {
                console.log(`\n📊 Dados atualizados:`);
                console.log(`   Nome: ${updatedUser.name}`);
                console.log(`   Telefone: ${updatedUser.telefone}`);
                console.log(`   Tipo: ${updatedUser.tipo}`);
            }
            
            results.tests.push({
                name: 'Atualizar Usuário',
                status: 'SUCESSO'
            });
        } else {
            results.failed++;
            console.log(formatTestResult('Atualizar Usuário - FALHA', updateResult));
            results.tests.push({
                name: 'Atualizar Usuário',
                status: 'FALHA',
                error: updateResult.error
            });
        }
    }

    // Teste 5: Listar condutores (filtro por tipo)
    console.log('\n📝 Teste 5: Listar apenas condutores');
    const driversResult = await apiRequest('GET', '/users/drivers', {
        token: token
    });
    
    results.total++;
    if (driversResult.success) {
        results.success++;
        console.log(formatTestResult('Listar Condutores - Sucesso', driversResult));
        
        const drivers = driversResult.response.body?.data || [];
        console.log(`\n📊 Total de condutores: ${drivers.length}`);
        
        results.tests.push({
            name: 'Listar Condutores',
            status: 'SUCESSO',
            count: drivers.length
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Listar Condutores - FALHA', driversResult));
        results.tests.push({
            name: 'Listar Condutores',
            status: 'FALHA',
            error: driversResult.error
        });
    }

    // Teste 6: Buscar usuário por username
    console.log('\n📝 Teste 6: Buscar usuário por username');
    const searchResult = await apiRequest('GET', '/user?username=admin', {
        token: token
    });
    
    results.total++;
    if (searchResult.success) {
        results.success++;
        console.log(formatTestResult('Buscar por Username - Sucesso', searchResult));
        
        const users = searchResult.response.body?.data || [];
        console.log(`\n📊 Usuários encontrados: ${users.length}`);
        
        results.tests.push({
            name: 'Buscar por Username',
            status: 'SUCESSO',
            found: users.length
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Buscar por Username - FALHA', searchResult));
        results.tests.push({
            name: 'Buscar por Username',
            status: 'FALHA',
            error: searchResult.error
        });
    }

    // Teste 7: Tentar criar usuário com dados inválidos
    console.log('\n📝 Teste 7: Validação - criar usuário sem username');
    const invalidUser = {
        email: 'invalid@test.com',
        name: 'Usuário Inválido'
        // Falta username obrigatório
    };
    
    const invalidCreateResult = await apiRequest('POST', '/user', {
        token: token,
        body: invalidUser
    });
    
    results.total++;
    // Esperamos erro (400 ou 422)
    if (!invalidCreateResult.success && [400, 422].includes(invalidCreateResult.response.status)) {
        results.success++;
        console.log(formatTestResult('Validação de Dados - Comportamento Esperado', invalidCreateResult));
        results.tests.push({
            name: 'Validação de Dados',
            status: 'SUCESSO (erro esperado)'
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Validação de Dados - Comportamento Inesperado', invalidCreateResult));
        results.tests.push({
            name: 'Validação de Dados',
            status: 'FALHA (deveria retornar erro)'
        });
    }

    // Teste 8: Deletar usuário
    if (results.createdUserId) {
        console.log('\n📝 Teste 8: Deletar usuário');
        const deleteResult = await apiRequest('DELETE', `/user/${results.createdUserId}`, {
            token: token
        });
        
        results.total++;
        if (deleteResult.success || deleteResult.response.status === 204) {
            results.success++;
            console.log(formatTestResult('Deletar Usuário - Sucesso', deleteResult));
            results.tests.push({
                name: 'Deletar Usuário',
                status: 'SUCESSO'
            });

            // Verificar se foi deletado
            console.log('\n📝 Teste 8.1: Verificar se usuário foi deletado');
            const verifyDeleteResult = await apiRequest('GET', `/user/${results.createdUserId}`, {
                token: token
            });
            
            results.total++;
            if (verifyDeleteResult.response.status === 404) {
                results.success++;
                console.log(formatTestResult('Verificar Deleção - Comportamento Esperado (404)', verifyDeleteResult));
                results.tests.push({
                    name: 'Verificar Deleção',
                    status: 'SUCESSO (404 esperado)'
                });
            } else {
                results.failed++;
                console.log(formatTestResult('Verificar Deleção - Usuário ainda existe', verifyDeleteResult));
                results.tests.push({
                    name: 'Verificar Deleção',
                    status: 'FALHA (usuário ainda existe)'
                });
            }
        } else {
            results.failed++;
            console.log(formatTestResult('Deletar Usuário - FALHA', deleteResult));
            results.tests.push({
                name: 'Deletar Usuário',
                status: 'FALHA',
                error: deleteResult.error
            });
        }
    }

    return results;
}

// Executar testes se chamado diretamente
if (require.main === module) {
    const { apiRequest: loginRequest } = require('../utils/http-client.js');
    
    loginRequest('POST', '/auth/login', {
        body: {
            username: 'admin',
            password: 'admin'
        }
    })
    .then(loginResult => {
        if (!loginResult.success || !loginResult.response.body?.data?.token) {
            throw new Error('Falha no login: ' + loginResult.error);
        }
        
        const token = loginResult.response.body.data.token;
        const companyId = loginResult.response.body.data.user?.company_id || 1;
        
        return runUserTests(token, companyId);
    })
    .then(results => {
        console.log('\n\n' + '='.repeat(80));
        console.log('📊 RESUMO DOS TESTES DE USUÁRIOS');
        console.log('='.repeat(80));
        console.log(`Total de testes:  ${results.total}`);
        console.log(`✅ Sucessos:      ${results.success}`);
        console.log(`❌ Falhas:        ${results.failed}`);
        console.log(`📈 Taxa de êxito: ${((results.success / results.total) * 100).toFixed(1)}%`);
        console.log('='.repeat(80));
        
        console.log('\n📋 DETALHES DOS TESTES:\n');
        results.tests.forEach((test, index) => {
            let icon = '❓';
            if (test.status.includes('SUCESSO')) icon = '✅';
            else if (test.status.includes('FALHA')) icon = '❌';
            else if (test.status.includes('INFO')) icon = 'ℹ️';
            
            console.log(`${icon} ${index + 1}. ${test.name}: ${test.status}`);
            if (test.error) console.log(`   Erro: ${test.error}`);
            if (test.userId) console.log(`   ID: ${test.userId}`);
            if (test.count !== undefined) console.log(`   Quantidade: ${test.count}`);
            if (test.found !== undefined) console.log(`   Encontrados: ${test.found}`);
        });
        
        console.log('\n');
        process.exit(results.failed > 0 ? 1 : 0);
    })
    .catch(error => {
        console.error('❌ Erro ao executar testes:', error.message);
        process.exit(1);
    });
}

module.exports = { runUserTests };

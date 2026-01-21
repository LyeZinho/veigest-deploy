/**
 * VeiGest API - Testes de Autenticação
 * Testa endpoints de login, logout, me e refresh
 */

const { apiRequest, formatTestResult, decodeToken } = require('../utils/http-client.js');

// Credenciais de teste
const TEST_CREDENTIALS = {
    admin: {
        username: 'admin',
        password: 'admin'
    },
    manager: {
        username: 'gestor',
        password: 'gestor123'
    },
    driver: {
        username: 'driver1',
        password: 'driver123'
    }
};

/**
 * Executa todos os testes de autenticação
 */
async function runAuthTests() {
    console.log('\n🔐 INICIANDO TESTES DE AUTENTICAÇÃO\n');
    console.log('=' .repeat(80));
    
    const results = {
        total: 0,
        success: 0,
        failed: 0,
        tests: []
    };

    // Teste 1: Login com credenciais válidas (admin)
    console.log('\n📝 Teste 1: Login com credenciais válidas (admin)');
    const loginResult = await apiRequest('POST', '/auth/login', {
        body: TEST_CREDENTIALS.admin
    });
    
    results.total++;
    if (loginResult.success && loginResult.response.body?.data?.access_token) {
        results.success++;
        console.log(formatTestResult('Login Admin - Sucesso', loginResult));
        
        // Extrair e decodificar token
        const token = loginResult.response.body.data.access_token;
        const tokenData = decodeToken(token);
        
        if (tokenData) {
            console.log('\n🔍 TOKEN DECODIFICADO:');
            console.log(JSON.stringify(tokenData, null, 2));
        }
        
        results.tests.push({
            name: 'Login Admin',
            status: 'SUCESSO',
            token: token
        });

        // Teste 2: Validar token - endpoint /auth/me
        console.log('\n📝 Teste 2: Validar token com endpoint /auth/me');
        const meResult = await apiRequest('GET', '/auth/me', {
            token: token
        });
        
        results.total++;
        if (meResult.success) {
            results.success++;
            console.log(formatTestResult('Validação de Token (/auth/me)', meResult));
            results.tests.push({
                name: 'Validação Token',
                status: 'SUCESSO'
            });
        } else {
            results.failed++;
            console.log(formatTestResult('Validação de Token (/auth/me) - FALHA', meResult));
            results.tests.push({
                name: 'Validação Token',
                status: 'FALHA',
                error: meResult.error
            });
        }

        // Teste 3: Refresh token
        console.log('\n📝 Teste 3: Refresh token');
        const refreshResult = await apiRequest('POST', '/auth/refresh', {
            token: token
        });
        
        results.total++;
        if (refreshResult.success && refreshResult.response.body?.data?.access_token) {
            results.success++;
            console.log(formatTestResult('Refresh Token - Sucesso', refreshResult));
            
            const newToken = refreshResult.response.body.data.access_token;
            results.tests.push({
                name: 'Refresh Token',
                status: 'SUCESSO',
                newToken: newToken
            });
        } else {
            results.failed++;
            console.log(formatTestResult('Refresh Token - FALHA', refreshResult));
            results.tests.push({
                name: 'Refresh Token',
                status: 'FALHA',
                error: refreshResult.error
            });
        }

        // Teste 4: Logout
        console.log('\n📝 Teste 4: Logout');
        const logoutResult = await apiRequest('POST', '/auth/logout', {
            token: token
        });
        
        results.total++;
        if (logoutResult.success) {
            results.success++;
            console.log(formatTestResult('Logout - Sucesso', logoutResult));
            results.tests.push({
                name: 'Logout',
                status: 'SUCESSO'
            });
        } else {
            results.failed++;
            console.log(formatTestResult('Logout - FALHA', logoutResult));
            results.tests.push({
                name: 'Logout',
                status: 'FALHA',
                error: logoutResult.error
            });
        }

    } else {
        results.failed++;
        console.log(formatTestResult('Login Admin - FALHA', loginResult));
        results.tests.push({
            name: 'Login Admin',
            status: 'FALHA',
            error: loginResult.error
        });
    }

    // Teste 5: Login com credenciais inválidas
    console.log('\n📝 Teste 5: Login com credenciais inválidas');
    const invalidLoginResult = await apiRequest('POST', '/auth/login', {
        body: {
            username: 'invalid_user',
            password: 'wrong_password'
        }
    });
    
    results.total++;
    // Neste caso, esperamos FALHA (401)
    if (!invalidLoginResult.success && invalidLoginResult.response.status === 401) {
        results.success++;
        console.log(formatTestResult('Login Inválido - Comportamento Esperado', invalidLoginResult));
        results.tests.push({
            name: 'Login Inválido',
            status: 'SUCESSO (401 esperado)',
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Login Inválido - Comportamento Inesperado', invalidLoginResult));
        results.tests.push({
            name: 'Login Inválido',
            status: 'FALHA (deveria retornar 401)',
            error: 'Status inesperado'
        });
    }

    // Teste 6: Login como Manager
    console.log('\n📝 Teste 6: Login como Manager (multi-tenancy)');
    const managerLoginResult = await apiRequest('POST', '/auth/login', {
        body: TEST_CREDENTIALS.manager
    });
    
    results.total++;
    if (managerLoginResult.success && managerLoginResult.response.body?.data?.access_token) {
        results.success++;
        console.log(formatTestResult('Login Manager - Sucesso', managerLoginResult));
        
        const managerToken = managerLoginResult.response.body.data.token;
        const managerTokenData = decodeToken(managerToken);
        
        if (managerTokenData) {
            console.log('\n🔍 TOKEN MANAGER DECODIFICADO:');
            console.log(JSON.stringify(managerTokenData, null, 2));
            console.log(`\n📊 Company Code: ${managerTokenData.company_code}`);
            console.log(`📊 Roles: ${managerTokenData.roles?.join(', ') || 'N/A'}`);
            console.log(`📊 Permissions: ${managerTokenData.permissions?.length || 0} permissões`);
        }
        
        results.tests.push({
            name: 'Login Manager',
            status: 'SUCESSO',
            company_code: managerTokenData?.company_code
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Login Manager - FALHA', managerLoginResult));
        results.tests.push({
            name: 'Login Manager',
            status: 'FALHA',
            error: managerLoginResult.error
        });
    }

    // Teste 7: Acesso sem token
    console.log('\n📝 Teste 7: Acesso a endpoint protegido sem token');
    const noTokenResult = await apiRequest('GET', '/auth/me');
    
    results.total++;
    // Esperamos FALHA (401)
    if (!noTokenResult.success && noTokenResult.response.status === 401) {
        results.success++;
        console.log(formatTestResult('Acesso Sem Token - Comportamento Esperado (401)', noTokenResult));
        results.tests.push({
            name: 'Acesso Sem Token',
            status: 'SUCESSO (401 esperado)'
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Acesso Sem Token - Comportamento Inesperado', noTokenResult));
        results.tests.push({
            name: 'Acesso Sem Token',
            status: 'FALHA (deveria retornar 401)'
        });
    }

    return results;
}

// Executar testes se chamado diretamente
if (require.main === module) {
    runAuthTests()
        .then(results => {
            console.log('\n\n' + '='.repeat(80));
            console.log('📊 RESUMO DOS TESTES DE AUTENTICAÇÃO');
            console.log('='.repeat(80));
            console.log(`Total de testes:  ${results.total}`);
            console.log(`✅ Sucessos:      ${results.success}`);
            console.log(`❌ Falhas:        ${results.failed}`);
            console.log(`📈 Taxa de êxito: ${((results.success / results.total) * 100).toFixed(1)}%`);
            console.log('='.repeat(80));
            
            console.log('\n📋 DETALHES DOS TESTES:\n');
            results.tests.forEach((test, index) => {
                const icon = test.status.includes('SUCESSO') ? '✅' : '❌';
                console.log(`${icon} ${index + 1}. ${test.name}: ${test.status}`);
                if (test.error) {
                    console.log(`   Erro: ${test.error}`);
                }
                if (test.company_code) {
                    console.log(`   Company Code: ${test.company_code}`);
                }
            });
            
            console.log('\n');
            process.exit(results.failed > 0 ? 1 : 0);
        })
        .catch(error => {
            console.error('❌ Erro ao executar testes:', error);
            process.exit(1);
        });
}

module.exports = { runAuthTests };

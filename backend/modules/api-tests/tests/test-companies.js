/**
 * VeiGest API - Testes de Empresas
 * Testa endpoints de gestão de empresas com controle de acesso RBAC
 */

const { apiRequest, formatTestResult } = require('../utils/http-client.js');

/**
 * Executa todos os testes de empresas
 * @param {string} token - Token de autenticação
 * @param {number} companyId - ID da empresa para multi-tenancy
 */
async function runCompanyTests(token, companyId) {
    console.log('\n🏢 INICIANDO TESTES DE EMPRESAS\n');
    console.log('=' .repeat(80));
    console.log(`Token: ${token.substring(0, 30)}...`);
    console.log(`Company ID: ${companyId}`);
    console.log('='.repeat(80));
    
    const results = {
        total: 0,
        success: 0,
        failed: 0,
        tests: [],
        createdCompanyId: null
    };

    // Teste 1: Visualizar empresa atual
    console.log('\n📝 Teste 1: Visualizar empresa atual');
    const viewResult = await apiRequest('GET', `/company/${companyId}`, {
        token: token
    });
    
    results.total++;
    if (viewResult.success) {
        results.success++;
        console.log(formatTestResult('Visualizar Empresa - Sucesso', viewResult));
        const company = viewResult.response.body?.data || viewResult.response.body;
        if (company) {
            console.log(`\n📊 Detalhes da empresa:`);
            console.log(`   Nome: ${company.nome || company.name}`);
            console.log(`   Email: ${company.email}`);
            console.log(`   Status: ${company.status}`);
            console.log(`   Tax ID: ${company.tax_id || company.nif}`);
        }
        results.tests.push({
            name: 'Visualizar Empresa',
            status: 'SUCESSO'
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Visualizar Empresa - FALHA', viewResult));
        results.tests.push({
            name: 'Visualizar Empresa',
            status: 'FALHA',
            error: viewResult.error
        });
    }

    // Teste 2: Listar veículos da empresa
    console.log('\n📝 Teste 2: Listar veículos da empresa');
    const vehiclesResult = await apiRequest('GET', `/companies/${companyId}/vehicles`, {
        token: token
    });
    
    results.total++;
    if (vehiclesResult.success) {
        results.success++;
        console.log(formatTestResult('Listar Veículos da Empresa - Sucesso', vehiclesResult));
        const vehicles = vehiclesResult.response.body?.data || [];
        console.log(`\n📊 Total de veículos: ${vehicles.length}`);
        
        if (vehicles.length > 0) {
            console.log('\n📋 Primeiros veículos:');
            vehicles.slice(0, 3).forEach(vehicle => {
                console.log(`   - ${vehicle.license_plate} (${vehicle.brand} ${vehicle.model}) - Status: ${vehicle.status}`);
            });
        }
        
        results.tests.push({
            name: 'Listar Veículos da Empresa',
            status: 'SUCESSO',
            count: vehicles.length
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Listar Veículos da Empresa - FALHA', vehiclesResult));
        results.tests.push({
            name: 'Listar Veículos da Empresa',
            status: 'FALHA',
            error: vehiclesResult.error
        });
    }

    // Teste 3: Listar usuários da empresa
    console.log('\n📝 Teste 3: Listar usuários da empresa');
    const usersResult = await apiRequest('GET', `/companies/${companyId}/users`, {
        token: token
    });
    
    results.total++;
    if (usersResult.success) {
        results.success++;
        console.log(formatTestResult('Listar Usuários da Empresa - Sucesso', usersResult));
        const users = usersResult.response.body?.data || [];
        console.log(`\n📊 Total de usuários: ${users.length}`);
        
        if (users.length > 0) {
            console.log('\n📋 Primeiros usuários:');
            users.slice(0, 3).forEach(user => {
                console.log(`   - ${user.username} (${user.name || 'Sem nome'}) - Tipo: ${user.tipo} - Status: ${user.status}`);
            });
        }
        
        results.tests.push({
            name: 'Listar Usuários da Empresa',
            status: 'SUCESSO',
            count: users.length
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Listar Usuários da Empresa - FALHA', usersResult));
        results.tests.push({
            name: 'Listar Usuários da Empresa',
            status: 'FALHA',
            error: usersResult.error
        });
    }

    // Teste 4: Estatísticas da empresa
    console.log('\n📝 Teste 4: Obter estatísticas da empresa');
    const statsResult = await apiRequest('GET', `/companies/${companyId}/stats`, {
        token: token
    });
    
    results.total++;
    if (statsResult.success) {
        results.success++;
        console.log(formatTestResult('Estatísticas da Empresa - Sucesso', statsResult));
        const stats = statsResult.response.body?.data || statsResult.response.body;
        if (stats) {
            console.log(`\n📊 Estatísticas:`);
            console.log(`   Veículos totais: ${stats.vehicles_count || 0}`);
            console.log(`   Veículos ativos: ${stats.active_vehicles || 0}`);
            console.log(`   Usuários totais: ${stats.users_count || 0}`);
            console.log(`   Condutores: ${stats.drivers_count || 0}`);
            
            if (stats.maintenance_stats) {
                console.log(`   Manutenções totais: ${stats.maintenance_stats.total_maintenances || 0}`);
                console.log(`   Manutenções pendentes: ${stats.maintenance_stats.pending_maintenances || 0}`);
            }
            
            if (stats.fuel_stats) {
                console.log(`   Registros de combustível: ${stats.fuel_stats.total_fuel_logs || 0}`);
                console.log(`   Custo total combustível: R$ ${stats.fuel_stats.total_fuel_cost || 0}`);
            }
        }
        results.tests.push({
            name: 'Estatísticas da Empresa',
            status: 'SUCESSO'
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Estatísticas da Empresa - FALHA', statsResult));
        results.tests.push({
            name: 'Estatísticas da Empresa',
            status: 'FALHA',
            error: statsResult.error
        });
    }

    // Teste 5: Atualizar dados da empresa
    console.log('\n📝 Teste 5: Atualizar dados da empresa');
    const updateData = {
        telefone: '+351987654321',
        morada: 'Rua Teste API, 123',
        cidade: 'Lisboa'
    };
    
    const updateResult = await apiRequest('PUT', `/company/${companyId}`, {
        token: token,
        body: updateData
    });
    
    results.total++;
    if (updateResult.success) {
        results.success++;
        console.log(formatTestResult('Atualizar Empresa - Sucesso', updateResult));
        const updatedCompany = updateResult.response.body?.data || updateResult.response.body;
        if (updatedCompany) {
            console.log(`\n✅ Empresa atualizada:`);
            console.log(`   Telefone: ${updatedCompany.telefone || updatedCompany.phone}`);
            console.log(`   Morada: ${updatedCompany.morada || updatedCompany.address}`);
            console.log(`   Cidade: ${updatedCompany.cidade || updatedCompany.city}`);
        }
        results.tests.push({
            name: 'Atualizar Empresa',
            status: 'SUCESSO'
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Atualizar Empresa - FALHA', updateResult));
        results.tests.push({
            name: 'Atualizar Empresa',
            status: 'FALHA',
            error: updateResult.error
        });
    }

    // Teste 6: Tentar listar todas as empresas (apenas admin)
    console.log('\n📝 Teste 6: Listar todas as empresas (teste de permissão)');
    const listAllResult = await apiRequest('GET', '/company', {
        token: token
    });
    
    results.total++;
    // Este teste pode falhar se o usuário não for admin, o que é esperado
    if (listAllResult.success) {
        results.success++;
        console.log(formatTestResult('Listar Todas Empresas - Sucesso (usuário admin)', listAllResult));
        const companies = listAllResult.response.body?.data || [];
        console.log(`\n📊 Total de empresas no sistema: ${companies.length}`);
        results.tests.push({
            name: 'Listar Todas Empresas',
            status: 'SUCESSO (Admin)',
            count: companies.length
        });
    } else {
        // Se falhar por falta de permissão, não é um erro real
        if (listAllResult.response?.status === 403) {
            results.success++;
            console.log('✅ Acesso negado conforme esperado (usuário não-admin)');
            results.tests.push({
                name: 'Listar Todas Empresas',
                status: 'SUCESSO (Permissão negada como esperado)',
            });
        } else {
            results.failed++;
            console.log(formatTestResult('Listar Todas Empresas - FALHA', listAllResult));
            results.tests.push({
                name: 'Listar Todas Empresas',
                status: 'FALHA',
                error: listAllResult.error
            });
        }
    }

    // Teste 7: Filtrar veículos ativos da empresa
    console.log('\n📝 Teste 7: Filtrar veículos ativos da empresa');
    const activeVehiclesResult = await apiRequest('GET', `/companies/${companyId}/vehicles?status=active`, {
        token: token
    });
    
    results.total++;
    if (activeVehiclesResult.success) {
        results.success++;
        console.log(formatTestResult('Filtrar Veículos Ativos - Sucesso', activeVehiclesResult));
        const activeVehicles = activeVehiclesResult.response.body?.data || [];
        console.log(`\n📊 Veículos ativos: ${activeVehicles.length}`);
        results.tests.push({
            name: 'Filtrar Veículos Ativos',
            status: 'SUCESSO',
            count: activeVehicles.length
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Filtrar Veículos Ativos - FALHA', activeVehiclesResult));
        results.tests.push({
            name: 'Filtrar Veículos Ativos',
            status: 'FALHA',
            error: activeVehiclesResult.error
        });
    }

    // Teste 8: Filtrar condutores da empresa
    console.log('\n📝 Teste 8: Filtrar condutores da empresa');
    const driversResult = await apiRequest('GET', `/companies/${companyId}/users?tipo=condutor`, {
        token: token
    });
    
    results.total++;
    if (driversResult.success) {
        results.success++;
        console.log(formatTestResult('Filtrar Condutores - Sucesso', driversResult));
        const drivers = driversResult.response.body?.data || [];
        console.log(`\n📊 Condutores: ${drivers.length}`);
        
        if (drivers.length > 0) {
            console.log('\n🚗 Condutores encontrados:');
            drivers.slice(0, 3).forEach(driver => {
                console.log(`   - ${driver.username} (${driver.name || 'Sem nome'}) - Licença: ${driver.license_number || 'N/A'}`);
            });
        }
        
        results.tests.push({
            name: 'Filtrar Condutores',
            status: 'SUCESSO',
            count: drivers.length
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Filtrar Condutores - FALHA', driversResult));
        results.tests.push({
            name: 'Filtrar Condutores',
            status: 'FALHA',
            error: driversResult.error
        });
    }

    console.log('\n\n' + '='.repeat(80));
    console.log('📊 RESUMO DOS TESTES DE EMPRESAS');
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
        if (test.count !== undefined) console.log(`   Quantidade: ${test.count}`);
    });

    console.log('\n');
    return results;
}

// Executar testes se chamado diretamente
if (require.main === module) {
    // Primeiro, fazer login para obter token
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
        
        return runCompanyTests(token, companyId);
    })
    .then(results => {
        console.log('\n\n' + '='.repeat(80));
        console.log('📊 RESULTADO FINAL DOS TESTES DE EMPRESAS');
        console.log('='.repeat(80));
        console.log(`Total de testes:  ${results.total}`);
        console.log(`✅ Sucessos:      ${results.success}`);
        console.log(`❌ Falhas:        ${results.failed}`);
        console.log(`📈 Taxa de êxito: ${((results.success / results.total) * 100).toFixed(1)}%`);
        console.log('='.repeat(80) + '\n');
        
        process.exit(results.failed > 0 ? 1 : 0);
    })
    .catch(error => {
        console.error('\n❌ Erro nos testes de empresas:', error.message);
        process.exit(1);
    });
}

module.exports = { runCompanyTests };

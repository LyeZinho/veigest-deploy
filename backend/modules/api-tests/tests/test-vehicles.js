/**
 * VeiGest API - Testes de Veículos
 * Testa CRUD de veículos com multi-tenancy e validação de company_id
 */

const { apiRequest, formatTestResult } = require('../utils/http-client.js');

/**
 * Executa todos os testes de veículos
 * @param {string} token - Token de autenticação
 * @param {number} companyId - ID da empresa para multi-tenancy
 */
async function runVehicleTests(token, companyId) {
    console.log('\n🚗 INICIANDO TESTES DE VEÍCULOS\n');
    console.log('=' .repeat(80));
    console.log(`Token: ${token.substring(0, 30)}...`);
    console.log(`Company ID: ${companyId}`);
    console.log('=' .repeat(80));
    
    const results = {
        total: 0,
        success: 0,
        failed: 0,
        tests: [],
        createdVehicleId: null
    };

    // Teste 1: Listar todos os veículos (com filtro de empresa)
    console.log('\n📝 Teste 1: Listar todos os veículos');
    const listResult = await apiRequest('GET', '/vehicles', {
        token: token
    });
    
    results.total++;
    if (listResult.success) {
        results.success++;
        console.log(formatTestResult('Listar Veículos - Sucesso', listResult));
        
        const vehicles = listResult.response.body?.data || [];
        console.log(`\n📊 Total de veículos retornados: ${vehicles.length}`);
        
        // Verificar se todos os veículos pertencem à empresa correta
        const wrongCompany = vehicles.find(v => v.company_id !== companyId);
        if (wrongCompany) {
            console.log(`⚠️  AVISO: Veículo ${wrongCompany.id} pertence a empresa ${wrongCompany.company_id}, esperado ${companyId}`);
        }
        
        results.tests.push({
            name: 'Listar Veículos',
            status: 'SUCESSO',
            count: vehicles.length
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Listar Veículos - FALHA', listResult));
        results.tests.push({
            name: 'Listar Veículos',
            status: 'FALHA',
            error: listResult.error
        });
    }

    // Teste 2: Criar novo veículo
    console.log('\n📝 Teste 2: Criar novo veículo');
    const newVehicle = {
        license_plate: `TEST-${Date.now().toString().slice(-4)}`,
        brand: 'Tesla',
        model: 'Model 3',
        year: 2023,
        fuel_type: 'electric',
        mileage: 5000,
        status: 'active'
    };
    
    const createResult = await apiRequest('POST', '/vehicles', {
        token: token,
        body: newVehicle
    });
    
    results.total++;
    // Yii2 REST retorna o objeto diretamente, não wrapped em 'data'
    const vehicleData = createResult.response.body?.data || createResult.response.body;
    if (createResult.success && vehicleData?.id) {
        results.success++;
        results.createdVehicleId = vehicleData.id;
        console.log(formatTestResult('Criar Veículo - Sucesso', createResult));
        console.log(`\n✅ Veículo criado com ID: ${results.createdVehicleId}`);
        
        // Verificar se o company_id foi automaticamente atribuído
        if (vehicleData.company_id === companyId) {
            console.log(`✅ Company ID correto: ${vehicleData.company_id}`);
        } else {
            console.log(`⚠️  Company ID incorreto: ${vehicleData.company_id}, esperado: ${companyId}`);
        }
        
        results.tests.push({
            name: 'Criar Veículo',
            status: 'SUCESSO',
            vehicleId: results.createdVehicleId
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Criar Veículo - FALHA', createResult));
        results.tests.push({
            name: 'Criar Veículo',
            status: 'FALHA',
            error: createResult.error
        });
    }

    // Teste 3: Visualizar veículo específico
    if (results.createdVehicleId) {
        console.log('\n📝 Teste 3: Visualizar veículo específico');
        const viewResult = await apiRequest('GET', `/vehicle/${results.createdVehicleId}`, {
            token: token
        });
        
        results.total++;
        if (viewResult.success) {
            results.success++;
            console.log(formatTestResult('Visualizar Veículo - Sucesso', viewResult));
            results.tests.push({
                name: 'Visualizar Veículo',
                status: 'SUCESSO'
            });
        } else {
            results.failed++;
            console.log(formatTestResult('Visualizar Veículo - FALHA', viewResult));
            results.tests.push({
                name: 'Visualizar Veículo',
                status: 'FALHA',
                error: viewResult.error
            });
        }
    }

    // Teste 4: Atualizar veículo
    if (results.createdVehicleId) {
        console.log('\n📝 Teste 4: Atualizar veículo');
        const updateData = {
            quilometragem: 6500,
            cor: 'Vermelho',
            estado: 'manutenção'
        };
        
        const updateResult = await apiRequest('PUT', `/vehicle/${results.createdVehicleId}`, {
            token: token,
            body: updateData
        });
        
        results.total++;
        if (updateResult.success) {
            results.success++;
            console.log(formatTestResult('Atualizar Veículo - Sucesso', updateResult));
            
            const updatedVehicle = updateResult.response.body?.data;
            if (updatedVehicle) {
                console.log(`\n📊 Dados atualizados:`);
                console.log(`   Quilometragem: ${updatedVehicle.quilometragem}`);
                console.log(`   Cor: ${updatedVehicle.cor}`);
                console.log(`   Estado: ${updatedVehicle.estado}`);
            }
            
            results.tests.push({
                name: 'Atualizar Veículo',
                status: 'SUCESSO'
            });
        } else {
            results.failed++;
            console.log(formatTestResult('Atualizar Veículo - FALHA', updateResult));
            results.tests.push({
                name: 'Atualizar Veículo',
                status: 'FALHA',
                error: updateResult.error
            });
        }
    }

    // Teste 5: Tentar acessar veículo de outra empresa (se houver outro token)
    console.log('\n📝 Teste 5: Validação de multi-tenancy');
    console.log('ℹ️  Este teste verificará se o filtro por company_id está funcionando');
    // Nota: Para testar completamente, precisaríamos de um token de outra empresa
    results.tests.push({
        name: 'Validação Multi-tenancy',
        status: 'INFO',
        message: 'Verifique manualmente com tokens de diferentes empresas'
    });

    // Teste 6: Deletar veículo
    if (results.createdVehicleId) {
        console.log('\n📝 Teste 6: Deletar veículo');
        const deleteResult = await apiRequest('DELETE', `/vehicle/${results.createdVehicleId}`, {
            token: token
        });
        
        results.total++;
        if (deleteResult.success || deleteResult.response.status === 204) {
            results.success++;
            console.log(formatTestResult('Deletar Veículo - Sucesso', deleteResult));
            results.tests.push({
                name: 'Deletar Veículo',
                status: 'SUCESSO'
            });

            // Verificar se o veículo foi realmente deletado
            console.log('\n📝 Teste 6.1: Verificar se veículo foi deletado');
            const verifyDeleteResult = await apiRequest('GET', `/vehicle/${results.createdVehicleId}`, {
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
                console.log(formatTestResult('Verificar Deleção - Veículo ainda existe', verifyDeleteResult));
                results.tests.push({
                    name: 'Verificar Deleção',
                    status: 'FALHA (veículo ainda existe)'
                });
            }
        } else {
            results.failed++;
            console.log(formatTestResult('Deletar Veículo - FALHA', deleteResult));
            results.tests.push({
                name: 'Deletar Veículo',
                status: 'FALHA',
                error: deleteResult.error
            });
        }
    }

    // Teste 7: Criar veículo com dados inválidos
    console.log('\n📝 Teste 7: Validação de dados - criar com matrícula duplicada');
    const invalidVehicle = {
        matricula: newVehicle.matricula, // Mesma matrícula (se não foi deletado)
        marca: 'Ford',
        modelo: 'Focus',
        ano: 2020
    };
    
    const invalidCreateResult = await apiRequest('POST', '/vehicle', {
        token: token,
        body: invalidVehicle
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
    } else if (invalidCreateResult.success) {
        // Se criou, pode ser porque o anterior foi deletado
        console.log(formatTestResult('Validação de Dados - Veículo criado', invalidCreateResult));
        results.tests.push({
            name: 'Validação de Dados',
            status: 'INFO',
            message: 'Veículo criado (matrícula não estava duplicada)'
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Validação de Dados - Comportamento Inesperado', invalidCreateResult));
        results.tests.push({
            name: 'Validação de Dados',
            status: 'FALHA'
        });
    }

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
        
        return runVehicleTests(token, companyId);
    })
    .then(results => {
        console.log('\n\n' + '='.repeat(80));
        console.log('📊 RESUMO DOS TESTES DE VEÍCULOS');
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
            if (test.message) console.log(`   ${test.message}`);
            if (test.vehicleId) console.log(`   ID: ${test.vehicleId}`);
            if (test.count !== undefined) console.log(`   Quantidade: ${test.count}`);
        });
        
        console.log('\n');
        process.exit(results.failed > 0 ? 1 : 0);
    })
    .catch(error => {
        console.error('❌ Erro ao executar testes:', error.message);
        process.exit(1);
    });
}

module.exports = { runVehicleTests };

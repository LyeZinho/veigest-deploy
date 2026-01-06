/**
 * VeiGest API - Executor de Todos os Testes
 * Executa todos os testes da API e gera relatório consolidado
 */

const { runAuthTests } = require('./tests/test-auth.js');
const { runVehicleTests } = require('./tests/test-vehicles.js');
const { runUserTests } = require('./tests/test-users.js');
const { runCompanyTests } = require('./tests/test-companies.js');
const { runMaintenanceTests } = require('./tests/test-maintenance.js');
const { runFuelLogTests } = require('./tests/test-fuel-logs.js');
const { apiRequest } = require('./utils/http-client.js');

/**
 * Executa todos os testes da API VeiGest
 */
async function runAllTests() {
    const startTime = Date.now();
    
    console.log('\n');
    console.log('╔' + '═'.repeat(78) + '╗');
    console.log('║' + ' '.repeat(20) + 'VeiGest API - Suite de Testes' + ' '.repeat(29) + '║');
    console.log('║' + ' '.repeat(25) + 'Versão 1.0 - 2025' + ' '.repeat(36) + '║');
    console.log('╚' + '═'.repeat(78) + '╝');
    console.log('\n');

    const allResults = {
        total: 0,
        success: 0,
        failed: 0,
        suites: []
    };

    let globalToken = null;
    let globalCompanyId = null;

    try {
        // ========================================
        // 1. TESTES DE AUTENTICAÇÃO
        // ========================================
        console.log('\n📦 SUITE 1/6: AUTENTICAÇÃO');
        console.log('─'.repeat(80));
        
        const authResults = await runAuthTests();
        allResults.suites.push({
            name: 'Autenticação',
            ...authResults
        });
        allResults.total += authResults.total;
        allResults.success += authResults.success;
        allResults.failed += authResults.failed;

        // Obter token para os próximos testes
        console.log('\n🔑 Obtendo token para testes subsequentes...');
        const loginResult = await apiRequest('POST', '/auth/login', {
            body: {
                username: 'apiadmin',
                password: 'password'
            }
        });

        if (loginResult.success && loginResult.response.body?.data?.access_token) {
            globalToken = loginResult.response.body.data.access_token;
            globalCompanyId = loginResult.response.body.data.user?.company_id || 1;
            console.log(`✅ Token obtido com sucesso (Company ID: ${globalCompanyId})`);
        } else {
            throw new Error('Falha ao obter token de autenticação para testes subsequentes');
        }

        // ========================================
        // 2. TESTES DE EMPRESAS
        // ========================================
        console.log('\n\n📦 SUITE 2/6: EMPRESAS');
        console.log('─'.repeat(80));
        
        const companyResults = await runCompanyTests(globalToken, globalCompanyId);
        allResults.suites.push({
            name: 'Empresas',
            ...companyResults
        });
        allResults.total += companyResults.total;
        allResults.success += companyResults.success;
        allResults.failed += companyResults.failed;

        // ========================================
        // 3. TESTES DE VEÍCULOS
        // ========================================
        console.log('\n\n📦 SUITE 3/6: VEÍCULOS');
        console.log('─'.repeat(80));
        
        const vehicleResults = await runVehicleTests(globalToken, globalCompanyId);
        allResults.suites.push({
            name: 'Veículos',
            ...vehicleResults
        });
        allResults.total += vehicleResults.total;
        allResults.success += vehicleResults.success;
        allResults.failed += vehicleResults.failed;

        // ========================================
        // 4. TESTES DE USUÁRIOS
        // ========================================
        console.log('\n\n📦 SUITE 4/6: USUÁRIOS');
        console.log('─'.repeat(80));
        
        const userResults = await runUserTests(globalToken, globalCompanyId);
        allResults.suites.push({
            name: 'Usuários',
            ...userResults
        });
        allResults.total += userResults.total;
        allResults.success += userResults.success;
        allResults.failed += userResults.failed;

        // ========================================
        // 5. TESTES DE MANUTENÇÕES
        // ========================================
        console.log('\n\n📦 SUITE 5/6: MANUTENÇÕES');
        console.log('─'.repeat(80));
        
        const maintenanceResults = await runMaintenanceTests(globalToken, globalCompanyId);
        allResults.suites.push({
            name: 'Manutenções',
            ...maintenanceResults
        });
        allResults.total += maintenanceResults.total;
        allResults.success += maintenanceResults.success;
        allResults.failed += maintenanceResults.failed;

        // ========================================
        // 6. TESTES DE ABASTECIMENTOS
        // ========================================
        console.log('\n\n📦 SUITE 6/6: ABASTECIMENTOS');
        console.log('─'.repeat(80));
        
        const fuelLogResults = await runFuelLogTests(globalToken, globalCompanyId);
        allResults.suites.push({
            name: 'Abastecimentos',
            ...fuelLogResults
        });
        allResults.total += fuelLogResults.total;
        allResults.success += fuelLogResults.success;
        allResults.failed += fuelLogResults.failed;

    } catch (error) {
        console.error('\n❌ ERRO CRÍTICO ao executar testes:', error.message);
        console.error(error.stack);
        process.exit(1);
    }

    // ========================================
    // RELATÓRIO FINAL
    // ========================================
    const endTime = Date.now();
    const duration = ((endTime - startTime) / 1000).toFixed(2);

    console.log('\n\n');
    console.log('╔' + '═'.repeat(78) + '╗');
    console.log('║' + ' '.repeat(25) + 'RELATÓRIO FINAL' + ' '.repeat(38) + '║');
    console.log('╚' + '═'.repeat(78) + '╝');
    
    console.log('\n📊 ESTATÍSTICAS GLOBAIS:');
    console.log('─'.repeat(80));
    console.log(`⏱️  Tempo total de execução: ${duration}s`);
    console.log(`📋 Total de testes executados: ${allResults.total}`);
    console.log(`✅ Testes bem-sucedidos: ${allResults.success} (${((allResults.success / allResults.total) * 100).toFixed(1)}%)`);
    console.log(`❌ Testes falhados: ${allResults.failed} (${((allResults.failed / allResults.total) * 100).toFixed(1)}%)`);
    console.log('─'.repeat(80));

    console.log('\n📦 RESUMO POR SUITE:');
    console.log('─'.repeat(80));
    
    allResults.suites.forEach((suite, index) => {
        const successRate = ((suite.success / suite.total) * 100).toFixed(1);
        const icon = suite.failed === 0 ? '✅' : '⚠️';
        
        console.log(`\n${icon} ${index + 1}. ${suite.name.toUpperCase()}`);
        console.log(`   Total: ${suite.total} | Sucesso: ${suite.success} | Falhas: ${suite.failed} | Taxa: ${successRate}%`);
        
        if (suite.tests && suite.tests.length > 0) {
            console.log('   Testes:');
            suite.tests.forEach((test, testIndex) => {
                let testIcon = '❓';
                if (test.status.includes('SUCESSO')) testIcon = '✅';
                else if (test.status.includes('FALHA')) testIcon = '❌';
                else if (test.status.includes('INFO')) testIcon = 'ℹ️';
                
                console.log(`   ${testIcon} ${testIndex + 1}. ${test.name}: ${test.status}`);
            });
        }
    });

    console.log('\n' + '─'.repeat(80));
    
    // Status final
    if (allResults.failed === 0) {
        console.log('\n✅ TODOS OS TESTES PASSARAM COM SUCESSO!');
        console.log('🎉 A API VeiGest está funcionando corretamente.\n');
    } else {
        console.log(`\n⚠️  ${allResults.failed} TESTE(S) FALHARAM`);
        console.log('🔍 Revise os logs acima para detalhes dos erros.\n');
    }

    // Recomendações
    console.log('💡 PRÓXIMOS PASSOS:');
    console.log('─'.repeat(80));
    console.log('1. Verifique se o servidor está rodando em http://localhost:8002');
    console.log('2. Confirme que o banco de dados está populado com dados de teste');
    console.log('3. Execute testes individuais para depuração:');
    console.log('   - node api-tests/tests/test-auth.js');
    console.log('   - node api-tests/tests/test-vehicles.js');
    console.log('   - node api-tests/tests/test-users.js');
    console.log('4. Consulte a documentação em api-tests/README.md\n');

    console.log('═'.repeat(80));
    console.log(`Relatório gerado em: ${new Date().toLocaleString('pt-PT')}`);
    console.log('═'.repeat(80) + '\n');

    // Exit code
    process.exit(allResults.failed > 0 ? 1 : 0);
}

// Executar testes
if (require.main === module) {
    console.log('\n🚀 Iniciando suite de testes da API VeiGest...\n');
    
    runAllTests().catch(error => {
        console.error('\n❌ Erro fatal na execução dos testes:', error);
        process.exit(1);
    });
}

module.exports = { runAllTests };

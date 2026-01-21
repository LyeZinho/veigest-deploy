/**
 * VeiGest API - Testes de Manutenções
 * Testa endpoints de gestão de manutenções com multi-tenancy
 */

const { apiRequest, formatTestResult } = require('../utils/http-client.js');

/**
 * Executa todos os testes de manutenções
 * @param {string} token - Token de autenticação
 * @param {number} companyId - ID da empresa para multi-tenancy
 */
async function runMaintenanceTests(token, companyId) {
    console.log('\n🔧 INICIANDO TESTES DE MANUTENÇÕES\n');
    console.log('=' .repeat(80));
    console.log(`Token: ${token.substring(0, 30)}...`);
    console.log(`Company ID: ${companyId}`);
    console.log('='.repeat(80));
    
    const results = {
        total: 0,
        success: 0,
        failed: 0,
        tests: [],
        createdMaintenanceId: null,
        testVehicleId: null
    };

    // Primeiro, obter um veículo da empresa para usar nos testes
    console.log('\n🔍 Obtendo veículo da empresa para testes...');
    const vehiclesResult = await apiRequest('GET', '/vehicles?per-page=1', {
        token: token
    });

    if (vehiclesResult.success && vehiclesResult.response.body?.data?.length > 0) {
        results.testVehicleId = vehiclesResult.response.body.data[0].id;
        console.log(`✅ Usando veículo ID ${results.testVehicleId} para testes`);
    } else {
        console.log('⚠️ Nenhum veículo encontrado, alguns testes podem falhar');
    }

    // Teste 1: Listar todas as manutenções
    console.log('\n📝 Teste 1: Listar todas as manutenções');
    const listResult = await apiRequest('GET', '/maintenance', {
        token: token
    });
    
    results.total++;
    if (listResult.success) {
        results.success++;
        console.log(formatTestResult('Listar Manutenções - Sucesso', listResult));
        const maintenances = listResult.response.body?.data || [];
        console.log(`\n📊 Total de manutenções: ${maintenances.length}`);
        
        if (maintenances.length > 0) {
            console.log('\n📋 Primeiras manutenções:');
            maintenances.slice(0, 3).forEach(maintenance => {
                console.log(`   - ${maintenance.tipo} (${maintenance.descricao}) - Estado: ${maintenance.estado} - Custo: R$ ${maintenance.custo}`);
            });
        }
        
        results.tests.push({
            name: 'Listar Manutenções',
            status: 'SUCESSO',
            count: maintenances.length
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Listar Manutenções - FALHA', listResult));
        results.tests.push({
            name: 'Listar Manutenções',
            status: 'FALHA',
            error: listResult.error
        });
    }

    // Teste 2: Criar nova manutenção (apenas se tiver veículo)
    if (results.testVehicleId) {
        console.log('\n📝 Teste 2: Criar nova manutenção');
        const newMaintenance = {
            vehicle_id: results.testVehicleId,
            tipo: 'preventiva',
            descricao: 'Manutenção de teste API - Troca de óleo',
            custo: 150.50,
            data_manutencao: '2024-12-25',
            quilometragem: 45000,
            fornecedor: 'Oficina Teste API',
            estado: 'agendada',
            observacoes: 'Criado via teste automatizado'
        };
        
        const createResult = await apiRequest('POST', '/maintenance', {
            token: token,
            body: newMaintenance
        });
        
        results.total++;
        const maintenanceData = createResult.response.body?.data || createResult.response.body;
        if (createResult.success && maintenanceData?.id) {
            results.success++;
            results.createdMaintenanceId = maintenanceData.id;
            console.log(formatTestResult('Criar Manutenção - Sucesso', createResult));
            console.log(`\n✅ Manutenção criada com ID: ${results.createdMaintenanceId}`);
            
            results.tests.push({
                name: 'Criar Manutenção',
                status: 'SUCESSO',
                maintenanceId: results.createdMaintenanceId
            });
        } else {
            results.failed++;
            console.log(formatTestResult('Criar Manutenção - FALHA', createResult));
            results.tests.push({
                name: 'Criar Manutenção',
                status: 'FALHA',
                error: createResult.error
            });
        }
    } else {
        console.log('\n⚠️ Teste 2: Criar manutenção - PULADO (sem veículo)');
        results.tests.push({
            name: 'Criar Manutenção',
            status: 'PULADO - Sem veículo disponível'
        });
    }

    // Teste 3: Visualizar manutenção específica
    if (results.createdMaintenanceId) {
        console.log('\n📝 Teste 3: Visualizar manutenção específica');
        const viewResult = await apiRequest('GET', `/maintenance/${results.createdMaintenanceId}`, {
            token: token
        });
        
        results.total++;
        if (viewResult.success) {
            results.success++;
            console.log(formatTestResult('Visualizar Manutenção - Sucesso', viewResult));
            const maintenance = viewResult.response.body?.data || viewResult.response.body;
            if (maintenance) {
                console.log(`\n📊 Detalhes da manutenção:`);
                console.log(`   ID: ${maintenance.id}`);
                console.log(`   Tipo: ${maintenance.tipo}`);
                console.log(`   Descrição: ${maintenance.descricao}`);
                console.log(`   Custo: R$ ${maintenance.custo}`);
                console.log(`   Estado: ${maintenance.estado}`);
                console.log(`   Data: ${maintenance.data_manutencao}`);
                console.log(`   Fornecedor: ${maintenance.fornecedor}`);
            }
            results.tests.push({
                name: 'Visualizar Manutenção',
                status: 'SUCESSO'
            });
        } else {
            results.failed++;
            console.log(formatTestResult('Visualizar Manutenção - FALHA', viewResult));
            results.tests.push({
                name: 'Visualizar Manutenção',
                status: 'FALHA',
                error: viewResult.error
            });
        }
    }

    // Teste 4: Atualizar manutenção
    if (results.createdMaintenanceId) {
        console.log('\n📝 Teste 4: Atualizar manutenção');
        const updateData = {
            custo: 175.00,
            estado: 'em_andamento',
            observacoes: 'Manutenção atualizada via teste API - Em andamento'
        };
        
        const updateResult = await apiRequest('PUT', `/maintenance/${results.createdMaintenanceId}`, {
            token: token,
            body: updateData
        });
        
        results.total++;
        if (updateResult.success) {
            results.success++;
            console.log(formatTestResult('Atualizar Manutenção - Sucesso', updateResult));
            const updatedMaintenance = updateResult.response.body?.data || updateResult.response.body;
            if (updatedMaintenance) {
                console.log(`\n✅ Manutenção atualizada:`);
                console.log(`   Custo: R$ ${updatedMaintenance.custo}`);
                console.log(`   Estado: ${updatedMaintenance.estado}`);
                console.log(`   Observações: ${updatedMaintenance.observacoes}`);
            }
            results.tests.push({
                name: 'Atualizar Manutenção',
                status: 'SUCESSO'
            });
        } else {
            results.failed++;
            console.log(formatTestResult('Atualizar Manutenção - FALHA', updateResult));
            results.tests.push({
                name: 'Atualizar Manutenção',
                status: 'FALHA',
                error: updateResult.error
            });
        }
    }

    // Teste 5: Listar manutenções por veículo
    if (results.testVehicleId) {
        console.log('\n📝 Teste 5: Listar manutenções por veículo');
        const vehicleMaintenancesResult = await apiRequest('GET', `/maintenance/by-vehicle/${results.testVehicleId}`, {
            token: token
        });
        
        results.total++;
        if (vehicleMaintenancesResult.success) {
            results.success++;
            console.log(formatTestResult('Manutenções por Veículo - Sucesso', vehicleMaintenancesResult));
            const vehicleMaintenances = vehicleMaintenancesResult.response.body?.data || [];
            console.log(`\n📊 Manutenções do veículo ${results.testVehicleId}: ${vehicleMaintenances.length}`);
            results.tests.push({
                name: 'Manutenções por Veículo',
                status: 'SUCESSO',
                count: vehicleMaintenances.length
            });
        } else {
            results.failed++;
            console.log(formatTestResult('Manutenções por Veículo - FALHA', vehicleMaintenancesResult));
            results.tests.push({
                name: 'Manutenções por Veículo',
                status: 'FALHA',
                error: vehicleMaintenancesResult.error
            });
        }
    }

    // Teste 6: Listar manutenções por estado
    console.log('\n📝 Teste 6: Listar manutenções agendadas');
    const scheduledMaintenancesResult = await apiRequest('GET', '/maintenance/by-status/agendada', {
        token: token
    });
    
    results.total++;
    if (scheduledMaintenancesResult.success) {
        results.success++;
        console.log(formatTestResult('Manutenções Agendadas - Sucesso', scheduledMaintenancesResult));
        const scheduledMaintenances = scheduledMaintenancesResult.response.body?.data || [];
        console.log(`\n📊 Manutenções agendadas: ${scheduledMaintenances.length}`);
        results.tests.push({
            name: 'Manutenções Agendadas',
            status: 'SUCESSO',
            count: scheduledMaintenances.length
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Manutenções Agendadas - FALHA', scheduledMaintenancesResult));
        results.tests.push({
            name: 'Manutenções Agendadas',
            status: 'FALHA',
            error: scheduledMaintenancesResult.error
        });
    }

    // Teste 7: Agendar manutenção
    if (results.createdMaintenanceId) {
        console.log('\n📝 Teste 7: Agendar manutenção');
        const scheduleData = {
            scheduled_date: '2024-12-30',
            priority: 'alta',
            assigned_technician: 'João Silva - Técnico Teste'
        };
        
        const scheduleResult = await apiRequest('POST', `/maintenance/${results.createdMaintenanceId}/schedule`, {
            token: token,
            body: scheduleData
        });
        
        results.total++;
        if (scheduleResult.success) {
            results.success++;
            console.log(formatTestResult('Agendar Manutenção - Sucesso', scheduleResult));
            const scheduledMaintenance = scheduleResult.response.body?.data || scheduleResult.response.body;
            if (scheduledMaintenance) {
                console.log(`\n✅ Manutenção agendada:`);
                console.log(`   Data agendada: ${scheduledMaintenance.data_manutencao}`);
                console.log(`   Estado: ${scheduledMaintenance.estado}`);
                console.log(`   Técnico: ${scheduledMaintenance.fornecedor}`);
            }
            results.tests.push({
                name: 'Agendar Manutenção',
                status: 'SUCESSO'
            });
        } else {
            results.failed++;
            console.log(formatTestResult('Agendar Manutenção - FALHA', scheduleResult));
            results.tests.push({
                name: 'Agendar Manutenção',
                status: 'FALHA',
                error: scheduleResult.error
            });
        }
    }

    // Teste 8: Estatísticas de manutenções
    console.log('\n📝 Teste 8: Obter estatísticas de manutenções');
    const statsResult = await apiRequest('GET', '/maintenance/stats', {
        token: token
    });
    
    results.total++;
    if (statsResult.success) {
        results.success++;
        console.log(formatTestResult('Estatísticas de Manutenções - Sucesso', statsResult));
        const stats = statsResult.response.body?.data || statsResult.response.body;
        if (stats) {
            console.log(`\n📊 Estatísticas:`);
            console.log(`   Manutenções totais: ${stats.total_maintenances || 0}`);
            console.log(`   Manutenções pendentes: ${stats.pending_maintenances || 0}`);
            console.log(`   Manutenções concluídas: ${stats.completed_maintenances || 0}`);
            console.log(`   Custo total: R$ ${stats.total_cost || 0}`);
            console.log(`   Custo médio: R$ ${stats.average_cost || 0}`);
            
            if (stats.maintenances_by_type) {
                console.log('\n🔧 Por tipo:');
                stats.maintenances_by_type.forEach(type => {
                    console.log(`   - ${type.tipo}: ${type.count} manutenções (R$ ${type.total_cost})`);
                });
            }
        }
        results.tests.push({
            name: 'Estatísticas de Manutenções',
            status: 'SUCESSO'
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Estatísticas de Manutenções - FALHA', statsResult));
        results.tests.push({
            name: 'Estatísticas de Manutenções',
            status: 'FALHA',
            error: statsResult.error
        });
    }

    // Teste 9: Relatório mensal
    console.log('\n📝 Teste 9: Relatório mensal de manutenções');
    const monthlyReportResult = await apiRequest('GET', '/maintenance/reports/monthly?year=2024&month=12', {
        token: token
    });
    
    results.total++;
    if (monthlyReportResult.success) {
        results.success++;
        console.log(formatTestResult('Relatório Mensal - Sucesso', monthlyReportResult));
        const report = monthlyReportResult.response.body?.data || monthlyReportResult.response.body;
        if (report && report.summary) {
            console.log(`\n📊 Relatório dezembro/2024:`);
            console.log(`   Manutenções: ${report.summary.total_maintenances || 0}`);
            console.log(`   Custo total: R$ ${report.summary.total_cost || 0}`);
            
            if (report.summary.by_type) {
                console.log('\n🔧 Por tipo:');
                Object.entries(report.summary.by_type).forEach(([type, count]) => {
                    console.log(`   - ${type}: ${count}`);
                });
            }
        }
        results.tests.push({
            name: 'Relatório Mensal',
            status: 'SUCESSO'
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Relatório Mensal - FALHA', monthlyReportResult));
        results.tests.push({
            name: 'Relatório Mensal',
            status: 'FALHA',
            error: monthlyReportResult.error
        });
    }

    // Teste 10: Relatório de custos
    console.log('\n📝 Teste 10: Relatório de custos de manutenções');
    const costsReportResult = await apiRequest('GET', '/maintenance/reports/costs?start_date=2024-01-01&end_date=2024-12-31', {
        token: token
    });
    
    results.total++;
    if (costsReportResult.success) {
        results.success++;
        console.log(formatTestResult('Relatório de Custos - Sucesso', costsReportResult));
        const report = costsReportResult.response.body?.data || costsReportResult.response.body;
        if (report && report.costs) {
            console.log(`\n💰 Relatório de custos 2024:`);
            console.log(`   Custo total: R$ ${report.costs.total_cost || 0}`);
            console.log(`   Custo médio: R$ ${report.costs.average_cost || 0}`);
            console.log(`   Total de manutenções: ${report.total_maintenances || 0}`);
            
            if (report.costs.by_vehicle && report.costs.by_vehicle.length > 0) {
                console.log('\n🚗 Por veículo (primeiros 3):');
                report.costs.by_vehicle.slice(0, 3).forEach(vehicle => {
                    console.log(`   - ${vehicle.vehicle?.license_plate || 'N/A'}: R$ ${vehicle.total_cost} (${vehicle.maintenance_count} manutenções)`);
                });
            }
        }
        results.tests.push({
            name: 'Relatório de Custos',
            status: 'SUCESSO'
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Relatório de Custos - FALHA', costsReportResult));
        results.tests.push({
            name: 'Relatório de Custos',
            status: 'FALHA',
            error: costsReportResult.error
        });
    }

    // Teste 11: Filtros de busca
    console.log('\n📝 Teste 11: Testar filtros de busca');
    const searchResult = await apiRequest('GET', '/maintenance?tipo=preventiva&search=óleo', {
        token: token
    });
    
    results.total++;
    if (searchResult.success) {
        results.success++;
        console.log(formatTestResult('Filtros de Busca - Sucesso', searchResult));
        const filtered = searchResult.response.body?.data || [];
        console.log(`\n🔍 Manutenções filtradas (preventiva + 'óleo'): ${filtered.length}`);
        results.tests.push({
            name: 'Filtros de Busca',
            status: 'SUCESSO',
            count: filtered.length
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Filtros de Busca - FALHA', searchResult));
        results.tests.push({
            name: 'Filtros de Busca',
            status: 'FALHA',
            error: searchResult.error
        });
    }

    console.log('\n\n' + '='.repeat(80));
    console.log('📊 RESUMO DOS TESTES DE MANUTENÇÕES');
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
        else if (test.status.includes('PULADO')) icon = '⚠️';
        else if (test.status.includes('INFO')) icon = 'ℹ️';

        console.log(`${icon} ${index + 1}. ${test.name}: ${test.status}`);
        if (test.error) console.log(`   Erro: ${test.error}`);
        if (test.count !== undefined) console.log(`   Quantidade: ${test.count}`);
        if (test.maintenanceId) console.log(`   ID Manutenção: ${test.maintenanceId}`);
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
        
        return runMaintenanceTests(token, companyId);
    })
    .then(results => {
        console.log('\n\n' + '='.repeat(80));
        console.log('📊 RESULTADO FINAL DOS TESTES DE MANUTENÇÕES');
        console.log('='.repeat(80));
        console.log(`Total de testes:  ${results.total}`);
        console.log(`✅ Sucessos:      ${results.success}`);
        console.log(`❌ Falhas:        ${results.failed}`);
        console.log(`📈 Taxa de êxito: ${((results.success / results.total) * 100).toFixed(1)}%`);
        console.log('='.repeat(80) + '\n');
        
        process.exit(results.failed > 0 ? 1 : 0);
    })
    .catch(error => {
        console.error('\n❌ Erro nos testes de manutenções:', error.message);
        process.exit(1);
    });
}

module.exports = { runMaintenanceTests };

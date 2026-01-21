/**
 * VeiGest API - Testes de Abastecimentos
 * Testa endpoints de gestão de registros de combustível com multi-tenancy
 */

const { apiRequest, formatTestResult } = require('../utils/http-client.js');

/**
 * Executa todos os testes de abastecimentos
 * @param {string} token - Token de autenticação
 * @param {number} companyId - ID da empresa para multi-tenancy
 */
async function runFuelLogTests(token, companyId) {
    console.log('\n⛽ INICIANDO TESTES DE ABASTECIMENTOS\n');
    console.log('=' .repeat(80));
    console.log(`Token: ${token.substring(0, 30)}...`);
    console.log(`Company ID: ${companyId}`);
    console.log('='.repeat(80));
    
    const results = {
        total: 0,
        success: 0,
        failed: 0,
        tests: [],
        createdFuelLogId: null,
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

    // Teste 1: Listar todos os registros de abastecimento
    console.log('\n📝 Teste 1: Listar todos os registros de abastecimento');
    const listResult = await apiRequest('GET', '/fuel-logs', {
        token: token
    });
    
    results.total++;
    if (listResult.success) {
        results.success++;
        console.log(formatTestResult('Listar Abastecimentos - Sucesso', listResult));
        const fuelLogs = listResult.response.body?.data || [];
        console.log(`\n📊 Total de registros: ${fuelLogs.length}`);
        
        if (fuelLogs.length > 0) {
            console.log('\n📋 Primeiros registros:');
            fuelLogs.slice(0, 3).forEach(log => {
                console.log(`   - ${log.data_abastecimento}: ${log.litros}L - R$ ${log.custo_total} (${log.local || 'Local não informado'})`);
            });
        }
        
        results.tests.push({
            name: 'Listar Abastecimentos',
            status: 'SUCESSO',
            count: fuelLogs.length
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Listar Abastecimentos - FALHA', listResult));
        results.tests.push({
            name: 'Listar Abastecimentos',
            status: 'FALHA',
            error: listResult.error
        });
    }

    // Teste 2: Criar novo registro de abastecimento (apenas se tiver veículo)
    if (results.testVehicleId) {
        console.log('\n📝 Teste 2: Criar novo registro de abastecimento');
        const newFuelLog = {
            vehicle_id: results.testVehicleId,
            litros: 45.5,
            custo_total: 289.75,
            quilometragem: 47500,
            data_abastecimento: '2024-12-18',
            local: 'Posto API Teste',
            preco_por_litro: 6.37,
            observacoes: 'Abastecimento criado via teste automatizado'
        };
        
        const createResult = await apiRequest('POST', '/fuel-logs', {
            token: token,
            body: newFuelLog
        });
        
        results.total++;
        const fuelLogData = createResult.response.body?.data || createResult.response.body;
        if (createResult.success && fuelLogData?.id) {
            results.success++;
            results.createdFuelLogId = fuelLogData.id;
            console.log(formatTestResult('Criar Abastecimento - Sucesso', createResult));
            console.log(`\n✅ Abastecimento criado com ID: ${results.createdFuelLogId}`);
            
            results.tests.push({
                name: 'Criar Abastecimento',
                status: 'SUCESSO',
                fuelLogId: results.createdFuelLogId
            });
        } else {
            results.failed++;
            console.log(formatTestResult('Criar Abastecimento - FALHA', createResult));
            results.tests.push({
                name: 'Criar Abastecimento',
                status: 'FALHA',
                error: createResult.error
            });
        }
    } else {
        console.log('\n⚠️ Teste 2: Criar abastecimento - PULADO (sem veículo)');
        results.tests.push({
            name: 'Criar Abastecimento',
            status: 'PULADO - Sem veículo disponível'
        });
    }

    // Teste 3: Visualizar registro específico
    if (results.createdFuelLogId) {
        console.log('\n📝 Teste 3: Visualizar registro específico');
        const viewResult = await apiRequest('GET', `/fuel-logs/${results.createdFuelLogId}`, {
            token: token
        });
        
        results.total++;
        if (viewResult.success) {
            results.success++;
            console.log(formatTestResult('Visualizar Abastecimento - Sucesso', viewResult));
            const fuelLog = viewResult.response.body?.data || viewResult.response.body;
            if (fuelLog) {
                console.log(`\n📊 Detalhes do abastecimento:`);
                console.log(`   ID: ${fuelLog.id}`);
                console.log(`   Data: ${fuelLog.data_abastecimento}`);
                console.log(`   Litros: ${fuelLog.litros}L`);
                console.log(`   Custo total: R$ ${fuelLog.custo_total}`);
                console.log(`   Preço/litro: R$ ${fuelLog.preco_por_litro}`);
                console.log(`   Local: ${fuelLog.local}`);
                console.log(`   Quilometragem: ${fuelLog.quilometragem}km`);
            }
            results.tests.push({
                name: 'Visualizar Abastecimento',
                status: 'SUCESSO'
            });
        } else {
            results.failed++;
            console.log(formatTestResult('Visualizar Abastecimento - FALHA', viewResult));
            results.tests.push({
                name: 'Visualizar Abastecimento',
                status: 'FALHA',
                error: viewResult.error
            });
        }
    }

    // Teste 4: Atualizar registro
    if (results.createdFuelLogId) {
        console.log('\n📝 Teste 4: Atualizar registro de abastecimento');
        const updateData = {
            custo_total: 295.00,
            preco_por_litro: 6.48,
            observacoes: 'Abastecimento atualizado via teste API - Preço corrigido'
        };
        
        const updateResult = await apiRequest('PUT', `/fuel-logs/${results.createdFuelLogId}`, {
            token: token,
            body: updateData
        });
        
        results.total++;
        if (updateResult.success) {
            results.success++;
            console.log(formatTestResult('Atualizar Abastecimento - Sucesso', updateResult));
            const updatedFuelLog = updateResult.response.body?.data || updateResult.response.body;
            if (updatedFuelLog) {
                console.log(`\n✅ Abastecimento atualizado:`);
                console.log(`   Custo total: R$ ${updatedFuelLog.custo_total}`);
                console.log(`   Preço/litro: R$ ${updatedFuelLog.preco_por_litro}`);
                console.log(`   Observações: ${updatedFuelLog.observacoes}`);
            }
            results.tests.push({
                name: 'Atualizar Abastecimento',
                status: 'SUCESSO'
            });
        } else {
            results.failed++;
            console.log(formatTestResult('Atualizar Abastecimento - FALHA', updateResult));
            results.tests.push({
                name: 'Atualizar Abastecimento',
                status: 'FALHA',
                error: updateResult.error
            });
        }
    }

    // Teste 5: Listar abastecimentos por veículo
    if (results.testVehicleId) {
        console.log('\n📝 Teste 5: Listar abastecimentos por veículo');
        const vehicleFuelLogsResult = await apiRequest('GET', `/fuel-logs/by-vehicle/${results.testVehicleId}`, {
            token: token
        });
        
        results.total++;
        if (vehicleFuelLogsResult.success) {
            results.success++;
            console.log(formatTestResult('Abastecimentos por Veículo - Sucesso', vehicleFuelLogsResult));
            const vehicleFuelLogs = vehicleFuelLogsResult.response.body?.data || [];
            console.log(`\n📊 Abastecimentos do veículo ${results.testVehicleId}: ${vehicleFuelLogs.length}`);
            results.tests.push({
                name: 'Abastecimentos por Veículo',
                status: 'SUCESSO',
                count: vehicleFuelLogs.length
            });
        } else {
            results.failed++;
            console.log(formatTestResult('Abastecimentos por Veículo - FALHA', vehicleFuelLogsResult));
            results.tests.push({
                name: 'Abastecimentos por Veículo',
                status: 'FALHA',
                error: vehicleFuelLogsResult.error
            });
        }
    }

    // Teste 6: Estatísticas de consumo
    console.log('\n📝 Teste 6: Obter estatísticas de consumo');
    const statsResult = await apiRequest('GET', '/fuel-logs/stats?period=monthly', {
        token: token
    });
    
    results.total++;
    if (statsResult.success) {
        results.success++;
        console.log(formatTestResult('Estatísticas de Consumo - Sucesso', statsResult));
        const stats = statsResult.response.body?.data || statsResult.response.body;
        if (stats && stats.summary) {
            console.log(`\n📊 Estatísticas mensais:`);
            console.log(`   Total de registros: ${stats.summary.total_fuel_logs || 0}`);
            console.log(`   Total de litros: ${stats.summary.total_liters || 0}L`);
            console.log(`   Custo total: R$ ${stats.summary.total_cost || 0}`);
            console.log(`   Preço médio/litro: R$ ${stats.summary.average_price_per_liter || 0}`);
            console.log(`   Eficiência média: ${stats.summary.fuel_efficiency || 0} km/L`);
            console.log(`   Custo por km: R$ ${stats.summary.cost_per_km || 0}`);
            
            if (stats.by_vehicle && stats.by_vehicle.length > 0) {
                console.log('\n🚗 Por veículo (primeiros 3):');
                stats.by_vehicle.slice(0, 3).forEach(vehicle => {
                    console.log(`   - ${vehicle.vehicle?.license_plate || 'N/A'}: ${vehicle.total_liters}L - R$ ${vehicle.total_cost}`);
                });
            }
        }
        results.tests.push({
            name: 'Estatísticas de Consumo',
            status: 'SUCESSO'
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Estatísticas de Consumo - FALHA', statsResult));
        results.tests.push({
            name: 'Estatísticas de Consumo',
            status: 'FALHA',
            error: statsResult.error
        });
    }

    // Teste 7: Alertas de combustível
    console.log('\n📝 Teste 7: Verificar alertas de combustível');
    const alertsResult = await apiRequest('GET', '/fuel-logs/alerts', {
        token: token
    });
    
    results.total++;
    if (alertsResult.success) {
        results.success++;
        console.log(formatTestResult('Alertas de Combustível - Sucesso', alertsResult));
        const alerts = alertsResult.response.body?.data || alertsResult.response.body;
        if (alerts) {
            console.log(`\n🚨 Alertas de combustível:`);
            console.log(`   Total de alertas: ${alerts.total_alerts || 0}`);
            
            if (alerts.alerts && alerts.alerts.length > 0) {
                console.log('\n⚠️ Alertas encontrados:');
                alerts.alerts.slice(0, 3).forEach(alert => {
                    console.log(`   - ${alert.vehicle?.license_plate || 'N/A'}: ${alert.message} (Prioridade: ${alert.priority})`);
                    if (alert.days_since_last_fuel) {
                        console.log(`     • ${alert.days_since_last_fuel} dias sem abastecimento`);
                    }
                    if (alert.km_since_last_fuel) {
                        console.log(`     • ${alert.km_since_last_fuel} km sem abastecimento`);
                    }
                });
            } else {
                console.log('   ✅ Nenhum alerta encontrado');
            }
        }
        results.tests.push({
            name: 'Alertas de Combustível',
            status: 'SUCESSO'
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Alertas de Combustível - FALHA', alertsResult));
        results.tests.push({
            name: 'Alertas de Combustível',
            status: 'FALHA',
            error: alertsResult.error
        });
    }

    // Teste 8: Relatório de eficiência
    console.log('\n📝 Teste 8: Relatório de eficiência de combustível');
    const efficiencyReportResult = await apiRequest('GET', '/fuel-logs/efficiency-report?start_date=2024-01-01&end_date=2024-12-31', {
        token: token
    });
    
    results.total++;
    if (efficiencyReportResult.success) {
        results.success++;
        console.log(formatTestResult('Relatório de Eficiência - Sucesso', efficiencyReportResult));
        const report = efficiencyReportResult.response.body?.data || efficiencyReportResult.response.body;
        if (report && report.summary) {
            console.log(`\n📊 Relatório de eficiência 2024:`);
            console.log(`   Veículos analisados: ${report.summary.total_vehicles || 0}`);
            console.log(`   Custo total de combustível: R$ ${report.summary.total_fuel_cost || 0}`);
            console.log(`   Litros totais: ${report.summary.total_liters || 0}L`);
            console.log(`   Eficiência média da frota: ${report.summary.fleet_average_efficiency || 0} km/L`);
            
            if (report.vehicle_efficiency && report.vehicle_efficiency.length > 0) {
                console.log('\n🚗 Eficiência por veículo (primeiros 3):');
                report.vehicle_efficiency.slice(0, 3).forEach(vehicle => {
                    console.log(`   - ${vehicle.vehicle?.license_plate || 'N/A'}: ${vehicle.fuel_efficiency} km/L - R$ ${vehicle.cost_per_km}/km`);
                });
            }
            
            if (report.recommendations && report.recommendations.length > 0) {
                console.log('\n💡 Recomendações:');
                report.recommendations.slice(0, 3).forEach(rec => {
                    console.log(`   - ${rec}`);
                });
            }
        }
        results.tests.push({
            name: 'Relatório de Eficiência',
            status: 'SUCESSO'
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Relatório de Eficiência - FALHA', efficiencyReportResult));
        results.tests.push({
            name: 'Relatório de Eficiência',
            status: 'FALHA',
            error: efficiencyReportResult.error
        });
    }

    // Teste 9: Filtrar por data
    console.log('\n📝 Teste 9: Filtrar abastecimentos por período');
    const dateFilterResult = await apiRequest('GET', '/fuel-logs?start_date=2024-01-01&end_date=2024-12-31', {
        token: token
    });
    
    results.total++;
    if (dateFilterResult.success) {
        results.success++;
        console.log(formatTestResult('Filtro por Data - Sucesso', dateFilterResult));
        const filtered = dateFilterResult.response.body?.data || [];
        console.log(`\n🔍 Abastecimentos em 2024: ${filtered.length}`);
        results.tests.push({
            name: 'Filtro por Data',
            status: 'SUCESSO',
            count: filtered.length
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Filtro por Data - FALHA', dateFilterResult));
        results.tests.push({
            name: 'Filtro por Data',
            status: 'FALHA',
            error: dateFilterResult.error
        });
    }

    // Teste 10: Busca por local
    console.log('\n📝 Teste 10: Buscar por local de abastecimento');
    const searchResult = await apiRequest('GET', '/fuel-logs?search=posto', {
        token: token
    });
    
    results.total++;
    if (searchResult.success) {
        results.success++;
        console.log(formatTestResult('Busca por Local - Sucesso', searchResult));
        const searchResults = searchResult.response.body?.data || [];
        console.log(`\n🔍 Abastecimentos com 'posto': ${searchResults.length}`);
        
        if (searchResults.length > 0) {
            console.log('\n📋 Locais encontrados:');
            const uniqueLocals = [...new Set(searchResults.map(log => log.local).filter(Boolean))];
            uniqueLocals.slice(0, 3).forEach(local => {
                console.log(`   - ${local}`);
            });
        }
        
        results.tests.push({
            name: 'Busca por Local',
            status: 'SUCESSO',
            count: searchResults.length
        });
    } else {
        results.failed++;
        console.log(formatTestResult('Busca por Local - FALHA', searchResult));
        results.tests.push({
            name: 'Busca por Local',
            status: 'FALHA',
            error: searchResult.error
        });
    }

    // Teste 11: Estatísticas com filtro por veículo
    if (results.testVehicleId) {
        console.log('\n📝 Teste 11: Estatísticas específicas do veículo');
        const vehicleStatsResult = await apiRequest('GET', `/fuel-logs/stats?vehicle_id=${results.testVehicleId}&period=yearly`, {
            token: token
        });
        
        results.total++;
        if (vehicleStatsResult.success) {
            results.success++;
            console.log(formatTestResult('Estatísticas do Veículo - Sucesso', vehicleStatsResult));
            const stats = vehicleStatsResult.response.body?.data || vehicleStatsResult.response.body;
            if (stats && stats.summary) {
                console.log(`\n📊 Estatísticas do veículo ${results.testVehicleId}:`);
                console.log(`   Registros: ${stats.summary.total_fuel_logs || 0}`);
                console.log(`   Litros: ${stats.summary.total_liters || 0}L`);
                console.log(`   Custo: R$ ${stats.summary.total_cost || 0}`);
                console.log(`   Eficiência: ${stats.summary.fuel_efficiency || 0} km/L`);
            }
            results.tests.push({
                name: 'Estatísticas do Veículo',
                status: 'SUCESSO'
            });
        } else {
            results.failed++;
            console.log(formatTestResult('Estatísticas do Veículo - FALHA', vehicleStatsResult));
            results.tests.push({
                name: 'Estatísticas do Veículo',
                status: 'FALHA',
                error: vehicleStatsResult.error
            });
        }
    }

    console.log('\n\n' + '='.repeat(80));
    console.log('📊 RESUMO DOS TESTES DE ABASTECIMENTOS');
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
        if (test.fuelLogId) console.log(`   ID Abastecimento: ${test.fuelLogId}`);
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
        
        return runFuelLogTests(token, companyId);
    })
    .then(results => {
        console.log('\n\n' + '='.repeat(80));
        console.log('📊 RESULTADO FINAL DOS TESTES DE ABASTECIMENTOS');
        console.log('='.repeat(80));
        console.log(`Total de testes:  ${results.total}`);
        console.log(`✅ Sucessos:      ${results.success}`);
        console.log(`❌ Falhas:        ${results.failed}`);
        console.log(`📈 Taxa de êxito: ${((results.success / results.total) * 100).toFixed(1)}%`);
        console.log('='.repeat(80) + '\n');
        
        process.exit(results.failed > 0 ? 1 : 0);
    })
    .catch(error => {
        console.error('\n❌ Erro nos testes de abastecimentos:', error.message);
        process.exit(1);
    });
}

module.exports = { runFuelLogTests };

/**
 * Teste de Conectividade Completa - API VeiGest
 * Testa conectividade e estrutura básica de todos os endpoints
 */

const API_URL = 'http://localhost:8002/api';

console.log('🔍 Testando conectividade completa da API VeiGest...\n');
console.log(`Base URL: ${API_URL}\n`);

// Lista de endpoints para testar
const endpoints = [
    // Autenticação
    { method: 'POST', path: '/auth/login', name: 'Login' },
    
    // Empresas
    { method: 'GET', path: '/company/1', name: 'Visualizar Empresa', needsAuth: true },
    
    // Veículos  
    { method: 'GET', path: '/vehicle', name: 'Listar Veículos', needsAuth: true },
    
    // Usuários
    { method: 'GET', path: '/user', name: 'Listar Usuários', needsAuth: true },
    
    // Manutenções
    { method: 'GET', path: '/maintenance', name: 'Listar Manutenções', needsAuth: true },
    
    // Abastecimentos
    { method: 'GET', path: '/fuel-log', name: 'Listar Abastecimentos', needsAuth: true },
    
    // Rotas
    { method: 'GET', path: '/route', name: 'Listar Rotas', needsAuth: true },
    
    // Alertas
    { method: 'GET', path: '/alert', name: 'Listar Alertas', needsAuth: true },
    
    // Documentos
    { method: 'GET', path: '/document', name: 'Listar Documentos', needsAuth: true },
];

let globalToken = null;

async function testEndpoint(endpoint) {
    const url = API_URL + endpoint.path;
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    };
    
    let body = null;
    
    // Se for login, enviar credenciais
    if (endpoint.path === '/auth/login') {
        body = JSON.stringify({
            username: 'admin',
            password: 'admin'
        });
    }
    
    // Se precisa de autenticação e temos token, adicionar
    if (endpoint.needsAuth && globalToken) {
        headers['Authorization'] = `Bearer ${globalToken}`;
    }
    
    try {
        console.log(`🧪 Testando: ${endpoint.method} ${endpoint.path} (${endpoint.name})`);
        
        const response = await fetch(url, {
            method: endpoint.method,
            headers: headers,
            body: body
        });
        
        const contentType = response.headers.get('content-type');
        const responseText = await response.text();
        
        if (contentType && contentType.includes('application/json')) {
            const data = JSON.parse(responseText);
            
            if (response.ok) {
                console.log(`✅ ${endpoint.name}: OK (${response.status})`);
                
                // Se for login bem-sucedido, salvar token
                if (endpoint.path === '/auth/login' && data.data && data.data.access_token) {
                    globalToken = data.data.access_token;
                    console.log(`   🔑 Token obtido: ${globalToken.substring(0, 30)}...`);
                }
                
                // Mostrar informações básicas da resposta
                if (Array.isArray(data.data)) {
                    console.log(`   📊 Retornou ${data.data.length} itens`);
                } else if (data.data && typeof data.data === 'object') {
                    const keys = Object.keys(data.data);
                    console.log(`   📋 Campos: ${keys.slice(0, 5).join(', ')}${keys.length > 5 ? '...' : ''}`);
                }
                
                return { success: true, status: response.status, data };
            } else {
                console.log(`⚠️  ${endpoint.name}: Erro ${response.status}`);
                if (data.message) {
                    console.log(`   📝 Mensagem: ${data.message}`);
                }
                return { success: false, status: response.status, error: data };
            }
        } else {
            console.log(`❌ ${endpoint.name}: Resposta não-JSON (${contentType})`);
            console.log(`   📄 Conteúdo: ${responseText.substring(0, 200)}...`);
            return { success: false, status: response.status, error: 'Non-JSON response' };
        }
        
    } catch (error) {
        console.log(`❌ ${endpoint.name}: Erro de conexão`);
        console.log(`   🔥 Erro: ${error.message}`);
        return { success: false, error: error.message };
    }
}

async function runConnectivityTests() {
    console.log('═'.repeat(80));
    console.log('🚀 INICIANDO TESTES DE CONECTIVIDADE');
    console.log('═'.repeat(80));
    
    let totalTests = 0;
    let successfulTests = 0;
    const results = [];
    
    // Testar cada endpoint sequencialmente
    for (const endpoint of endpoints) {
        console.log('\n' + '─'.repeat(60));
        
        const result = await testEndpoint(endpoint);
        results.push({
            endpoint: endpoint.name,
            ...result
        });
        
        totalTests++;
        if (result.success) {
            successfulTests++;
        }
        
        // Pequena pausa entre requests
        await new Promise(resolve => setTimeout(resolve, 500));
    }
    
    // Relatório final
    console.log('\n\n');
    console.log('═'.repeat(80));
    console.log('📊 RELATÓRIO DE CONECTIVIDADE');
    console.log('═'.repeat(80));
    console.log(`Total de endpoints testados: ${totalTests}`);
    console.log(`✅ Sucessos: ${successfulTests}`);
    console.log(`❌ Falhas: ${totalTests - successfulTests}`);
    console.log(`📈 Taxa de sucesso: ${((successfulTests / totalTests) * 100).toFixed(1)}%`);
    
    console.log('\n📋 DETALHAMENTO:');
    console.log('─'.repeat(80));
    results.forEach((result, index) => {
        const icon = result.success ? '✅' : '❌';
        const status = result.status ? `(${result.status})` : '';
        console.log(`${icon} ${index + 1}. ${result.endpoint} ${status}`);
        if (!result.success && result.error) {
            console.log(`   💬 ${typeof result.error === 'string' ? result.error : result.error.message || 'Erro desconhecido'}`);
        }
    });
    
    console.log('\n');
    
    if (successfulTests === totalTests) {
        console.log('🎉 TODOS OS TESTES PASSARAM!');
        console.log('✨ A API está funcionando corretamente.');
        console.log('\n💡 Próximos passos:');
        console.log('   • Execute: node run-all-tests.js (testes completos)');
        console.log('   • Consulte: API_ENDPOINTS_COMPLETE.md (documentação)');
    } else {
        console.log('⚠️  ALGUNS TESTES FALHARAM');
        console.log('\n💡 Possíveis causas:');
        console.log('   • Servidor backend não está rodando');
        console.log('   • Banco de dados não está configurado');
        console.log('   • URL base incorreta');
        console.log('   • Módulos da API não estão carregados');
        console.log('\n🔧 Troubleshooting:');
        console.log('   • Verifique logs: docker-compose logs backend');
        console.log('   • Teste manual: curl http://localhost:21080/api/auth/info');
    }
    
    console.log('\n' + '═'.repeat(80));
    console.log(`Teste executado em: ${new Date().toLocaleString('pt-PT')}`);
    console.log('═'.repeat(80));
}

// Executar testes
runConnectivityTests().catch(error => {
    console.error('\n💥 ERRO CRÍTICO:', error);
    process.exit(1);
});

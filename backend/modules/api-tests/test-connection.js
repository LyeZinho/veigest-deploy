/**
 * Script rápido para testar conectividade com a API
 */

const API_URL = 'http://localhost:8002/api/auth/login';

console.log('🔍 Testando conexão com API VeiGest...\n');
console.log(`URL: ${API_URL}\n`);

fetch(API_URL, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    body: JSON.stringify({
        username: 'admin',
        password: 'admin'
    })
})
.then(async response => {
    console.log(`Status: ${response.status} ${response.statusText}`);
    console.log('\nHeaders:');
    response.headers.forEach((value, key) => {
        console.log(`  ${key}: ${value}`);
    });
    
    const contentType = response.headers.get('content-type');
    console.log(`\nContent-Type: ${contentType}`);
    
    const text = await response.text();
    
    if (contentType && contentType.includes('application/json')) {
        console.log('\n✅ Resposta JSON detectada:');
        console.log(JSON.stringify(JSON.parse(text), null, 2));
    } else if (contentType && contentType.includes('text/html')) {
        console.log('\n❌ PROBLEMA: Servidor retornou HTML ao invés de JSON!');
        console.log('\nPrimeiros 500 caracteres da resposta:');
        console.log(text.substring(0, 500) + '...\n');
        console.log('⚠️  Verifique:');
        console.log('   1. O servidor backend está rodando?');
        console.log('   2. O módulo API está configurado corretamente?');
        console.log('   3. A rota /api/v1 está mapeada para backend/modules/api?');
    } else {
        console.log('\n⚠️  Resposta inesperada:');
        console.log(text.substring(0, 500));
    }
})
.catch(error => {
    console.error('\n❌ Erro de conexão:');
    console.error(error.message);
    console.log('\n⚠️  Certifique-se de que o servidor está rodando em http://localhost:8002');
});

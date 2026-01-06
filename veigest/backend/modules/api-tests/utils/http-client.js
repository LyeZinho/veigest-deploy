/**
 * VeiGest API - HTTP Client Utility
 * Fornece funções para fazer requisições HTTP e formatar respostas
 */

const API_BASE_URL = 'http://localhost:8002/api';

/**
 * Faz uma requisição HTTP para a API
 * @param {string} method - Método HTTP (GET, POST, PUT, DELETE)
 * @param {string} endpoint - Endpoint da API (ex: /auth/login)
 * @param {object} options - Opções adicionais (body, headers, token)
 * @returns {Promise<object>} Objeto com request e response detalhados
 */
async function apiRequest(method, endpoint, options = {}) {
    const url = endpoint.startsWith('http') ? endpoint : `${API_BASE_URL}${endpoint}`;
    const startTime = Date.now();
    
    // Preparar headers
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(options.headers || {})
    };

    // Adicionar token de autenticação se fornecido
    if (options.token) {
        headers['Authorization'] = `Bearer ${options.token}`;
    }

    // Preparar request config
    const requestConfig = {
        method: method.toUpperCase(),
        headers: headers
    };

    // Adicionar body se fornecido
    if (options.body && ['POST', 'PUT', 'PATCH'].includes(method.toUpperCase())) {
        requestConfig.body = JSON.stringify(options.body);
    }

    // Preparar objeto de log do request
    const requestLog = {
        method: method.toUpperCase(),
        url: url,
        headers: headers,
        body: options.body || null
    };

    let responseLog = null;
    let error = null;

    try {
        const response = await fetch(url, requestConfig);
        const endTime = Date.now();
        const responseText = await response.text();
        
        let responseData = null;
        try {
            responseData = responseText ? JSON.parse(responseText) : null;
        } catch (e) {
            responseData = responseText;
        }

        responseLog = {
            status: response.status,
            statusText: response.statusText,
            headers: Object.fromEntries(response.headers.entries()),
            body: responseData,
            responseTime: `${endTime - startTime}ms`
        };

        return {
            success: response.ok,
            request: requestLog,
            response: responseLog,
            error: !response.ok ? `HTTP ${response.status}: ${response.statusText}` : null
        };

    } catch (err) {
        const endTime = Date.now();
        
        return {
            success: false,
            request: requestLog,
            response: {
                status: 0,
                statusText: 'Network Error',
                body: null,
                responseTime: `${endTime - startTime}ms`
            },
            error: err.message
        };
    }
}

/**
 * Formata o resultado de um teste para exibição
 * @param {string} testName - Nome do teste
 * @param {object} result - Resultado do apiRequest
 * @returns {string} String formatada com detalhes do teste
 */
function formatTestResult(testName, result) {
    const separator = '='.repeat(80);
    const subseparator = '-'.repeat(80);
    
    let output = `\n${separator}\n`;
    output += `📋 TESTE: ${testName}\n`;
    output += `${separator}\n\n`;

    // REQUEST
    output += `📤 REQUEST:\n`;
    output += `${subseparator}\n`;
    output += `Método:  ${result.request.method}\n`;
    output += `URL:     ${result.request.url}\n\n`;
    
    output += `Headers:\n`;
    Object.entries(result.request.headers).forEach(([key, value]) => {
        // Truncar token para exibição
        if (key === 'Authorization' && value.length > 50) {
            output += `  ${key}: ${value.substring(0, 30)}...${value.substring(value.length - 10)}\n`;
        } else {
            output += `  ${key}: ${value}\n`;
        }
    });
    
    if (result.request.body) {
        output += `\nBody:\n`;
        output += JSON.stringify(result.request.body, null, 2)
            .split('\n')
            .map(line => `  ${line}`)
            .join('\n') + '\n';
    }

    // RESPONSE
    output += `\n📥 RESPONSE:\n`;
    output += `${subseparator}\n`;
    output += `Status:  ${result.response.status} ${result.response.statusText}\n`;
    output += `Tempo:   ${result.response.responseTime}\n\n`;
    
    if (result.response.headers && Object.keys(result.response.headers).length > 0) {
        output += `Headers:\n`;
        Object.entries(result.response.headers).forEach(([key, value]) => {
            output += `  ${key}: ${value}\n`;
        });
        output += '\n';
    }
    
    if (result.response.body) {
        output += `Body:\n`;
        const bodyStr = typeof result.response.body === 'string' 
            ? result.response.body 
            : JSON.stringify(result.response.body, null, 2);
        output += bodyStr.split('\n').map(line => `  ${line}`).join('\n') + '\n';
    }

    // RESULTADO
    output += `\n${subseparator}\n`;
    if (result.success) {
        output += `✅ RESULTADO: SUCESSO\n`;
    } else {
        output += `❌ RESULTADO: FALHA\n`;
        if (result.error) {
            output += `Erro: ${result.error}\n`;
        }
    }
    output += `${separator}\n`;

    return output;
}

/**
 * Decodifica um token Base64
 * @param {string} token - Token Base64
 * @returns {object|null} Dados decodificados ou null se inválido
 */
function decodeToken(token) {
    try {
        const base64 = token.replace(/-/g, '+').replace(/_/g, '/');
        const jsonPayload = decodeURIComponent(
            atob(base64)
                .split('')
                .map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2))
                .join('')
        );
        return JSON.parse(jsonPayload);
    } catch (e) {
        return null;
    }
}

/**
 * Aguarda um tempo especificado
 * @param {number} ms - Milissegundos para aguardar
 * @returns {Promise<void>}
 */
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

// Exportar funções para Node.js
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        apiRequest,
        formatTestResult,
        decodeToken,
        sleep,
        API_BASE_URL
    };
}

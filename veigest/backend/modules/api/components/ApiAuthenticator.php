<?php

namespace backend\modules\api\components;

use Yii;
use yii\filters\auth\AuthMethod;
use yii\web\UnauthorizedHttpException;
use yii\web\IdentityInterface;
use common\models\User;

/**
 * Autenticador personalizado da API VeiGest
 * 
 * Implementa autenticação Bearer Token com Base64 encoding
 * Suporta multi-tenancy através de company_id no token
 * 
 * .
 */
class ApiAuthenticator extends AuthMethod
{
    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get('Authorization');
        
        if (!$authHeader) {
            return null;
        }
        
        // Verificar formato Bearer Token
        if (!preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            return null;
        }
        
        $token = $matches[1];
        
        // Decodificar token Base64
        $decodedToken = base64_decode($token);
        if (!$decodedToken) {
            throw new UnauthorizedHttpException('Token inválido');
        }
        
        $tokenData = json_decode($decodedToken, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UnauthorizedHttpException('Formato de token inválido');
        }
        
        // Validar estrutura do token
        if (!isset($tokenData['user_id']) || !isset($tokenData['expires_at'])) {
            throw new UnauthorizedHttpException('Token incompleto');
        }
        
        // Verificar expiração
        if ($tokenData['expires_at'] < time()) {
            throw new UnauthorizedHttpException('Token expirado');
        }
        
        // Buscar usuário
        $identity = User::findIdentity($tokenData['user_id']);
        if (!$identity) {
            throw new UnauthorizedHttpException('Usuário não encontrado');
        }
        
        // Verificar se usuário está ativo
        if ($identity->status !== 'active') {
            throw new UnauthorizedHttpException('Usuário inativo');
        }
        
        // Armazenar dados do token para uso posterior
        Yii::$app->params['token_data'] = $tokenData;
        Yii::$app->params['company_id'] = $tokenData['company_id'] ?? null;
        Yii::$app->params['user_id'] = $tokenData['user_id'];
        
        return $identity;
    }
    
    /**
     * {@inheritdoc}
     */
    public function challenge($response)
    {
        $response->getHeaders()->set('WWW-Authenticate', 'Bearer realm="API"');
    }
}

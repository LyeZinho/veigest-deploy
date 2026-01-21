<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\filters\Cors;
use yii\web\Response;
use backend\modules\api\components\ApiAuthenticator;

/**
 * Controlador base para API REST
 * 
 * Fornece configurações comuns e comportamentos padrão
 * para todos os controladores da API VeiGest
 * 
 * .
 */
class BaseApiController extends ActiveController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // CORS - Cross-Origin Resource Sharing
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Max-Age' => 86400,
            ],
        ];

        // Content Negotiator
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/xml' => Response::FORMAT_XML,
            ],
        ];

        // Verb Filter
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index' => ['GET'],
                'view' => ['GET'],
                'create' => ['POST'],
                'update' => ['PUT', 'PATCH'],
                'delete' => ['DELETE'],
            ],
        ];

        // Autenticação (será sobrescrita pelos controladores filhos se necessário)
        $behaviors['authenticator'] = [
            'class' => ApiAuthenticator::class,
            'except' => ['options'], // Permitir OPTIONS sem autenticação
        ];

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();

        // Personalizar serializer para incluir meta informações
        $actions['index']['dataFilter'] = [
            'class' => \yii\data\ActiveDataFilter::class,
            'searchModel' => $this->modelClass,
        ];

        return $actions;
    }

    /**
     * Verificar acesso do usuário a determinada ação
     * 
     * @param string $action ID da ação
     * @param object $model Modelo sendo acessado (se aplicável)
     * @param array $params Parâmetros adicionais
     * @throws \yii\web\ForbiddenHttpException se acesso negado
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        // Verificações básicas de multi-tenancy
        if ($model && method_exists($model, 'hasAttribute') && $model->hasAttribute('company_id')) {
            $userCompanyId = Yii::$app->params['company_id'] ?? null;
            
            if ($userCompanyId && $model->company_id != $userCompanyId) {
                throw new \yii\web\ForbiddenHttpException('Acesso negado: recurso não pertence à sua empresa');
            }
        }
        
        // Implementações específicas devem sobrescrever este método
        parent::checkAccess($action, $model, $params);
    }

    /**
     * Ação OPTIONS para CORS preflight
     */
    public function actionOptions()
    {
        Yii::$app->response->statusCode = 204;
        return null;
    }

    /**
     * Obter company_id do token de autenticação
     * 
     * @return integer|null
     */
    protected function getCompanyId()
    {
        return Yii::$app->params['company_id'] ?? null;
    }

    /**
     * Obter user_id do token de autenticação
     * 
     * @return integer|null
     */
    protected function getUserId()
    {
        return Yii::$app->params['user_id'] ?? null;
    }

    /**
     * Resposta de erro padronizada
     * 
     * @param string $message Mensagem de erro
     * @param integer $code Código de status HTTP
     * @param array $errors Detalhes dos erros (opcional)
     * @return array
     */
    protected function errorResponse($message, $code = 400, $errors = [])
    {
        Yii::$app->response->statusCode = $code;
        
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => date('c'),
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        return $response;
    }

    /**
     * Resposta de sucesso padronizada
     * 
     * @param mixed $data Dados de resposta
     * @param string $message Mensagem de sucesso (opcional)
     * @param integer $code Código de status HTTP
     * @return array
     */
    protected function successResponse($data, $message = null, $code = 200)
    {
        Yii::$app->response->statusCode = $code;
        
        $response = [
            'success' => true,
            'data' => $data,
            'timestamp' => date('c'),
        ];
        
        if ($message) {
            $response['message'] = $message;
        }
        
        return $response;
    }
}

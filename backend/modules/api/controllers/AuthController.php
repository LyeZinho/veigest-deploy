<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\rest\Controller;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;
use common\models\User;
use backend\modules\api\components\ApiAuthenticator;

/**
 * Auth API Controller
 * 
 * Fornece endpoints de autenticação para API VeiGest
 * Implementa login, logout, refresh token e informações do usuário
 * 
 * .
 */
class AuthController extends Controller
{
    /**
     * Registro público de usuário
     * POST /api/auth/register
     * Permite criar usuário sem autenticação
     */
    public function actionRegister()
    {
        $body = Yii::$app->request->bodyParams;
        $username = $body['username'] ?? null;
        $email = $body['email'] ?? null;
        $password = $body['password'] ?? null;
        $role = $body['role'] ?? 'driver';

        if (!$username || !$email || !$password) {
            return [
                'success' => false,
                'message' => 'Campos obrigatórios: username, email, password.'
            ];
        }

        // Verificar se já existe username ou email
        if (\common\models\User::find()->where(['username' => $username])->exists()) {
            return [
                'success' => false,
                'message' => 'Username já existe.'
            ];
        }
        if (\common\models\User::find()->where(['email' => $email])->exists()) {
            return [
                'success' => false,
                'message' => 'Email já existe.'
            ];
        }

        $user = new \common\models\User();
        $user->username = $username;
        $user->name = $username; // Preenche name obrigatório
        $user->email = $email;
        $user->setPassword($password);
        $user->auth_key = Yii::$app->security->generateRandomString();
        $user->status = 'active';
        $user->roles = $role;
        $user->company_id = 1; // Registro público vincula à empresa padrão (ID 1)

        if ($user->save()) {
            return [
                'success' => true,
                'message' => 'Usuário registrado com sucesso.',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'roles' => $user->roles,
                    'status' => $user->status,
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Erro ao registrar usuário.',
                'errors' => $user->errors
            ];
        }
    }
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // CORS filter
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Max-Age' => 86400,
            ],
        ];

        // Content negotiator
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];

        // Verb filter
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'login' => ['POST'],
                'logout' => ['POST'],
                'me' => ['GET'],
                'refresh' => ['POST'],
                'info' => ['GET'],
            ],
        ];

        // Autenticação apenas para endpoints protegidos
        $behaviors['authenticator'] = [
            'class' => ApiAuthenticator::class,
            'except' => ['login', 'register', 'options'],
        ];

        return $behaviors;
    }

    /**
     * Login de usuário
     * 
     * @return array
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     */
    public function actionLogin()
    {
        $body = Yii::$app->request->bodyParams;
        $username = $body['username'] ?? null;
        $password = $body['password'] ?? null;

        // Validar dados obrigatórios
        if (!$username || !$password) {
            throw new BadRequestHttpException('Username e password são obrigatórios');
        }

        // Buscar usuário por username ou email
        $user = User::findByUsername($username);
        if (!$user) {
            $user = User::find()->where(['email' => $username])->one();
        }

        // Validar credenciais
        if (!$user || !$user->validatePassword($password)) {
            throw new UnauthorizedHttpException('Credenciais inválidas');
        }

        // Verificar se usuário está ativo
        if ($user->status !== 'active') {
            throw new UnauthorizedHttpException('Conta de usuário inativa');
        }

        // Obter informações da empresa
        $company = null;
        if ($user->company_id) {
            $company = \backend\modules\api\models\Company::findOne($user->company_id);
        }

        // Obter papéis do usuário (RBAC)
        $roles = [];
        $permissions = [];
        
        if (Yii::$app->authManager) {
            $roles = array_keys(Yii::$app->authManager->getRolesByUser($user->id));
            
            // Obter permissões do usuário
            foreach ($roles as $roleName) {
                $role = Yii::$app->authManager->getRole($roleName);
                if ($role) {
                    $rolePermissions = array_keys(Yii::$app->authManager->getPermissionsByRole($roleName));
                    $permissions = array_merge($permissions, $rolePermissions);
                }
            }
            $permissions = array_unique($permissions);
        }

        // Gerar token Base64 com dados do usuário
        $expiresAt = time() + (24 * 60 * 60); // 24 horas
        $tokenData = [
            'user_id' => $user->id,
            'username' => $user->username,
            'company_id' => $company ? $company->id : null,
            'company_code' => $company ? $company->code ?? null : null,
            'roles' => $roles,
            'permissions' => $permissions,
            'expires_at' => $expiresAt,
            'issued_at' => time(),
        ];
        
        $accessToken = base64_encode(json_encode($tokenData));

        return [
            'success' => true,
            'data' => [
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'expires_in' => 86400, // 24 horas em segundos
                'expires_at' => $expiresAt,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => $user->status,
                    'company_id' => $user->company_id,
                ],
                'company' => $company ? [
                    'id' => $company->id,
                    'name' => $company->nome ?? $company->name,
                    'code' => $company->code ?? null,
                    'email' => $company->email,
                ] : null,
                'roles' => $roles,
                'permissions' => $permissions,
            ]
        ];
    }

    /**
     * Obter informações do usuário autenticado
     * 
     * @return array
     */
    public function actionMe()
    {
        $tokenData = Yii::$app->params['token_data'] ?? [];
        
        if (empty($tokenData['user_id'])) {
            throw new UnauthorizedHttpException('Token inválido');
        }
        
        $user = User::findIdentity($tokenData['user_id']);
        if (!$user) {
            throw new UnauthorizedHttpException('Usuário não encontrado');
        }
        
        $company = null;
        if ($user->company_id) {
            $company = \backend\modules\api\models\Company::findOne($user->company_id);
        }

        return [
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => $user->status,
                    'company_id' => $user->company_id,
                ],
                'company' => $company ? [
                    'id' => $company->id,
                    'name' => $company->name,
                    'code' => $company->code,
                    'email' => $company->email,
                ] : null,
                'roles' => $tokenData['roles'] ?? [],
                'permissions' => $tokenData['permissions'] ?? [],
                'token_info' => [
                    'issued_at' => $tokenData['issued_at'] ?? null,
                    'expires_at' => $tokenData['expires_at'] ?? null,
                ],
            ]
        ];
    }

    /**
     * Refresh do token de autenticação
     * 
     * @return array
     */
    public function actionRefresh()
    {
        $user = Yii::$app->user->identity;
        $oldTokenData = Yii::$app->params['token_data'] ?? [];
        
        // Gerar novo token com mesmos dados mas nova expiração
        $expiresAt = time() + (24 * 60 * 60); // 24 horas
        $tokenData = array_merge($oldTokenData, [
            'expires_at' => $expiresAt,
            'issued_at' => time(),
        ]);
        
        $accessToken = base64_encode(json_encode($tokenData));

        return [
            'success' => true,
            'data' => [
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'expires_in' => 86400,
                'expires_at' => $expiresAt,
            ]
        ];
    }

    /**
     * Logout do usuário
     * 
     * @return array
     */
    public function actionLogout()
    {
        // Como usamos tokens stateless, o logout é apenas do lado cliente
        // Aqui podemos registrar o logout para auditoria se necessário
        
        return [
            'success' => true,
            'message' => 'Logout realizado com sucesso',
        ];
    }

    /**
     * Informações sobre a API
     * 
     * @return array
     */
    public function actionInfo()
    {
        return [
            'success' => true,
            'data' => [
                'api_name' => 'VeiGest REST API',
                'version' => '1.0',
                'framework' => 'Yii2',
                'authentication' => 'Bearer Token (Base64)',
                'endpoints' => [
                    'auth' => [
                        'POST /api/auth/login',
                        'GET /api/auth/me',
                        'POST /api/auth/refresh',
                        'POST /api/auth/logout',
                        'GET /api/auth/info',
                    ],
                ],
                'timestamp' => date('c'),
            ]
        ];
    }

    /**
     * Ação OPTIONS para CORS preflight
     */
    public function actionOptions()
    {
        Yii::$app->response->statusCode = 204;
        return null;
    }
}

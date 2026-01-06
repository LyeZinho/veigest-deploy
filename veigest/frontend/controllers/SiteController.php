<?php

namespace frontend\controllers;

use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use frontend\models\TicketForm;

/**
 * Site controller
 */
class SiteController extends Controller
{
    public function beforeAction($action)
    {
        // Use error layout for error pages (cleaner 403/404/500 pages)
        if ($action->id === 'error') {
            $this->layout = 'error';
        }
        return parent::beforeAction($action);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'signup', 'my-tickets', 'ticket-view'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['my-tickets', 'ticket-view'],
                        'allow' => true,
                        'roles' => ['@'], // Apenas utilizadores autenticados podem ver os seus tickets
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
            'captcha' => [
                'class' => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        $this->layout = 'login';

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new \common\models\LoginForm();

        // DEBUG: Log raw POST data
        $postData = Yii::$app->request->post();
        Yii::info('RAW POST DATA: ' . json_encode($postData), 'login');

        if ($model->load(Yii::$app->request->post())) {
            Yii::info('===== LOGIN ATTEMPT =====', 'login');
            Yii::info('Model username: ' . $model->username, 'login');
            Yii::info('Model password: ' . $model->password, 'login');
            Yii::info('Password received: ' . ($model->password ? 'YES' : 'NO'), 'login');
            
            if ($model->validate()) {
                Yii::info('✓ Form validation passed', 'login');
                
                if ($model->login()) {
                    $user = Yii::$app->user->identity;
                    Yii::info('Login successful for user: ' . $user->id . ' role: ' . $user->role, 'login');

                    // Redirect based on role (using user->role field directly)
                    $role = $user->role;
                    
                    if ($role === 'admin') {
                        Yii::info('User is admin - redirecting to /site/index', 'login');
                        return $this->redirect(['/site/index']);
                    }

                    if ($role === 'manager') {
                        Yii::info('User is manager - redirecting to /dashboard/index', 'login');
                        return $this->redirect(['/dashboard/index']);
                    }

                    if ($role === 'driver') {
                        Yii::info('User is driver - redirecting to /dashboard/index', 'login');
                        return $this->redirect(['/dashboard/index']);
                    }

                    Yii::info('User has unknown role - using goHome()', 'login');
                    return $this->goHome();
                } else {
                    Yii::error('✗ Login method returned false', 'login');
                }
            } else {
                Yii::error('✗ Form validation FAILED: ' . json_encode($model->errors), 'login');
            }
        } else {
            Yii::info('No POST data received', 'login');
        }

        return $this->render('login', ['model' => $model]);
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        // Clear session and identity
        Yii::$app->user->logout();
        
        // Get cookie domain for cross-subdomain clearing
        $serverHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $isLocalhost = strpos($serverHost, 'localhost') !== false || strpos($serverHost, '127.0.0.1') !== false;
        $cookieDomain = $isLocalhost ? '' : '.dryadlang.org';
        
        // Clear identity cookie
        $cookies = Yii::$app->response->cookies;
        $cookies->remove('_identity-frontend');
        
        // Also expire the cookie directly in case remove doesn't work across domains
        if ($cookieDomain) {
            setcookie('_identity-frontend', '', time() - 3600, '/', $cookieDomain);
            setcookie('PHPSESSID', '', time() - 3600, '/', $cookieDomain);
            setcookie('_csrf-frontend', '', time() - 3600, '/', $cookieDomain);
        } else {
            setcookie('_identity-frontend', '', time() - 3600, '/');
            setcookie('PHPSESSID', '', time() - 3600, '/');
            setcookie('_csrf-frontend', '', time() - 3600, '/');
        }
        
        // Destroy session completely
        Yii::$app->session->destroy();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        }

        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {

        $this->layout = 'login';
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            }

            Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {

        $this->layout = 'login';
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return mixed
     */
    public function actionVerifyEmail($token)
    {
        \Yii::info(['route' => 'verify-email', 'token' => $token], __METHOD__);
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            \Yii::warning(['verify-email' => 'invalid', 'reason' => $e->getMessage(), 'token' => $token], __METHOD__);
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($model->verifyEmail()) {
            Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
            \Yii::info(['verify-email' => 'success', 'token' => $token], __METHOD__);
            return $this->render('verifyEmailSuccess');
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        \Yii::warning(['verify-email' => 'failed-to-save', 'token' => $token], __METHOD__);
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
    }

    /**
     * Displays ticket page.
     * Any user (logged or guest) can create a support ticket.
     *
     * @return mixed
     */
    public function actionTicket()
    {
        $model = new TicketForm();
        
        // Pre-fill form with logged user data
        $model->loadUserData();
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $ticket = $model->saveTicket();
            if ($ticket) {
                Yii::$app->session->setFlash('success', 'O seu ticket #' . $ticket->id . ' foi criado com sucesso! Entraremos em contacto brevemente.');
            } else {
                Yii::$app->session->setFlash('error', 'Ocorreu um erro ao criar o seu ticket. Por favor, tente novamente.');
            }

            return $this->refresh();
        }

        return $this->render('ticket', [
            'model' => $model,
        ]);
    }

    /**
     * Displays user's own tickets.
     * Requires authentication.
     *
     * @return mixed
     */
    public function actionMyTickets()
    {
        $this->layout = 'dashboard';
        
        $userId = Yii::$app->user->id;
        $tickets = \common\models\SupportTicket::find()
            ->where(['user_id' => $userId])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
        
        return $this->render('my-tickets', [
            'tickets' => $tickets,
        ]);
    }

    /**
     * Displays a specific ticket for the logged user.
     * Requires authentication.
     *
     * @param int $id
     * @return mixed
     */
    public function actionTicketView($id)
    {
        $this->layout = 'dashboard';
        
        $userId = Yii::$app->user->id;
        $ticket = \common\models\SupportTicket::find()
            ->where(['id' => $id, 'user_id' => $userId])
            ->one();
        
        if (!$ticket) {
            throw new \yii\web\NotFoundHttpException('O ticket solicitado não foi encontrado.');
        }
        
        return $this->render('ticket-view', [
            'ticket' => $ticket,
        ]);
    }

    /**
     * Displays pricing page.
     *
     * @return mixed
     */
    public function actionPricing()
    {
        return $this->render('pricing');
    }

    /**
     * Displays benefits page.
     *
     * @return mixed
     */
    public function actionBenefits()
    {
        return $this->render('bennefits');
    }

    /**
     * Displays services page.
     *
     * @return mixed
     */
    public function actionServices()
    {
        return $this->render('services');
    }

    /**
     * Redirects to dashboard.
     *
     * @return mixed
     */
    public function actionDashboard()
    {
        return $this->redirect(['dashboard/index']);
    }
}

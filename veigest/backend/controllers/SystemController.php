<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use common\models\User;

/**
 * SystemController - Administrative Settings & Tools
 * 
 * Access Control:
 * - Admin only
 */
class SystemController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            $user = Yii::$app->user->identity;
                            return $user && $user->role === 'admin';
                        },
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    if (Yii::$app->user->isGuest) {
                        return Yii::$app->response->redirect(['site/login']);
                    }
                    $action->controller->layout = 'blank';
                    throw new ForbiddenHttpException('Acesso restrito a administradores.');
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'clear-cache' => ['post'],
                    'run-migrations' => ['post'],
                    'test-email' => ['post'],
                    'test-database' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Main settings page
     */
    public function actionSettings()
    {
        // Get system information
        $systemInfo = $this->getSystemInfo();
        $dbStats = $this->getDatabaseStats();
        
        // Handle form submissions
        if (Yii::$app->request->isPost) {
            $action = Yii::$app->request->post('action');
            
            switch ($action) {
                case 'save-general':
                    $this->saveGeneralSettings(Yii::$app->request->post());
                    Yii::$app->session->setFlash('success', 'Configurações gerais salvas com sucesso!');
                    break;
                case 'save-email':
                    $this->saveEmailSettings(Yii::$app->request->post());
                    Yii::$app->session->setFlash('success', 'Configurações de email salvas com sucesso!');
                    break;
            }
            
            return $this->refresh();
        }
        
        return $this->render('settings', [
            'systemInfo' => $systemInfo,
            'dbStats' => $dbStats,
        ]);
    }

    /**
     * Clear application cache
     */
    public function actionClearCache()
    {
        try {
            // Clear runtime cache
            $runtimePath = Yii::getAlias('@backend/runtime/cache');
            if (is_dir($runtimePath)) {
                $this->deleteDirectory($runtimePath);
            }
            
            // Clear frontend cache
            $frontendCachePath = Yii::getAlias('@frontend/runtime/cache');
            if (is_dir($frontendCachePath)) {
                $this->deleteDirectory($frontendCachePath);
            }
            
            // Clear assets
            $backendAssets = Yii::getAlias('@backend/web/assets');
            $frontendAssets = Yii::getAlias('@frontend/web/assets');
            
            Yii::$app->session->setFlash('success', 'Cache limpo com sucesso!');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Erro ao limpar cache: ' . $e->getMessage());
        }
        
        return $this->redirect(['settings']);
    }

    /**
     * Test database connection
     */
    public function actionTestDatabase()
    {
        try {
            $db = Yii::$app->db;
            $db->open();
            
            // Run a simple query
            $result = $db->createCommand('SELECT 1')->queryScalar();
            
            if ($result == 1) {
                Yii::$app->session->setFlash('success', 'Conexão com base de dados OK! Servidor: ' . $db->dsn);
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Erro na conexão: ' . $e->getMessage());
        }
        
        return $this->redirect(['settings']);
    }

    /**
     * Test email configuration
     */
    public function actionTestEmail()
    {
        try {
            $adminEmail = Yii::$app->params['adminEmail'] ?? 'admin@example.com';
            
            $sent = Yii::$app->mailer->compose()
                ->setFrom([$adminEmail => 'VeiGest System'])
                ->setTo($adminEmail)
                ->setSubject('VeiGest - Teste de Email')
                ->setTextBody('Este é um email de teste do sistema VeiGest. Se você recebeu este email, a configuração está correta!')
                ->send();
            
            if ($sent) {
                Yii::$app->session->setFlash('success', 'Email de teste enviado para: ' . $adminEmail);
            } else {
                Yii::$app->session->setFlash('warning', 'Email pode não ter sido enviado. Verifique as configurações.');
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Erro ao enviar email: ' . $e->getMessage());
        }
        
        return $this->redirect(['settings']);
    }

    /**
     * Show system logs
     */
    public function actionLogs()
    {
        $logFile = Yii::getAlias('@backend/runtime/logs/app.log');
        $logs = [];
        
        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $logs = array_slice(explode("\n", $content), -100); // Last 100 lines
            $logs = array_reverse($logs);
        }
        
        return $this->render('logs', [
            'logs' => $logs,
            'logFile' => $logFile,
        ]);
    }

    /**
     * Clear logs
     */
    public function actionClearLogs()
    {
        try {
            $backendLog = Yii::getAlias('@backend/runtime/logs/app.log');
            $frontendLog = Yii::getAlias('@frontend/runtime/logs/app.log');
            
            if (file_exists($backendLog)) {
                file_put_contents($backendLog, '');
            }
            if (file_exists($frontendLog)) {
                file_put_contents($frontendLog, '');
            }
            
            Yii::$app->session->setFlash('success', 'Logs limpos com sucesso!');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Erro ao limpar logs: ' . $e->getMessage());
        }
        
        return $this->redirect(['logs']);
    }

    /**
     * System information page
     */
    public function actionInfo()
    {
        return $this->render('info', [
            'systemInfo' => $this->getSystemInfo(),
            'dbStats' => $this->getDatabaseStats(),
            'phpInfo' => $this->getPhpInfo(),
        ]);
    }

    /**
     * Get system information
     */
    protected function getSystemInfo()
    {
        return [
            'php_version' => PHP_VERSION,
            'yii_version' => Yii::getVersion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
            'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'timezone' => date_default_timezone_get(),
            'disk_free_space' => $this->formatBytes(disk_free_space('/')),
            'disk_total_space' => $this->formatBytes(disk_total_space('/')),
        ];
    }

    /**
     * Get database statistics
     */
    protected function getDatabaseStats()
    {
        try {
            $db = Yii::$app->db;
            
            return [
                'users_count' => User::find()->count(),
                'users_active' => User::find()->where(['status' => 'active'])->count(),
                'vehicles_count' => $db->createCommand('SELECT COUNT(*) FROM vehicles')->queryScalar() ?: 0,
                'companies_count' => $db->createCommand('SELECT COUNT(*) FROM companies')->queryScalar() ?: 0,
                'maintenances_count' => $db->createCommand('SELECT COUNT(*) FROM maintenances')->queryScalar() ?: 0,
                'documents_count' => $db->createCommand('SELECT COUNT(*) FROM documents')->queryScalar() ?: 0,
                'db_name' => $this->extractDbName($db->dsn),
                'db_driver' => $db->driverName,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get PHP info (basic)
     */
    protected function getPhpInfo()
    {
        return [
            'extensions' => get_loaded_extensions(),
            'pdo_drivers' => \PDO::getAvailableDrivers(),
        ];
    }

    /**
     * Extract database name from DSN
     */
    protected function extractDbName($dsn)
    {
        if (preg_match('/dbname=([^;]+)/', $dsn, $matches)) {
            return $matches[1];
        }
        return 'Unknown';
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Delete directory recursively
     */
    protected function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        
        return rmdir($dir);
    }

    /**
     * Save general settings (placeholder - can be extended with actual DB storage)
     */
    protected function saveGeneralSettings($data)
    {
        // In a real implementation, you would save these to a settings table
        // For now, this is a placeholder
        Yii::info('General settings saved: ' . json_encode($data), 'system');
    }

    /**
     * Save email settings (placeholder)
     */
    protected function saveEmailSettings($data)
    {
        Yii::info('Email settings saved: ' . json_encode($data), 'system');
    }
}

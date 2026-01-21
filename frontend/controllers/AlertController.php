<?php

namespace frontend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\db\Query;
use frontend\models\Maintenance;
use frontend\models\Vehicle;

/**
 * AlertController - Alert Management
 * 
 * Access Control:
 * - Admin: NO ACCESS (frontend blocked)
 * - Manager: FULL ACCESS (view, create, resolve)
 * - Driver: READ ONLY (view alerts only)
 */
class AlertController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    // Block admin from frontend
                    [
                        'allow' => false,
                        'roles' => ['admin'],
                        'denyCallback' => function ($rule, $action) {
                            throw new ForbiddenHttpException(
                                'Administrators do not have access to the frontend.'
                            );
                        },
                    ],
                    // View alerts - manager and driver
                    [
                        'allow' => true,
                        'actions' => ['index', 'notifications'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('alerts.view');
                        },
                    ],
                    // Create/resolve alerts - manager only
                    [
                        'allow' => true,
                        'actions' => ['create', 'resolve'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('alerts.create') || 
                                   Yii::$app->user->can('alerts.resolve');
                        },
                    ],
                ],
            ],
        ];
    }

    public function init()
    {
        parent::init();
        $this->layout = 'dashboard';
    }

    public function actionIndex()
    {
        $companyId = Yii::$app->user->identity->company_id;
        $today = date('Y-m-d');

        // Janela: documentos 30 dias; manutenções 7 dias
        $docWarningUntil = date('Y-m-d', strtotime('+30 days'));
        $maintWarningUntil = date('Y-m-d', strtotime('+7 days'));

        // Agrupar e evitar duplicados
        $critical = [];
        $warning = [];
        $seen = [];

        // ==============================
        // Documentos - CRÍTICO (< hoje)
        // ==============================
        $expiredDocs = (new Query())
            ->from('documents')
            ->where(['company_id' => $companyId])
            ->andWhere(['<', 'expiry_date', $today])
            ->all();

        foreach ($expiredDocs as $doc) {
            $key = 'doc:' . $doc['id'] . ':critical';
            if (isset($seen[$key])) { continue; }
            $seen[$key] = true;

            $name = $doc['type'] ?? 'Documento';
            $dateStr = Yii::$app->formatter->asDate($doc['expiry_date']);
            $assoc = '';
            if (!empty($doc['vehicle_id'])) {
                $veh = Vehicle::findOne($doc['vehicle_id']);
                if ($veh) { $assoc = ' (' . $veh->license_plate . ')'; }
            }
            $critical[] = [
                'title' => 'Documento Expirado',
                'message' => $name . $assoc . " expirou em {$dateStr}",
                'action' => ['document/update', 'id' => $doc['id']],
            ];
        }

        // ==============================================
        // Documentos - ATENÇÃO (entre hoje e hoje + 30)
        // ==============================================
        $nearDocs = (new Query())
            ->from('documents')
            ->where(['company_id' => $companyId])
            ->andWhere(['between', 'expiry_date', $today, $docWarningUntil])
            ->all();

        foreach ($nearDocs as $doc) {
            $key = 'doc:' . $doc['id'] . ':warning';
            if (isset($seen[$key])) { continue; }
            $seen[$key] = true;

            $name = $doc['type'] ?? 'Documento';
            $dateStr = Yii::$app->formatter->asDate($doc['expiry_date']);
            $assoc = '';
            if (!empty($doc['vehicle_id'])) {
                $veh = Vehicle::findOne($doc['vehicle_id']);
                if ($veh) { $assoc = ' (' . $veh->license_plate . ')'; }
            }
            $warning[] = [
                'title' => 'Documento Próximo do Vencimento',
                'message' => $name . $assoc . " vence em {$dateStr}",
                'action' => ['document/update', 'id' => $doc['id']],
            ];
        }

        // =====================================
        // Manutenções - CRÍTICO (data < hoje)
        // =====================================
        $lateMaint = Maintenance::find()
            ->where([
                'company_id' => $companyId,
                'status' => 'scheduled',
            ])
            ->andWhere(['<', 'date', $today])
            ->all();

        foreach ($lateMaint as $m) {
            $key = 'maint:' . $m->id . ':critical';
            if (isset($seen[$key])) { continue; }
            $seen[$key] = true;

            $veh = $m->vehicle;
            $plate = $veh ? $veh->license_plate : null;
            $critical[] = [
                'title' => 'Manutenção Atrasada',
                'message' => $m->type . ($plate ? " do veículo {$plate}" : '') . ' está atrasada',
                'action' => ['maintenance/update', 'id' => $m->id],
            ];
        }

        // ============================================================
        // Manutenções - ATENÇÃO (hoje <= data <= hoje + 7) e scheduled
        // ============================================================
        $nearMaint = Maintenance::find()
            ->where([
                'company_id' => $companyId,
                'status' => 'scheduled',
            ])
            ->andWhere(['between', 'date', $today, $maintWarningUntil])
            ->all();

        foreach ($nearMaint as $m) {
            $key = 'maint:' . $m->id . ':warning';
            if (isset($seen[$key])) { continue; }
            $seen[$key] = true;

            $veh = $m->vehicle;
            $plate = $veh ? $veh->license_plate : null;
            $dateStr = Yii::$app->formatter->asDate($m->date);
            $warning[] = [
                'title' => 'Manutenção Próxima',
                'message' => $m->type . ($plate ? " do veículo {$plate}" : '') . " agendada para {$dateStr}",
                'action' => ['maintenance/update', 'id' => $m->id],
            ];
        }

        // ==============================
        // Cartas - CRÍTICO (expiradas)
        // ==============================
        $expiredLicenses = (new Query())
            ->from('users')
            ->where(['company_id' => $companyId])
            ->andWhere(['<', 'license_expiry', $today])
            ->andWhere(['not', ['license_expiry' => null]])
            ->all();

        foreach ($expiredLicenses as $driver) {
            $key = 'license:' . $driver['id'] . ':critical';
            if (isset($seen[$key])) { continue; }
            $seen[$key] = true;

            $dateStr = Yii::$app->formatter->asDate($driver['license_expiry']);
            $critical[] = [
                'title' => 'Carta de Condutor Expirada',
                'message' => $driver['name'] . ' - carta expirou em ' . $dateStr,
                'action' => ['driver/update', 'id' => $driver['id']],
            ];
        }

        // ====================================================
        // Cartas - ATENÇÃO (próximas 30 dias)
        // ====================================================
        $in30days = date('Y-m-d', strtotime('+30 days'));
        $nearLicenses = (new Query())
            ->from('users')
            ->where(['company_id' => $companyId])
            ->andWhere(['between', 'license_expiry', $today, $in30days])
            ->andWhere(['not', ['license_expiry' => null]])
            ->all();

        foreach ($nearLicenses as $driver) {
            $key = 'license:' . $driver['id'] . ':warning';
            if (isset($seen[$key])) { continue; }
            $seen[$key] = true;

            $dateStr = Yii::$app->formatter->asDate($driver['license_expiry']);
            $daysLeft = ceil((strtotime($driver['license_expiry']) - strtotime($today)) / 86400);
            $warning[] = [
                'title' => 'Carta de Condutor Próxima de Expirar',
                'message' => $driver['name'] . ' - vence em ' . $dateStr . ' (' . $daysLeft . 'd)',
                'action' => ['driver/update', 'id' => $driver['id']],
            ];
        }

        $counts = [
            'critical' => count($critical),
            'warning'  => count($warning),
        ];

        return $this->render('index', [
            'critical' => $critical,
            'warning' => $warning,
            'counts' => $counts,
        ]);
    }

    /**
     * Get notifications (only critical + near items for navbar badge)
     * Returns JSON for AJAX calls
     * Max 4 items
     */
    public function actionNotifications()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $companyId = Yii::$app->user->identity->company_id;
        $today = date('Y-m-d');
        $in5days = date('Y-m-d', strtotime('+5 days'));
        $notifications = [];
        $seen = [];

        // ==========================================
        // 1. Documentos EXPIRADOS (< hoje) - CRÍTICO
        // ==========================================
        $expiredDocs = (new Query())
            ->from('documents')
            ->where(['company_id' => $companyId])
            ->andWhere(['<', 'expiry_date', $today])
            ->all();

        foreach ($expiredDocs as $doc) {
            $key = 'doc:' . $doc['id'] . ':expired';
            if (isset($seen[$key])) { continue; }
            $seen[$key] = true;

            $name = $doc['type'] ?? 'Documento';
            $notifications[] = [
                'priority' => 1, // Crítico primeiro
                'type' => 'doc_expired',
                'title' => 'Documento Expirado',
                'message' => $name,
                'url' => \yii\helpers\Url::to(['document/view', 'id' => $doc['id']]),
            ];
        }

        // =================================================
        // 2. Manutenções ATRASADAS (data < hoje) - CRÍTICO
        // =================================================
        $lateMaint = Maintenance::find()
            ->where([
                'company_id' => $companyId,
                'status' => 'scheduled',
            ])
            ->andWhere(['<', 'date', $today])
            ->all();

        foreach ($lateMaint as $m) {
            $key = 'maint:' . $m->id . ':late';
            if (isset($seen[$key])) { continue; }
            $seen[$key] = true;

            $veh = $m->vehicle;
            $plate = $veh ? $veh->license_plate : '?';
            $notifications[] = [
                'priority' => 1, // Crítico
                'type' => 'maint_late',
                'title' => 'Manutenção Atrasada',
                'message' => $m->type . " - {$plate}",
                'url' => \yii\helpers\Url::to(['maintenance/view', 'id' => $m->id]),
            ];
        }

        // =========================================================
        // 3. Documentos PRÓXIMOS (vence nos próximos 5 dias)
        // =========================================================
        $nearDocs = (new Query())
            ->from('documents')
            ->where(['company_id' => $companyId])
            ->andWhere(['between', 'expiry_date', $today, $in5days])
            ->all();

        foreach ($nearDocs as $doc) {
            $key = 'doc:' . $doc['id'] . ':near';
            if (isset($seen[$key])) { continue; }
            $seen[$key] = true;

            $name = $doc['type'] ?? 'Documento';
            $daysLeft = ceil((strtotime($doc['expiry_date']) - strtotime($today)) / 86400);
            $notifications[] = [
                'priority' => 2, // Próximo
                'type' => 'doc_near',
                'title' => 'Documento a Vencer',
                'message' => $name . " ({$daysLeft}d)",
                'url' => \yii\helpers\Url::to(['document/view', 'id' => $doc['id']]),
            ];
        }

        // =======================================================
        // 4. Manutenções PRÓXIMAS (agendadas nos próximos 5 dias)
        // =======================================================
        $nearMaint = Maintenance::find()
            ->where([
                'company_id' => $companyId,
                'status' => 'scheduled',
            ])
            ->andWhere(['between', 'date', $today, $in5days])
            ->all();

        foreach ($nearMaint as $m) {
            $key = 'maint:' . $m->id . ':near';
            if (isset($seen[$key])) { continue; }
            $seen[$key] = true;

            $veh = $m->vehicle;
            $plate = $veh ? $veh->license_plate : '?';
            $daysLeft = ceil((strtotime($m->date) - strtotime($today)) / 86400);
            $notifications[] = [
                'priority' => 2, // Próximo
                'type' => 'maint_near',
                'title' => 'Manutenção Próxima',
                'message' => $m->type . " - {$plate} ({$daysLeft}d)",
                'url' => \yii\helpers\Url::to(['maintenance/view', 'id' => $m->id]),
            ];
        }

        // =======================================================
        // 5. CARTAS DE CONDUTORES - CRÍTICO (expiradas)
        // =======================================================
        $expiredLicenses = (new Query())
            ->from('users')
            ->where(['company_id' => $companyId])
            ->andWhere(['<', 'license_expiry', $today])
            ->andWhere(['not', ['license_expiry' => null]])
            ->all();

        foreach ($expiredLicenses as $driver) {
            $key = 'license:' . $driver['id'] . ':expired';
            if (isset($seen[$key])) { continue; }
            $seen[$key] = true;

            $dateStr = Yii::$app->formatter->asDate($driver['license_expiry']);
            $notifications[] = [
                'priority' => 0, // CRÍTICO
                'type' => 'license_expired',
                'title' => 'Carta Expirada',
                'message' => $driver['name'] . ' - Expirada em ' . $dateStr,
                'url' => \yii\helpers\Url::to(['driver/view', 'id' => $driver['id']]),
            ];
        }

        // =======================================================
        // 6. CARTAS DE CONDUTORES - PRÓXIMAS (próximos 30 dias)
        // =======================================================
        $in30days = date('Y-m-d', strtotime('+30 days'));
        $nearLicenses = (new Query())
            ->from('users')
            ->where(['company_id' => $companyId])
            ->andWhere(['between', 'license_expiry', $today, $in30days])
            ->andWhere(['not', ['license_expiry' => null]])
            ->all();

        foreach ($nearLicenses as $driver) {
            $key = 'license:' . $driver['id'] . ':near';
            if (isset($seen[$key])) { continue; }
            $seen[$key] = true;

            $daysLeft = ceil((strtotime($driver['license_expiry']) - strtotime($today)) / 86400);
            $notifications[] = [
                'priority' => 1, // AVISO
                'type' => 'license_near',
                'title' => 'Carta Próxima de Expirar',
                'message' => $driver['name'] . ' (' . $daysLeft . 'd)',
                'url' => \yii\helpers\Url::to(['driver/view', 'id' => $driver['id']]),
            ];
        }

        // Ordenar por prioridade (críticos primeiro) e depois por data
        usort($notifications, function($a, $b) {
            if ($a['priority'] !== $b['priority']) {
                return $a['priority'] - $b['priority'];
            }
            return 0;
        });

        // Limitar a 4 itens
        $notifications = array_slice($notifications, 0, 4);

        return [
            'count' => count($notifications),
            'items' => $notifications,
        ];
    }
}

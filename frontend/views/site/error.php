<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception */

use yii\helpers\Html;

$this->title = $name;

// Extract error code
$errorCode = 500;
if (isset($exception) && $exception instanceof \yii\web\HttpException) {
    $errorCode = $exception->statusCode;
} elseif (preg_match('/(\d{3})/', $name, $matches)) {
    $errorCode = (int)$matches[1];
}

$is403 = ($errorCode == 403);
$is404 = ($errorCode == 404);
$is500 = ($errorCode >= 500);
?>

<div class="error-container">
    <div class="logo">
        <img src="<?= Yii::getAlias('@web') ?>/images/veigest-logo.png" alt="VeiGest" onerror="this.style.display='none'">
        <span>VeiGest</span>
    </div>
    
    <?php if ($is403): ?>
        <div class="error-icon" style="font-size:80px;margin-bottom:20px;color:#e74c3c;">
            <i class="fas fa-shield-halved"></i>
        </div>
        <div class="error-code" style="font-size:72px;font-weight:700;color:#e74c3c;margin-bottom:10px;">403</div>
        <h1 class="error-title" style="font-size:24px;font-weight:600;color:#2c3e50;margin-bottom:15px;">Acesso Negado</h1>
    <?php elseif ($is404): ?>
        <div class="error-icon" style="font-size:80px;margin-bottom:20px;color:#f39c12;">
            <i class="fas fa-map-signs"></i>
        </div>
        <div class="error-code" style="font-size:72px;font-weight:700;color:#f39c12;margin-bottom:10px;">404</div>
        <h1 class="error-title" style="font-size:24px;font-weight:600;color:#2c3e50;margin-bottom:15px;">Página Não Encontrada</h1>
    <?php else: ?>
        <div class="error-icon" style="font-size:80px;margin-bottom:20px;color:#9b59b6;">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="error-code" style="font-size:72px;font-weight:700;color:#9b59b6;margin-bottom:10px;"><?= $errorCode ?></div>
        <h1 class="error-title" style="font-size:24px;font-weight:600;color:#2c3e50;margin-bottom:15px;"><?= Html::encode($name) ?></h1>
    <?php endif; ?>
    
    <p class="error-message" style="color:#7f8c8d;font-size:16px;margin-bottom:30px;line-height:1.6;">
        <?= nl2br(Html::encode($message)) ?>
    </p>
    
    <div class="btn-group" style="display:flex;gap:15px;justify-content:center;flex-wrap:wrap;">
        <?php if ($is403 && Yii::$app->user->identity && Yii::$app->user->identity->role === 'admin'): ?>
            <a href="<?= Yii::getAlias('@backendUrl') ?>" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:8px;font-weight:500;text-decoration:none;background:#09BC8A;color:white;">
                <i class="fas fa-cogs"></i> Ir para Administração
            </a>
        <?php else: ?>
            <a href="<?= Yii::$app->homeUrl ?>" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:8px;font-weight:500;text-decoration:none;background:#09BC8A;color:white;">
                <i class="fas fa-home"></i> Ir para Início
            </a>
        <?php endif; ?>
        <a href="javascript:history.back()" class="btn btn-secondary" style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:8px;font-weight:500;text-decoration:none;background:#ecf0f1;color:#2c3e50;">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
    
    <?php if (YII_ENV_DEV && $is500): ?>
    <div style="margin-top:30px;text-align:left;background:#f8f9fa;padding:15px;border-radius:8px;font-size:12px;">
        <strong>Debug Info (DEV only):</strong>
        <pre style="overflow-x:auto;white-space:pre-wrap;word-wrap:break-word;margin-top:10px;">
<?= Html::encode($exception->getTraceAsString()) ?>
        </pre>
    </div>
    <?php endif; ?>
</div>

<style>
    .logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 30px;
    }
    
    .logo img {
        width: 40px;
        height: 40px;
    }
    
    .logo span {
        font-size: 24px;
        font-weight: 700;
        color: #09BC8A;
    }
    
    .error-container {
        text-align: center;
        padding: 40px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        max-width: 500px;
        margin: 20px auto;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
</style>
<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception*/

use yii\helpers\Html;

$this->title = $name;

// Extract error code from exception or name
$errorCode = 403;
if (isset($exception) && $exception instanceof \yii\web\HttpException) {
    $errorCode = $exception->statusCode;
} elseif (preg_match('/(\d{3})/', $name, $matches)) {
    $errorCode = (int)$matches[1];
}

// Determine error type for styling
$is403 = ($errorCode == 403);
$is404 = ($errorCode == 404);
$is500 = ($errorCode >= 500);
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->title) ?> - VeiGest</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        
        .error-container {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 20px;
        }
        
        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .error-icon.forbidden { color: #e74c3c; }
        .error-icon.not-found { color: #f39c12; }
        .error-icon.server-error { color: #9b59b6; }
        
        .error-code {
            font-size: 72px;
            font-weight: 700;
            margin-bottom: 10px;
            line-height: 1;
        }
        
        .error-code.forbidden { color: #e74c3c; }
        .error-code.not-found { color: #f39c12; }
        .error-code.server-error { color: #9b59b6; }
        
        .error-title {
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .error-message {
            color: #7f8c8d;
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #09BC8A;
            color: white;
        }
        
        .btn-primary:hover {
            background: #078a68;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #ecf0f1;
            color: #2c3e50;
        }
        
        .btn-secondary:hover {
            background: #d5dbdb;
            transform: translateY(-2px);
        }
        
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
    </style>
</head>
<body>
    <div class="error-container">
        <div class="logo">
            <img src="<?= Yii::getAlias('@web') ?>/images/veigest-logo.png" alt="VeiGest" onerror="this.style.display='none'">
            <span>VeiGest</span>
        </div>
        
        <?php if ($is403): ?>
            <div class="error-icon forbidden">
                <i class="fas fa-shield-halved"></i>
            </div>
            <div class="error-code forbidden">403</div>
            <h1 class="error-title">Acesso Negado</h1>
        <?php elseif ($is404): ?>
            <div class="error-icon not-found">
                <i class="fas fa-map-signs"></i>
            </div>
            <div class="error-code not-found">404</div>
            <h1 class="error-title">Página Não Encontrada</h1>
        <?php else: ?>
            <div class="error-icon server-error">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="error-code server-error"><?= $errorCode ?></div>
            <h1 class="error-title"><?= Html::encode($name) ?></h1>
        <?php endif; ?>
        
        <p class="error-message">
            <?= nl2br(Html::encode($message)) ?>
        </p>
        
        <div class="btn-group">
            <a href="<?= Yii::getAlias('@frontendUrl') ?>" class="btn btn-primary">
                <i class="fas fa-home"></i> Ir para Início
            </a>
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</body>
</html>

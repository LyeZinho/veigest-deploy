<?php
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');

// Detect if running on localhost or production domain
$serverHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$isLocalhost = strpos($serverHost, 'localhost') !== false || strpos($serverHost, '127.0.0.1') !== false;

if ($isLocalhost) {
    Yii::setAlias('@backendUrl', 'http://localhost/android-app/Website/website-VeiGest/veigest/backend/web');
    Yii::setAlias('@frontendUrl', 'http://localhost/android-app/Website/website-VeiGest/veigest/frontend/web');
} else {
    // Use HTTP for dryadlang.org subdomains (change to https when SSL is configured)
    Yii::setAlias('@backendUrl', 'http://veigestback.dryadlang.org');
    Yii::setAlias('@frontendUrl', 'http://veigestfront.dryadlang.org');
}


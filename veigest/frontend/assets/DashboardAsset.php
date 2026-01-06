<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Dashboard asset bundle
 */
class DashboardAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        // CSS files will be included in the layout
    ];
    public $js = [];
    public $depends = [
        'yii\web\YiiAsset',  // This automatically loads yii.js
        'yii\bootstrap5\BootstrapAsset',
    ];
}
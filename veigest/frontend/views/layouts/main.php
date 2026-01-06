<?php

/** @var \yii\web\View $this */
/** @var string $content */

use common\widgets\Alert;
use frontend\assets\AppAsset;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --color-primary: #09BC8A;
            --color-onyx: #3C3C3C;
            --color-turquoise: #75DDDD;
            --color-lavender-gray: #C8BFC7;
            --color-lavender-blush: #FFEAEE;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .text-primary { color: #09BC8A !important; }
 
        .bg-primary { background-color: #09BC8A !important; }

        .border-primary { border-color: #09BC8A !important; }
        
        .hero-gradient {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-turquoise) 100%);
        }
        
        .card-shadow {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary {
            background-color: var(--color-primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0a9a71;
        }
        
        .service-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(9, 188, 138, 0.15);
        }
    </style>
</head>

<body class="d-flex flex-column h-100">
    <?php include '_navbar.php'; ?>

    <?php $this->beginBody() ?>

    <main>
        <?= \common\widgets\Alert::widget() ?>
        <?= $content ?>
    </main>

    <?php $this->endBody() ?>

    <?php include '_footer.php'; ?>
</body>

</html>
<?php $this->endPage(); ?>
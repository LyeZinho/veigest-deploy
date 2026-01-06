<?php

namespace frontend\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * Maintenance Fixture
 */
class MaintenanceFixture extends ActiveFixture
{
    public $modelClass = 'common\models\Maintenance';
    public $dataFile = '@frontend/tests/_data/maintenance.php';
    public $depends = [
        'frontend\tests\fixtures\CompanyFixture',
        'frontend\tests\fixtures\VehicleFixture',
    ];
}

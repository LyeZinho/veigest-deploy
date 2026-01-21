<?php

namespace frontend\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * FuelLog Fixture
 */
class FuelLogFixture extends ActiveFixture
{
    public $modelClass = 'common\models\FuelLog';
    public $dataFile = '@frontend/tests/_data/fuel_log.php';
    public $depends = [
        'frontend\tests\fixtures\CompanyFixture',
        'frontend\tests\fixtures\VehicleFixture',
        'frontend\tests\fixtures\UserFixture',
    ];
}

<?php

namespace frontend\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * Vehicle Fixture
 */
class VehicleFixture extends ActiveFixture
{
    public $modelClass = 'common\models\Vehicle';
    public $dataFile = '@frontend/tests/_data/vehicle.php';
    public $depends = [
        'frontend\tests\fixtures\CompanyFixture',
        'frontend\tests\fixtures\UserFixture',
    ];
}

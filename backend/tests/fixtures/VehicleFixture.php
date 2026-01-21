<?php

namespace backend\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * Vehicle fixture
 */
class VehicleFixture extends ActiveFixture
{
    public $modelClass = 'common\models\Vehicle';
    public $dataFile = '@backend/tests/_data/vehicle.php';
    public $depends = [
        'backend\tests\fixtures\CompanyFixture',
    ];
}

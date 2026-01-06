<?php

namespace backend\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * User fixture
 */
class UserFixture extends ActiveFixture
{
    public $modelClass = 'common\models\User';
    public $dataFile = '@backend/tests/_data/user.php';
    public $depends = [
        'backend\tests\fixtures\CompanyFixture',
    ];
}

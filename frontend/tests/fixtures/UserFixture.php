<?php

namespace frontend\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * User Fixture
 */
class UserFixture extends ActiveFixture
{
    public $modelClass = 'common\models\User';
    public $dataFile = '@frontend/tests/_data/user.php';
    public $depends = [
        'frontend\tests\fixtures\CompanyFixture',
    ];
}

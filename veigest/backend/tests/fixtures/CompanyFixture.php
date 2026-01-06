<?php

namespace backend\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * Company fixture
 */
class CompanyFixture extends ActiveFixture
{
    public $modelClass = 'common\models\Company';
    public $dataFile = '@backend/tests/_data/company.php';
}

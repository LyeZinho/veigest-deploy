<?php

namespace frontend\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * Company Fixture
 */
class CompanyFixture extends ActiveFixture
{
    public $modelClass = 'common\models\Company';
    public $dataFile = '@frontend/tests/_data/company.php';
}

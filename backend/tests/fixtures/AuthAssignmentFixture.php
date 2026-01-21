<?php

namespace backend\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * Auth Assignment fixture - RBAC user roles
 */
class AuthAssignmentFixture extends ActiveFixture
{
    public $tableName = '{{%auth_assignment}}';
    public $dataFile = '@backend/tests/_data/auth_assignment.php';
    public $depends = [
        'backend\tests\fixtures\UserFixture',
    ];
}

<?php

use yii\db\Migration;

class m260107_175207_assign_roles_to_existing_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260107_175207_assign_roles_to_existing_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260107_175207_assign_roles_to_existing_users cannot be reverted.\n";

        return false;
    }
    */
}

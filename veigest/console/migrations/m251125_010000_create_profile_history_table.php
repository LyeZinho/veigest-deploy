<?php

use yii\db\Migration;

/**
 * Migration para criar tabela de histórico de alterações de perfil.
 * 
 * RF-FO-003.5: Histórico de alterações
 */
class m251125_010000_create_profile_history_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%profile_history}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'field_name' => $this->string(50)->notNull()->comment('Campo alterado'),
            'old_value' => $this->text()->comment('Valor anterior'),
            'new_value' => $this->text()->comment('Novo valor'),
            'change_type' => "ENUM('update','password','photo') NOT NULL DEFAULT 'update'",
            'ip_address' => $this->string(45)->comment('IP do utilizador'),
            'user_agent' => $this->string(255)->comment('Browser/dispositivo'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_profile_history_user', '{{%profile_history}}', 'user_id');
        $this->createIndex('idx_profile_history_type', '{{%profile_history}}', 'change_type');
        $this->createIndex('idx_profile_history_date', '{{%profile_history}}', 'created_at');
        
        $this->addForeignKey(
            'fk_profile_history_user',
            '{{%profile_history}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_profile_history_user', '{{%profile_history}}');
        $this->dropTable('{{%profile_history}}');
    }
}

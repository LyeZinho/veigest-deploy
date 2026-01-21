<?php

use yii\db\Migration;

/**
 * Migration para adicionar coluna status à tabela maintenances.
 * 
 * Valores possíveis:
 * - scheduled: manutenção agendada para o futuro
 * - completed: manutenção concluída
 * - overdue: manutenção atrasada (pode ser atualizado via trigger/cron)
 */
class m251125_000000_add_status_to_maintenances extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Adiciona coluna status
        $this->addColumn('{{%maintenances}}', 'status', "ENUM('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled' AFTER workshop");
        
        // Cria índice para melhor performance nas queries
        $this->createIndex('idx_maintenances_status', '{{%maintenances}}', 'status');
        
        // Atualiza registros existentes baseado na data
        // Se a data já passou, assume-se que foi concluída
        $today = date('Y-m-d');
        $this->update('{{%maintenances}}', ['status' => 'completed'], ['<', 'date', $today]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_maintenances_status', '{{%maintenances}}');
        $this->dropColumn('{{%maintenances}}', 'status');
    }
}

<?php

use yii\db\Migration;

/**
 * Fix Manager Role Permissions - Bug Fix #9
 * 
 * Problem: Manager role was missing critical permissions for maintenances, documents, and fuel operations
 * This caused 403 Forbidden errors when managers tried to access these features.
 * 
 * Solution: Add missing CRUD permissions for:
 * - maintenances.* (view, create, update, delete, schedule)
 * - documents.* (view, create, update, delete)
 * - fuel.* (create, delete - already had view and update)
 * - alerts.create (was missing)
 * 
 * @see /home/pedro/facul/website-VeiGest/relatorios/relatorio-melhorias-dashboard-frota-2025.md
 */
class m260105_130154_fix_manager_permissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Check if permissions already exist (from updated consolidated migration)
        $existingPerms = (new \yii\db\Query())
            ->from('{{%auth_item_child}}')
            ->where(['parent' => 'manager', 'child' => 'maintenances.view'])
            ->exists();
        
        if ($existingPerms) {
            echo "ℹ️  Manager permissions already exist (from consolidated migration)\n";
            echo "   Skipping duplicate insertion to avoid PRIMARY KEY conflict.\n";
            echo "   This is expected behavior for fresh installations after 05/01/2026.\n";
            return true;
        }

        // Original code - only executes if permissions don't exist yet
        // This path is for databases created before the consolidated migration was updated
        
        // Add missing maintenance permissions to manager role
        $this->batchInsert('{{%auth_item_child}}', ['parent', 'child'], [
            ['manager', 'maintenances.view'],
            ['manager', 'maintenances.create'],
            ['manager', 'maintenances.update'],
            ['manager', 'maintenances.delete'],
            ['manager', 'maintenances.schedule'],
        ]);

        // Add missing document permissions to manager role
        $this->batchInsert('{{%auth_item_child}}', ['parent', 'child'], [
            ['manager', 'documents.view'],
            ['manager', 'documents.create'],
            ['manager', 'documents.update'],
            ['manager', 'documents.delete'],
        ]);

        // Add missing fuel permissions to manager role (create and delete)
        $this->batchInsert('{{%auth_item_child}}', ['parent', 'child'], [
            ['manager', 'fuel.create'],
            ['manager', 'fuel.delete'],
        ]);

        // Add missing alerts create permission to manager role
        $this->insert('{{%auth_item_child}}', [
            'parent' => 'manager',
            'child' => 'alerts.create',
        ]);

        echo "✅ Manager permissions fixed successfully!\n";
        echo "   - Added 5 maintenance permissions\n";
        echo "   - Added 4 document permissions\n";
        echo "   - Added 2 fuel permissions\n";
        echo "   - Added 1 alert permission\n";
        echo "   Total: 12 new permissions added to manager role\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Remove maintenance permissions from manager
        $this->delete('{{%auth_item_child}}', [
            'parent' => 'manager',
            'child' => ['maintenances.view', 'maintenances.create', 'maintenances.update', 'maintenances.delete', 'maintenances.schedule']
        ]);

        // Remove document permissions from manager
        $this->delete('{{%auth_item_child}}', [
            'parent' => 'manager',
            'child' => ['documents.view', 'documents.create', 'documents.update', 'documents.delete']
        ]);

        // Remove fuel permissions from manager
        $this->delete('{{%auth_item_child}}', [
            'parent' => 'manager',
            'child' => ['fuel.create', 'fuel.delete']
        ]);

        // Remove alerts create permission from manager
        $this->delete('{{%auth_item_child}}', [
            'parent' => 'manager',
            'child' => 'alerts.create'
        ]);

        echo "✅ Manager permissions reverted successfully!\n";

        return true;
    }
}

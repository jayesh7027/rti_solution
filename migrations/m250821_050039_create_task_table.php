<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task}}`.
 */
class m250821_050039_create_task_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'description' => $this->text(),
            'status' => "ENUM('pending','in_progress','completed') NOT NULL DEFAULT 'pending'",
            'priority' => "ENUM('low','medium','high') NOT NULL DEFAULT 'medium'",
            'due_date' => $this->date()->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'deleted_at' => $this->integer()->null(), // soft delete (bonus)
        ]);
        $this->createIndex('idx_task_status', '{{%task}}', 'status');
        $this->createIndex('idx_task_priority', '{{%task}}', 'priority');
        $this->createIndex('idx_task_due_date', '{{%task}}', 'due_date');
        $this->createIndex('idx_task_deleted_at', '{{%task}}', 'deleted_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%task}}');
    }
}

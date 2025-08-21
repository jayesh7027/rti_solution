<?php
use yii\db\Migration;

class m250821_070000_create_task_log_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('task_log', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull(),
            'action' => $this->string(20)->notNull(), // create, update, delete
            'data' => $this->text(), // JSON snapshot
            'created_at' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey('fk_task_log_task', 'task_log', 'task_id', 'task', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_task_log_task', 'task_log');
        $this->dropTable('task_log');
    }
}

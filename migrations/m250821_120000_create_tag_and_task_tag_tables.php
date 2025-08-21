<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tag}}` and `{{%task_tag}}` for many-to-many relation.
 */
class m250821_120000_create_tag_and_task_tag_tables extends Migration
{
    public function safeUp()
    {
        // Create tag table
        $this->createTable('{{%tag}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(64)->notNull()->unique(),
        ]);

        // Create task_tag junction table
        $this->createTable('{{%task_tag}}', [
            'task_id' => $this->integer()->notNull(),
            'tag_id' => $this->integer()->notNull(),
            'PRIMARY KEY(task_id, tag_id)',
        ]);

        // Add foreign keys
        $this->addForeignKey('fk-task_tag-task_id', '{{%task_tag}}', 'task_id', '{{%task}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-task_tag-tag_id', '{{%task_tag}}', 'tag_id', '{{%tag}}', 'id', 'CASCADE', 'CASCADE');

        // Insert 20 dummy tags
        $tags = [
            'Urgent', 'Important', 'Low Priority', 'Bug', 'Feature', 'Enhancement', 'Research', 'Testing', 'Documentation', 'Meeting',
            'Review', 'Blocked', 'In Progress', 'Completed', 'Backlog', 'Design', 'Frontend', 'Backend', 'DevOps', 'QA'
        ];
        foreach ($tags as $tag) {
            $this->insert('{{%tag}}', ['name' => $tag]);
        }
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-task_tag-task_id', '{{%task_tag}}');
        $this->dropForeignKey('fk-task_tag-tag_id', '{{%task_tag}}');
        $this->dropTable('{{%task_tag}}');
        $this->dropTable('{{%tag}}');
    }
}

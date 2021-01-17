<?php

use yii\db\Migration;

/**
 * Class m210115_140922_project
 */
class m210115_140922_project extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('project', [
            'id' => $this->primaryKey(),
            'name' => $this->string(128)->notNull(),
            'owner_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('idx-project-owner_id-updated_at', 'project', 'owner_id, updated_at');
        $this->addForeignKey('fk-project-owner', 'project', 'owner_id', 'user', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('source', [
            'id' => $this->primaryKey(),
            'project_id' => $this->integer()->notNull(),
            'title' => $this->string(128),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'locked_until' => $this->integer()->notNull()->defaultValue(0),
            'priority' => $this->integer()->notNull(),
            'status' => $this->integer()->notNull(),
            'url' => $this->string(255)->notNull(),
        ]);
        $this->createIndex('idx-source-project_id-priority', 'source', 'project_id, priority');
        $this->addForeignKey('fk-source-project', 'source', 'project_id', 'project', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('source_paragraph', [
            'id' => $this->primaryKey(),
            'source_id' => $this->integer()->notNull(),
            'priority' => $this->integer()->notNull(),
            'content' => $this->text(),
        ]);
        $this->createIndex('idx-source_paragraph-source_id-priority', 'source_paragraph', 'source_id, priority');
        $this->addForeignKey('fk-source-source_paragraph', 'source_paragraph', 'source_id', 'source', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('source_paragraph');
        $this->dropTable('source');
        $this->dropTable('project');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210115_140922_project cannot be reverted.\n";

        return false;
    }
    */
}

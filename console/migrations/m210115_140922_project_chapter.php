<?php

use yii\db\Migration;

/**
 * Class m210115_140922_project
 */
class m210115_140922_project_chapter extends Migration
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
            'word_count' => $this->integer()->notNull()->defaultValue(0),
            'edit_count' => $this->integer()->notNull()->defaultValue(0),
        ]);
        $this->createIndex('idx-project-owner_id-updated_at', 'project', 'owner_id, updated_at');
        $this->addForeignKey('fk-project-owner', 'project', 'owner_id', 'user', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('chapter', [
            'id' => $this->primaryKey(),
            'project_id' => $this->integer()->notNull(),
            'title' => $this->string(128),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'locked_until' => $this->integer()->notNull()->defaultValue(0),
            'priority' => $this->integer()->notNull(),
            'status' => $this->integer()->notNull(),
            'url' => $this->string(255)->notNull(),
            'word_count' => $this->integer()->notNull()->defaultValue(0),
            'edit_count' => $this->integer()->notNull()->defaultValue(0),
            'error_message' => $this->text(),
        ]);
        $this->createIndex('idx-chapter-project_id-priority', 'chapter', 'project_id, priority');
        $this->addForeignKey('fk-chapter-project', 'chapter', 'project_id', 'project', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('chapter_paragraph', [
            'id' => $this->primaryKey(),
            'chapter_id' => $this->integer()->notNull(),
            'priority' => $this->integer()->notNull(),
            'content' => $this->text(),
        ]);
        $this->createIndex('idx-chapter_paragraph-chapter_id-priority', 'chapter_paragraph', 'chapter_id, priority');
        $this->addForeignKey('fk-chapter-chapter_paragraph', 'chapter_paragraph', 'chapter_id', 'chapter', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('project_access_token', [
            'id' => $this->primaryKey(),
            'project_id' => $this->integer()->notNull()->unique(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'token' => $this->text(),
        ]);
        $this->addForeignKey('fk-project_access_token-project', 'project_access_token', 'project_id',
            'project', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('project_access_token');
        $this->dropTable('chapter_paragraph');
        $this->dropTable('chapter');
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

<?php

use yii\db\Migration;

/**
 * Class m210126_114908_source_to_chapter
 */
class m210126_114908_source_to_chapter extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameTable('source', 'chapter');
        $this->renameColumn('source_paragraph', 'source_id', 'chapter_id');
        $this->renameTable('source_paragraph', 'chapter_paragraph');
        $this->renameColumn('export', 'source_id', 'chapter_id');
        $this->renameTable('export', 'chapter_export');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('chapter_export', 'chapter_id', 'source_id');
        $this->renameTable('chapter_export', 'export');
        $this->renameColumn('chapter_paragraph', 'chapter_id', 'source_id');
        $this->renameTable('chapter_paragraph', 'source_paragraph');
        $this->renameTable('chapter', 'source');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210126_114908_source_to_chapter cannot be reverted.\n";

        return false;
    }
    */
}

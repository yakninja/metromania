<?php

use yii\db\Migration;

/**
 * Class m210129_140011_chapter_warning
 */
class m210129_140011_chapter_warning extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('chapter', 'warning_message', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('chapter', 'warning_message');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210129_140011_chapter_warning cannot be reverted.\n";

        return false;
    }
    */
}

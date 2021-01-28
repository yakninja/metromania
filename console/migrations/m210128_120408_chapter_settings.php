<?php

use yii\db\Migration;

/**
 * Class m210128_120408_chapter_settings
 */
class m210128_120408_chapter_settings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('chapter', 'ignore_gray_text', $this->boolean()->notNull()->defaultValue(true));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('chapter', 'ignore_gray_text');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210128_120408_chapter_settings cannot be reverted.\n";

        return false;
    }
    */
}

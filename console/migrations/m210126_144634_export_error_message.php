<?php

use yii\db\Migration;

/**
 * Class m210126_144634_export_error_message
 */
class m210126_144634_export_error_message extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('chapter_export', 'error_message', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('chapter_export', 'error_message');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210126_144634_export_error_message cannot be reverted.\n";

        return false;
    }
    */
}

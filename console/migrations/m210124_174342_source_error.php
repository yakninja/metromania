<?php

use yii\db\Migration;

/**
 * Class m210124_174342_source_error
 */
class m210124_174342_source_error extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('source', 'error_message', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('source', 'error_message');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210124_174342_source_error cannot be reverted.\n";

        return false;
    }
    */
}

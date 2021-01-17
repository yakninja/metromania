<?php

use yii\db\Migration;

/**
 * Class m210115_140812_session
 */
class m210115_140812_session extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE TABLE session
            (
                id CHAR(40) NOT NULL PRIMARY KEY,
                expire INTEGER,
                data LONGBLOB
            )');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('session');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210115_141112_session cannot be reverted.\n";

        return false;
    }
    */
}

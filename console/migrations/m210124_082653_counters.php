<?php

use yii\db\Migration;

/**
 * Class m210124_082653_counters
 */
class m210124_082653_counters extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('project', 'edit_count', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn('source', 'edit_count', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn('project', 'word_count', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn('source', 'word_count', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('source', 'word_count');
        $this->dropColumn('project', 'word_count');
        $this->dropColumn('source', 'edit_count');
        $this->dropColumn('project', 'edit_count');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210124_082653_counters cannot be reverted.\n";

        return false;
    }
    */
}

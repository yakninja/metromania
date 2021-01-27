<?php

use yii\db\Migration;

/**
 * Class m210127_081649_chapter_hash
 */
class m210127_081649_chapter_hash extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('chapter', 'hash', $this->string(40)->null());
        $this->addColumn('chapter', 'content_updated_at', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('chapter', 'content_updated_at');
        $this->dropColumn('chapter', 'hash');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210127_081649_chapter_hash cannot be reverted.\n";

        return false;
    }
    */
}

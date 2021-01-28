<?php

use yii\db\Migration;

/**
 * Class m210128_083113_chapter_publication_hash
 */
class m210128_083113_chapter_publication_hash extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('chapter_publication', 'hash', $this->string(40)->null());
        $this->addColumn('chapter_publication', 'published_at', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('chapter_publication', 'hash');
        $this->dropColumn('chapter_publication', 'published_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210128_083113_chapter_publication_hash cannot be reverted.\n";

        return false;
    }
    */
}

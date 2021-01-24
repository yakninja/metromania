<?php

use yii\db\Migration;

/**
 * Class m210122_145707_project_access_token
 */
class m210122_145707_project_access_token extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
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
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210122_145707_project_access_token cannot be reverted.\n";

        return false;
    }
    */
}

<?php

use yii\db\Migration;

/**
 * Class m210123_204047_destination_table
 */
class m210123_204047_destination_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('destination_provider', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'url' => $this->string(255)->null(),
            'api_class' => $this->string(255)->notNull(),
        ]);
        $this->insert('destination_provider', [
            'name' => 'Ficbook',
            'url' => 'https://ficbook.net',
            'api_class' => 'common\apis\Ficbook',
        ]);

        $this->createTable('project_destination_settings', [
            'id' => $this->primaryKey(),
            'project_id' => $this->integer()->notNull(),
            'provider_id' => $this->integer()->notNull(),
            'username' => $this->string(128)->null(),
            'password' => $this->string(128)->null(),
        ]);
        $this->addForeignKey('fk-project_destination_settings-project', 'project_destination_settings', 'project_id',
            'project', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-project_destination_settings-destination_provider', 'project_destination_settings', 'provider_id',
            'destination_provider', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('uq-project_destination_settings', 'project_destination_settings',
            'project_id, provider_id', true);

        $this->createTable('destination', [
            'id' => $this->primaryKey(),
            'source_id' => $this->integer()->notNull(),
            'provider_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'locked_until' => $this->integer()->notNull()->defaultValue(0),
            'status' => $this->integer()->notNull(),
            'url' => $this->string(255)->notNull(),
        ]);
        $this->addForeignKey('fk-destination-destination_provider', 'destination', 'provider_id',
            'destination_provider', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-destination-source', 'destination', 'source_id',
            'source', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('uq-destination', 'destination',
            'source_id, provider_id', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('destination');
        $this->dropTable('project_destination_settings');
        $this->dropTable('destination_provider');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210123_204047_destination_table cannot be reverted.\n";

        return false;
    }
    */
}

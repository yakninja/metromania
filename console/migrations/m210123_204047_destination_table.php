<?php

use yii\db\Migration;

/**
 * Class m210123_204047_export_table
 */
class m210123_204047_export_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('export_provider', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'url' => $this->string(255)->null(),
            'api_class' => $this->string(255)->notNull(),
        ]);
        $this->insert('export_provider', [
            'name' => 'Ficbook',
            'url' => 'https://ficbook.net',
            'api_class' => 'common\apis\Ficbook',
        ]);

        $this->createTable('project_export_settings', [
            'id' => $this->primaryKey(),
            'project_id' => $this->integer()->notNull(),
            'provider_id' => $this->integer()->notNull(),
            'username' => $this->string(128)->null(),
            'password' => $this->string(128)->null(),
        ]);
        $this->addForeignKey('fk-project_export_settings-project', 'project_export_settings', 'project_id',
            'project', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-project_export_settings-export_provider', 'project_export_settings', 'provider_id',
            'export_provider', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('uq-project_export_settings', 'project_export_settings',
            'project_id, provider_id', true);

        $this->createTable('export', [
            'id' => $this->primaryKey(),
            'source_id' => $this->integer()->notNull(),
            'provider_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'locked_until' => $this->integer()->notNull()->defaultValue(0),
            'status' => $this->integer()->notNull(),
            'url' => $this->string(255)->notNull(),
        ]);
        $this->addForeignKey('fk-export-export_provider', 'export', 'provider_id',
            'export_provider', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-export-source', 'export', 'source_id',
            'source', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('uq-export', 'export',
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
        echo "m210123_204047_export_table cannot be reverted.\n";

        return false;
    }
    */
}

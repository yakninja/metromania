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

        $this->createTable('chapter_export', [
            'id' => $this->primaryKey(),
            'chapter_id' => $this->integer()->notNull(),
            'provider_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'locked_until' => $this->integer()->notNull()->defaultValue(0),
            'status' => $this->integer()->notNull(),
            'url' => $this->string(255)->notNull(),
            'error_message' => $this->text(),
        ]);
        $this->addForeignKey('fk-chapter_export-export_provider', 'chapter_export', 'provider_id',
            'export_provider', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-chapter_export-chapter', 'chapter_export', 'chapter_id',
            'chapter', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('uq-chapter_export', 'chapter_export',
            'chapter_id, provider_id', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('chapter_export');
        $this->dropTable('project_export_settings');
        $this->dropTable('export_provider');
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

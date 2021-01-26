<?php

use yii\db\Migration;

/**
 * Class m210123_204047_publication_tables
 */
class m210123_204047_publication_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('publication_provider', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'url' => $this->string(255)->null(),
            'api_class' => $this->string(255)->notNull(),
        ]);
        $this->insert('publication_provider', [
            'name' => 'Ficbook',
            'url' => 'https://ficbook.net',
            'api_class' => 'common\apis\Ficbook',
        ]);

        $this->createTable('project_publication_settings', [
            'id' => $this->primaryKey(),
            'project_id' => $this->integer()->notNull(),
            'provider_id' => $this->integer()->notNull(),
            'username' => $this->string(128)->null(),
            'password' => $this->string(128)->null(),
        ]);
        $this->addForeignKey('fk-project_publication_settings-project', 'project_publication_settings', 'project_id',
            'project', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-project_publication_settings-publication_provider', 'project_publication_settings', 'provider_id',
            'publication_provider', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('uq-project_publication_settings', 'project_publication_settings',
            'project_id, provider_id', true);

        $this->createTable('chapter_publication', [
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
        $this->addForeignKey('fk-chapter_publication-publication_provider', 'chapter_publication', 'provider_id',
            'publication_provider', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-chapter_publication-chapter', 'chapter_publication', 'chapter_id',
            'chapter', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('uq-chapter_publication', 'chapter_publication',
            'chapter_id, provider_id', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('chapter_publication');
        $this->dropTable('project_publication_settings');
        $this->dropTable('publication_provider');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210123_204047_publication_table cannot be reverted.\n";

        return false;
    }
    */
}

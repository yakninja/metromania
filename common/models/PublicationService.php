<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "publication_service".
 *
 * @property int $id
 * @property string $name
 * @property string|null $url
 * @property string $api_class
 *
 * @property ChapterPublication[] $publications
 * @property Chapter[] $chapters
 * @property ProjectPublicationSettings[] $projectPublicationSettings
 * @property Project[] $projects
 */
class PublicationService extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'publication_service';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'api_class'], 'required'],
            [['name', 'url', 'api_class'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'url' => Yii::t('app', 'Url'),
            'api_class' => Yii::t('app', 'Api Class'),
        ];
    }

    /**
     * Gets query for [[Publications]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPublications()
    {
        return $this->hasMany(ChapterPublication::class, ['service_id' => 'id']);
    }

    /**
     * Gets query for [[Chapters]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChapters()
    {
        return $this->hasMany(Chapter::class, ['id' => 'chapter_id'])
            ->viaTable('publication', ['service_id' => 'id']);
    }

    /**
     * Gets query for [[ProjectPublicationSettings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjectPublicationSettings()
    {
        return $this->hasMany(ProjectPublicationSettings::class, ['service_id' => 'id']);
    }

    /**
     * Gets query for [[Projects]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjects()
    {
        return $this->hasMany(Project::class, ['id' => 'project_id'])
            ->viaTable('project_publication_settings', ['service_id' => 'id']);
    }
}

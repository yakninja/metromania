<?php

namespace common\models\project;

use Yii;

/**
 * This is the model class for table "export_provider".
 *
 * @property int $id
 * @property string $name
 * @property string|null $url
 * @property string $api_class
 *
 * @property ChapterExport[] $exports
 * @property Chapter[] $chapters
 * @property ProjectExportSettings[] $projectExportSettings
 * @property Project[] $projects
 */
class ExportProvider extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'export_provider';
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
     * Gets query for [[Exports]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExports()
    {
        return $this->hasMany(ChapterExport::class, ['provider_id' => 'id']);
    }

    /**
     * Gets query for [[Chapters]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChapters()
    {
        return $this->hasMany(Chapter::class, ['id' => 'chapter_id'])
            ->viaTable('export', ['provider_id' => 'id']);
    }

    /**
     * Gets query for [[ProjectExportSettings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjectExportSettings()
    {
        return $this->hasMany(ProjectExportSettings::class, ['provider_id' => 'id']);
    }

    /**
     * Gets query for [[Projects]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjects()
    {
        return $this->hasMany(Project::class, ['id' => 'project_id'])
            ->viaTable('project_export_settings', ['provider_id' => 'id']);
    }
}

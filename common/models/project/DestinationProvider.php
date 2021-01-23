<?php

namespace common\models\project;

use Yii;

/**
 * This is the model class for table "destination_provider".
 *
 * @property int $id
 * @property string $name
 * @property string|null $url
 * @property string $api_class
 *
 * @property Destination[] $destinations
 * @property Source[] $sources
 * @property ProjectDestinationSettings[] $projectDestinationSettings
 * @property Project[] $projects
 */
class DestinationProvider extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'destination_provider';
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
     * Gets query for [[Destinations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDestinations()
    {
        return $this->hasMany(Destination::class, ['provider_id' => 'id']);
    }

    /**
     * Gets query for [[Sources]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSources()
    {
        return $this->hasMany(Source::class, ['id' => 'source_id'])->viaTable('destination', ['provider_id' => 'id']);
    }

    /**
     * Gets query for [[ProjectDestinationSettings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjectDestinationSettings()
    {
        return $this->hasMany(ProjectDestinationSettings::class, ['provider_id' => 'id']);
    }

    /**
     * Gets query for [[Projects]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjects()
    {
        return $this->hasMany(Project::class, ['id' => 'project_id'])->viaTable('project_destination_settings', ['provider_id' => 'id']);
    }
}

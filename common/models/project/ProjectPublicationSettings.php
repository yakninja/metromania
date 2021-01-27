<?php

namespace common\models\project;

use Yii;

/**
 * This is the model class for table "project_publication_settings".
 *
 * @property int $id
 * @property int $project_id
 * @property int $service_id
 * @property string|null $username
 * @property string|null $password
 *
 * @property PublicationService $provider
 * @property Project $project
 */
class ProjectPublicationSettings extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project_publication_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['!project_id', 'service_id'], 'required'],
            [['project_id', 'service_id'], 'integer'],
            [['username', 'password'], 'string', 'max' => 128],
            [['project_id', 'service_id'], 'unique', 'targetAttribute' => ['project_id', 'service_id']],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => PublicationService::class, 'targetAttribute' => ['service_id' => 'id']],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::class, 'targetAttribute' => ['project_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'project_id' => Yii::t('app', 'Project ID'),
            'service_id' => Yii::t('app', 'Service ID'),
            'username' => Yii::t('app', 'Username'),
            'password' => Yii::t('app', 'Password'),
        ];
    }

    /**
     * Gets query for [[PublicationService]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(PublicationService::class, ['id' => 'service_id']);
    }

    /**
     * Gets query for [[Project]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::class, ['id' => 'project_id']);
    }
}

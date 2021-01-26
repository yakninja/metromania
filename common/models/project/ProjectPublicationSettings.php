<?php

namespace common\models\project;

use Yii;

/**
 * This is the model class for table "project_publication_settings".
 *
 * @property int $id
 * @property int $project_id
 * @property int $provider_id
 * @property string|null $username
 * @property string|null $password
 *
 * @property PublicationProvider $provider
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
            [['!project_id', 'provider_id'], 'required'],
            [['project_id', 'provider_id'], 'integer'],
            [['username', 'password'], 'string', 'max' => 128],
            [['project_id', 'provider_id'], 'unique', 'targetAttribute' => ['project_id', 'provider_id']],
            [['provider_id'], 'exist', 'skipOnError' => true, 'targetClass' => PublicationProvider::class, 'targetAttribute' => ['provider_id' => 'id']],
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
            'provider_id' => Yii::t('app', 'Provider ID'),
            'username' => Yii::t('app', 'Username'),
            'password' => Yii::t('app', 'Password'),
        ];
    }

    /**
     * Gets query for [[Provider]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProvider()
    {
        return $this->hasOne(PublicationProvider::class, ['id' => 'provider_id']);
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

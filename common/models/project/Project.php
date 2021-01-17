<?php

namespace common\models\project;

use common\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "project".
 *
 * @property int $id
 * @property string $name
 * @property int $owner_id
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $owner
 * @property Source[] $sources
 */
class Project extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project';
    }

    public function behaviors()
    {
        return [
            'timestamp' => TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', '!owner_id'], 'required'],
            [['owner_id'], 'integer'],
            [['name'], 'string', 'max' => 128],
            [['owner_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['owner_id' => 'id']],
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
            'owner_id' => Yii::t('app', 'Owner ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * Gets query for [[Owner]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(User::class, ['id' => 'owner_id']);
    }

    /**
     * Gets query for [[Sources]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSources()
    {
        return $this->hasMany(Source::class, ['project_id' => 'id']);
    }
}

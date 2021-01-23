<?php

namespace common\models\project;

use Yii;

/**
 * This is the model class for table "destination".
 *
 * @property int $id
 * @property int $source_id
 * @property int $provider_id
 * @property int $created_at
 * @property int $updated_at
 * @property int $locked_until
 * @property int $status
 * @property string $url
 *
 * @property DestinationProvider $provider
 * @property Source $source
 */
class Destination extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'destination';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['source_id', 'provider_id', 'created_at', 'updated_at', 'status', 'url'], 'required'],
            [['source_id', 'provider_id', 'created_at', 'updated_at', 'locked_until', 'status'], 'integer'],
            [['url'], 'string', 'max' => 255],
            [['source_id', 'provider_id'], 'unique', 'targetAttribute' => ['source_id', 'provider_id']],
            [['provider_id'], 'exist', 'skipOnError' => true, 'targetClass' => DestinationProvider::class, 'targetAttribute' => ['provider_id' => 'id']],
            [['source_id'], 'exist', 'skipOnError' => true, 'targetClass' => Source::class, 'targetAttribute' => ['source_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'source_id' => Yii::t('app', 'Source ID'),
            'provider_id' => Yii::t('app', 'Provider ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'locked_until' => Yii::t('app', 'Locked Until'),
            'status' => Yii::t('app', 'Status'),
            'url' => Yii::t('app', 'Url'),
        ];
    }

    /**
     * Gets query for [[Provider]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProvider()
    {
        return $this->hasOne(DestinationProvider::class, ['id' => 'provider_id']);
    }

    /**
     * Gets query for [[Source]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSource()
    {
        return $this->hasOne(Source::class, ['id' => 'source_id']);
    }
}

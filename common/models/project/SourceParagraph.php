<?php

namespace common\models\project;

use Yii;

/**
 * This is the model class for table "source_paragraph".
 *
 * @property int $id
 * @property int $source_id
 * @property int $priority
 * @property string|null $content
 *
 * @property Source $source
 */
class SourceParagraph extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'source_paragraph';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['source_id', 'priority'], 'required'],
            [['source_id', 'priority'], 'integer'],
            [['content'], 'string'],
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
            'priority' => Yii::t('app', 'Priority'),
            'content' => Yii::t('app', 'Content'),
        ];
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

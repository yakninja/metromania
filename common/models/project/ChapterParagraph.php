<?php

namespace common\models\project;

use Yii;

/**
 * This is the model class for table "chapter_paragraph".
 *
 * @property int $id
 * @property int $chapter_id
 * @property int $priority
 * @property string|null $content
 *
 * @property Chapter $chapter
 */
class ChapterParagraph extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chapter_paragraph';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chapter_id', 'priority'], 'required'],
            [['chapter_id', 'priority'], 'integer'],
            [['content'], 'string'],
            [['chapter_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chapter::class, 'targetAttribute' => ['chapter_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'chapter_id' => Yii::t('app', 'Chapter ID'),
            'priority' => Yii::t('app', 'Priority'),
            'content' => Yii::t('app', 'Content'),
        ];
    }

    /**
     * Gets query for [[Chapter]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChapter()
    {
        return $this->hasOne(Chapter::class, ['id' => 'chapter_id']);
    }
}

<?php

namespace common\models\project;

use Yii;

/**
 * This is the model class for table "source".
 *
 * @property int $id
 * @property int $project_id
 * @property string|null $title
 * @property int $created_at
 * @property int $updated_at
 * @property int $locked_until
 * @property int $priority
 * @property int $status
 * @property string $url
 *
 * @property Project $project
 * @property SourceParagraph[] $sourceParagraphs
 */
class Source extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'source';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id', 'created_at', 'updated_at', 'priority', 'status', 'url'], 'required'],
            [['project_id', 'created_at', 'updated_at', 'locked_until', 'priority', 'status'], 'integer'],
            [['title'], 'string', 'max' => 128],
            [['url'], 'string', 'max' => 255],
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
            'title' => Yii::t('app', 'Title'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'locked_until' => Yii::t('app', 'Locked Until'),
            'priority' => Yii::t('app', 'Priority'),
            'status' => Yii::t('app', 'Status'),
            'url' => Yii::t('app', 'Url'),
        ];
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

    /**
     * Gets query for [[SourceParagraphs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSourceParagraphs()
    {
        return $this->hasMany(SourceParagraph::class, ['source_id' => 'id']);
    }
}

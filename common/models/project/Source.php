<?php

namespace common\models\project;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

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
 * @property int $edit_count
 * @property int $word_count
 *
 * @property Project $project
 * @property SourceParagraph[] $sourceParagraphs
 */
class Source extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_GET = 1;

    public static function statusLabels()
    {
        return [
            self::STATUS_NEW => Yii::t('app', 'Status: new'),
            self::STATUS_GET => Yii::t('app', 'Status: get'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'source';
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
            [['!project_id', 'url'], 'required'],
            [['project_id', 'priority', '!status', '!edit_count', '!word_count'], 'integer'],
            [['title'], 'string', 'max' => 128],
            [['url'], 'string', 'max' => 255],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::class, 'targetAttribute' => ['project_id' => 'id']],
            ['status', 'default', 'value' => self::STATUS_NEW],
            ['status', 'in', 'range' => array_keys(self::statusLabels())],
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
            'word_count' => Yii::t('app', 'Word Count'),
            'edit_count' => Yii::t('app', 'Edit Count'),
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert) {
            $this->priority = self::find()
                    ->select(new Expression('max(priority)'))
                    ->where(['project_id' => $this->project_id])
                    ->createCommand()
                    ->queryScalar() + 1;
        }
        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        if (array_key_exists('edit_count', $changedAttributes)
            || array_key_exists('word_count', $changedAttributes)) {
            $this->project->summarize();
        }
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

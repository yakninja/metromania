<?php

namespace common\models\chapter;

use common\models\project\Project;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "chapter".
 *
 * @property int $id
 * @property int $project_id
 * @property string|null $title
 * @property int $created_at
 * @property int $updated_at
 * @property int $content_updated_at
 * @property int $locked_until
 * @property int $priority
 * @property int $status
 * @property string $url
 * @property int $edit_count
 * @property int $word_count
 * @property string $error_message
 * @property string $hash
 * @property boolean $ignore_gray_text
 *
 * @property Project $project
 * @property ChapterParagraph[] $paragraphs
 * @property ChapterPublication[] $publications
 * @property string $content
 */
class Chapter extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_WAITING = 1;
    const STATUS_GET = 2;
    const STATUS_PUBLICATION = 3;
    const STATUS_OK = 10;
    const STATUS_ERROR = -1;

    public static function statusLabels()
    {
        return [
            self::STATUS_NEW => Yii::t('app', 'Status: new'),
            self::STATUS_WAITING => Yii::t('app', 'Status: waiting'),
            self::STATUS_GET => Yii::t('app', 'Status: get'),
            self::STATUS_PUBLICATION => Yii::t('app', 'Status: publication'),
            self::STATUS_ERROR => Yii::t('app', 'Status: error'),
            self::STATUS_OK => Yii::t('app', 'Status: OK'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chapter';
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
            [['project_id', 'priority', '!status', '!edit_count', '!word_count',
                '!content_updated_at'], 'integer'],
            [['title'], 'string', 'max' => 128],
            [['url'], 'string', 'max' => 255],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::class, 'targetAttribute' => ['project_id' => 'id']],
            ['status', 'default', 'value' => self::STATUS_NEW],
            ['status', 'in', 'range' => array_keys(self::statusLabels())],
            ['error_message', 'string'],
            ['hash', 'string', 'max' => 40],
            ['ignore_gray_text', 'boolean'],
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
            'content_updated_at' => Yii::t('app', 'Content Updated At'),
            'locked_until' => Yii::t('app', 'Locked Until'),
            'priority' => Yii::t('app', 'Priority'),
            'status' => Yii::t('app', 'Status'),
            'url' => Yii::t('app', 'Url'),
            'word_count' => Yii::t('app', 'Word Count'),
            'edit_count' => Yii::t('app', 'Edit Count'),
            'error_message' => Yii::t('app', 'Error Message'),
            'hash' => Yii::t('app', 'Hash'),
            'ignore_gray_text' => Yii::t('app', 'Ignore Gray Text'),
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
     * Gets query for [[ChapterParagraphs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParagraphs()
    {
        return $this->hasMany(ChapterParagraph::class, ['chapter_id' => 'id'])
            ->orderBy(['priority' => SORT_ASC]);
    }

    /**
     * Gets query for [[ChapterPublications]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPublications()
    {
        return $this->hasMany(ChapterPublication::class, ['chapter_id' => 'id']);
    }

    public function getContent()
    {
        $content = '';
        foreach ($this->paragraphs as $p) {
            $content .= trim($p->content) . "\n\n";
        }
        return $content;
    }

    /**
     * Lock this record for $time seconds. Using updated_at to avoid concurrent locking
     *
     * @param int $time
     * @param int $lock_status
     * @return bool true if lock was successful
     */
    public function lock(int $time, $lock_status = Chapter::STATUS_GET)
    {
        $row_count = Chapter::updateAll(
            [
                'locked_until' => time() + $time,
                'updated_at' => time(),
                'status' => $lock_status,
                'error_message' => null,
            ],
            [
                'id' => $this->id,
                'updated_at' => $this->updated_at,
            ],
        );
        if ($row_count) {
            $this->refresh();
            return true;
        }
        return false;
    }

    /**
     * @param $error_message
     * @return bool
     */
    public function setError($error_message)
    {
        Yii::error($error_message);
        $this->error_message = $error_message;
        $this->status = Chapter::STATUS_ERROR;
        $this->locked_until = 0;
        return $this->save();
    }
}

<?php

namespace common\models\chapter;

use common\models\PublicationService;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "publication".
 *
 * @property int $id
 * @property int $chapter_id
 * @property int $service_id
 * @property int $created_at
 * @property int $updated_at
 * @property int $locked_until
 * @property int $published_at
 * @property int $status
 * @property string $url
 * @property string $error_message
 * @property string $hash
 *
 * @property PublicationService $service
 * @property Chapter $chapter
 */
class ChapterPublication extends \yii\db\ActiveRecord
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

    public function behaviors()
    {
        return [
            'timestamp' => TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chapter_publication';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['!chapter_id', 'service_id', 'url'], 'required'],
            [['chapter_id', 'service_id', 'status'], 'integer'],
            [['url'], 'string', 'max' => 255],
            ['url', 'url'],
            [['chapter_id', 'service_id'], 'unique', 'targetAttribute' => ['chapter_id', 'service_id']],
            [['service_id', 'url'], 'unique', 'targetAttribute' => ['service_id', 'url']],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => PublicationService::class, 'targetAttribute' => ['service_id' => 'id']],
            [['chapter_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chapter::class, 'targetAttribute' => ['chapter_id' => 'id']],
            ['status', 'default', 'value' => self::STATUS_NEW],
            ['status', 'in', 'range' => array_keys(self::statusLabels())],
            ['error_message', 'string'],
            ['hash', 'string', 'max' => 40],
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
            'service_id' => Yii::t('app', 'Service ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'published_at' => Yii::t('app', 'Published At'),
            'locked_until' => Yii::t('app', 'Locked Until'),
            'status' => Yii::t('app', 'Status'),
            'url' => Yii::t('app', 'Url'),
            'hash' => Yii::t('app', 'Hash'),
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
     * Gets query for [[Chapter]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChapter()
    {
        return $this->hasOne(Chapter::class, ['id' => 'chapter_id']);
    }

    /**
     * Lock this record for $time seconds. Using updated_at to avoid concurrent locking
     *
     * @param int $time
     * @param int $lock_status
     * @return bool true if lock was successful
     */
    public function lock(int $time, $lock_status = Chapter::STATUS_PUBLICATION)
    {
        $row_count = ChapterPublication::updateAll(
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

<?php

namespace common\models\project;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "export".
 *
 * @property int $id
 * @property int $chapter_id
 * @property int $provider_id
 * @property int $created_at
 * @property int $updated_at
 * @property int $locked_until
 * @property int $status
 * @property string $url
 * @property string $error_message
 *
 * @property ExportProvider $provider
 * @property Chapter $chapter
 */
class ChapterExport extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_WAITING = 1;
    const STATUS_GET = 2;
    const STATUS_EXPORT = 3;
    const STATUS_OK = 10;
    const STATUS_ERROR = -1;

    public static function statusLabels()
    {
        return [
            self::STATUS_NEW => Yii::t('app', 'Status: new'),
            self::STATUS_WAITING => Yii::t('app', 'Status: waiting'),
            self::STATUS_GET => Yii::t('app', 'Status: get'),
            self::STATUS_EXPORT => Yii::t('app', 'Status: export'),
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
        return 'chapter_export';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['!chapter_id', 'provider_id', 'url'], 'required'],
            [['chapter_id', 'provider_id', 'status'], 'integer'],
            [['url'], 'string', 'max' => 255],
            ['url', 'url'],
            [['chapter_id', 'provider_id'], 'unique', 'targetAttribute' => ['chapter_id', 'provider_id']],
            [['provider_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExportProvider::class, 'targetAttribute' => ['provider_id' => 'id']],
            [['chapter_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chapter::class, 'targetAttribute' => ['chapter_id' => 'id']],
            ['status', 'default', 'value' => self::STATUS_NEW],
            ['status', 'in', 'range' => array_keys(self::statusLabels())],
            ['error_message', 'string'],
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
        return $this->hasOne(ExportProvider::class, ['id' => 'provider_id']);
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

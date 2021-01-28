<?php

namespace common\models\project;

use common\models\User;
use Google_Client;
use Google_Service_Docs;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "project".
 *
 * @property int $id
 * @property string $name
 * @property int $owner_id
 * @property int $created_at
 * @property int $updated_at
 * @property int $edit_count
 * @property int $word_count
 *
 * @property User $owner
 * @property ProjectAccessToken $accessToken
 * @property Chapter[] $chapters
 * @property ProjectPublicationSettings[] $publicationSettings
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
            [['owner_id', '!edit_count', '!word_count'], 'integer'],
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
            'word_count' => Yii::t('app', 'Word Count'),
            'edit_count' => Yii::t('app', 'Edit Count'),
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
     * Gets query for [[ProjectAccessToken]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccessToken()
    {
        return $this->hasOne(ProjectAccessToken::class, ['project_id' => 'id']);
    }

    /**
     * Gets query for [[Chapters]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChapters()
    {
        return $this->hasMany(Chapter::class, ['project_id' => 'id']);
    }

    /**
     * Gets query for [[ProjectPublicationSettings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPublicationSettings()
    {
        return $this->hasMany(ProjectPublicationSettings::class, ['project_id' => 'id']);
    }

    /**
     * @return Google_Client|null
     * @throws \Google\Exception
     */
    public function getGoogleClient()
    {
        if (!$this->accessToken) {
            return null;
        }

        $client = new Google_Client();
        $client->setScopes(Google_Service_Docs::DOCUMENTS_READONLY);
        $client->setAuthConfig(Yii::getAlias('@common/config/credentials.json'));
        $client->setAccessType('offline');
        $client->setAccessToken(Json::decode($this->accessToken->token));

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            $this->accessToken->token = Json::encode($client->getAccessToken());
            if (!$this->accessToken->save()) {
                Yii::error('Could not save project access token: ' .
                    implode(', ', $this->accessToken->firstErrors));
                return null;
            }
        }
        return $client;
    }

    /**
     * Summarize counters from all chapters
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public function summarize()
    {
        $counters = Chapter::find()
            ->select(['edit_count' => new Expression('sum(edit_count)'), 'word_count' => new Expression('sum(word_count)')])
            ->where(['project_id' => $this->id])
            ->createCommand()
            ->queryOne();
        $this->word_count = $counters['word_count'];
        $this->edit_count = $counters['edit_count'];
        return $this->save();
    }

    /**
     * How many chapters in project has what status
     *
     * @return array
     */
    public function getChapterStats()
    {
        $stats = ArrayHelper::map(Chapter::find()
            ->select("status, count(*) count")
            ->where(['project_id' => $this->id])
            ->groupBy('status')
            ->asArray()
            ->all(), 'status', 'count');

        $stats['total'] = array_sum(array_values($stats));

        $stats['with_edits'] = Chapter::find()
            ->select("count(*)")
            ->where(['project_id' => $this->id])
            ->andWhere('edit_count > 0')
            ->scalar();

        $stats['having_service'] = [];
        $pubServiceIds = PublicationService::find()->select('id')->column();

        foreach ($pubServiceIds as $id) {
            $stats['having_service'][$id] = ChapterPublication::find()
                ->innerJoinWith('chapter')
                ->where(['chapter.project_id' => $this->id, 'service_id' => $id])
                ->count();
        }

        $stats['total_publications'] = array_sum(array_values($stats['having_service']));
        $stats['changed_content'] = ChapterPublication::find()
            ->innerJoinWith('chapter')
            ->where(['chapter.project_id' => $this->id])
            ->andWhere('chapter.hash <> chapter_publication.hash')
            ->count();

        return $stats;
    }
}

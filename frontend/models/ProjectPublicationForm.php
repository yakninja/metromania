<?php

namespace frontend\models;

use common\jobs\ChapterPublishJob;
use common\models\chapter\ChapterPublication;
use common\models\project\Project;
use common\models\PublicationService;
use Yii;
use yii\base\Model;
use yii\queue\Queue;

/**
 * Export project's chapters
 *
 * @package frontend\models
 * @property int $project_id
 * @property Project $project
 */
class ProjectPublicationForm extends Model
{
    public $project_id;
    public $service_id;
    public $not_having_edits = true;
    public $only_if_changed = true;

    public function rules()
    {
        return [
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::class,
                'targetAttribute' => ['project_id' => 'id']],

            [['not_having_edits', 'only_if_changed'], 'boolean'],

            ['service_id', 'required'],
            ['service_id', 'each', 'rule' => ['exist', 'skipOnError' => true, 'targetClass' => PublicationService::class,
                'targetAttribute' => ['service_id' => 'id']]],
        ];
    }

    /**
     * @return false|int number of added rows or false on error
     * @throws \yii\db\Exception
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $query = ChapterPublication::find()
            ->innerJoinWith('chapter')
            ->where(['project_id' => $this->project_id, 'service_id' => $this->service_id]);

        if ($this->not_having_edits) {
            $query->andWhere('chapter.edit_count = 0');
        }

        if ($this->only_if_changed) {
            $query->andWhere('(chapter_publication.hash IS NULL 
                OR chapter.hash <> chapter_publication.hash)');
        }

        /** @var Queue $queue */
        $queue = Yii::$app->get('queue');
        $n = 0;
        foreach ($query->all() as $publication) {
            /** @var ChapterPublication $publication */
            $queue->push(new ChapterPublishJob(['chapter_id' => $publication->id,
                'service_id' => $publication->service_id]));
            $n++;
        }
        return $n;
    }

    public function attributeLabels()
    {
        return [
            'service_id' => Yii::t('app', 'Publication Service'),
            'not_having_edits' => Yii::t('app', 'Not Having Edits Only'),
            'only_if_changed' => Yii::t('app', 'Only If Content Changed'),
        ];
    }

    /**
     * @return Project|null
     */
    public function getProject()
    {
        return Project::findOne($this->project_id);
    }
}

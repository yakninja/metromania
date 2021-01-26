<?php

namespace frontend\models;

use common\models\project\Chapter;
use common\models\project\Project;
use yii\base\Model;

/**
 * Import multiple Google Docs links as project chapters
 *
 * @package frontend\models
 * @property string $urls
 * @property int $project_id
 * @property Project $project
 */
class ChapterImportForm extends Model
{
    public $urls;
    public $project_id;

    public function rules()
    {
        return [
            ['urls', 'string'],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::class, 'targetAttribute' => ['project_id' => 'id']],
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
        $n = 0;
        if (preg_match_all('`https://docs\.google\.com/document/d/[0-9a-z_-]+`is', $this->urls, $r)) {
            $n = Chapter::find()->select("max(priority)")->where(['project_id' => $this->project_id])
                ->createCommand()->queryScalar();
            foreach ($r[0] as $url) {
                if (Chapter::findOne(['project_id' => $this->project_id, 'url' => $url])) {
                    continue;
                }
                $chapter = new Chapter([
                    'project_id' => $this->project_id,
                    'url' => $url,
                    'priority' => ++$n,
                ]);
                if (!$chapter->save()) {
                    $this->addError('urls', array_pop($chapter->firstErrors));
                    return false;
                }
            }
        }
        // reindex
        $i = 0;
        /** @var Chapter[] $chapters */
        $chapters = Chapter::find()->where(['project_id' => $this->project_id])->orderBy(['priority' => SORT_ASC])->all();
        foreach ($chapters as $chapter) {
            $chapter->priority = ++$i;
            $chapter->save();
        }
        return $n;
    }

    /**
     * @return Project|null
     */
    public function getProject()
    {
        return Project::findOne($this->project_id);
    }
}

<?php

namespace frontend\models;

use common\models\project\Project;
use common\models\project\Source;
use yii\base\Model;

/**
 * Import multiple Google Docs links as project sources
 *
 * @package frontend\models
 * @property string $urls
 * @property int $project_id
 * @property Project $project
 */
class SourceImportForm extends Model
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
            $n = Source::find()->select("max(priority)")->where(['project_id' => $this->project_id])
                ->createCommand()->queryScalar();
            foreach ($r[0] as $url) {
                if (Source::findOne(['project_id' => $this->project_id, 'url' => $url])) {
                    continue;
                }
                $source = new Source([
                    'project_id' => $this->project_id,
                    'url' => $url,
                    'priority' => ++$n,
                ]);
                if (!$source->save()) {
                    $this->addError('urls', array_pop($source->firstErrors));
                    return false;
                }
            }
        }
        // reindex
        $i = 0;
        /** @var Source[] $sources */
        $sources = Source::find()->where(['project_id' => $this->project_id])->orderBy(['priority' => SORT_ASC])->all();
        foreach ($sources as $source) {
            $source->priority = ++$i;
            $source->save();
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

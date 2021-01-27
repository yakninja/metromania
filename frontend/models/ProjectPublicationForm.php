<?php

namespace frontend\models;

use common\models\project\Project;
use common\models\project\PublicationService;
use Yii;
use yii\base\Model;

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

    public function rules()
    {
        return [
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::class, 'targetAttribute' => ['project_id' => 'id']],

            ['not_having_edits', 'boolean'],

            ['service_id', 'required'],
            ['service_id', 'each', 'rule' => ['exist', 'skipOnError' => true, 'targetClass' => PublicationService::class]],
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
        return $n;
    }

    public function attributeLabels()
    {
        return [
            'service_id' => Yii::t('app', 'Publication Provider'),
            'not_having_edits' => Yii::t('app', 'Not Having Edits Only'),
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

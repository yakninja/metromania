<?php

namespace common\models\project;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ChapterSearch represents the model behind the search form of `common\models\project\Chapter`.
 */
class ChapterSearch extends Chapter
{
    public $with_edits;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'project_id', 'status'], 'integer'],
            [['title', 'url'], 'safe'],
            ['with_edits', 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Chapter::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['priority' => SORT_ASC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'project_id' => $this->project_id,
            'status' => $this->status,
        ]);

        if (trim($this->with_edits) != '') {
            $query->andWhere([$this->with_edits ? '<>' : '=', 'edit_count', 0]);
        }

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'url', $this->url]);

        return $dataProvider;
    }
}

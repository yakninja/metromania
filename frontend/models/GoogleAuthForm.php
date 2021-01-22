<?php

namespace frontend\models;

use common\models\project\Project;
use common\models\project\ProjectAccessToken;
use Google_Client;
use Google_Service_Docs;
use Yii;
use yii\base\Model;

class GoogleAuthForm extends Model
{
    public $project_id;
    public $authCode;

    public function rules()
    {
        return [
            ['authCode', 'string'],
            ['project_id', 'integer'],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::class, 'targetAttribute' => ['project_id' => 'id']],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        $client = new Google_Client();
        $client->setScopes(Google_Service_Docs::DOCUMENTS_READONLY);
        $client->setAuthConfig(Yii::getAlias('@common/config/credentials.json'));
        $client->setAccessType('offline');
        $token = $client->fetchAccessTokenWithAuthCode($this->authCode);
        $accessToken = ProjectAccessToken::findOne(['project_id' => $this->project_id]);
        if (!$accessToken) {
            $accessToken = new ProjectAccessToken(['project_id' => $this->project_id]);
        }
        $accessToken->token = $token;
        if (!$accessToken->save()) {
            $this->addErrors($accessToken->errors);
            return false;
        }
        return true;
    }
}

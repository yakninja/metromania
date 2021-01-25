<?php

namespace frontend\controllers;

use common\jobs\SourceGetJob;
use common\models\project\Project;
use common\models\project\ProjectExportSettings;
use common\models\project\ProjectSearch;
use common\models\project\Source;
use common\models\project\SourceSearch;
use frontend\models\GoogleAuthForm;
use Google_Client;
use Google_Service_Docs;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\queue\Queue;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ProjectController implements the CRUD actions for Project model.
 */
class ProjectController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'get-all-sources' => ['POST'],
                    'export-setting-delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Project models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProjectSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Project model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $sourceSearchModel = new SourceSearch(['project_id' => $id]);
        $sourceDataProvider = $sourceSearchModel->search(Yii::$app->request->queryParams);

        return $this->render('view', [
            'model' => $model,
            'sourceSearchModel' => $sourceSearchModel,
            'sourceDataProvider' => $sourceDataProvider,
        ]);
    }

    /**
     * Creates a new Project model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Project(['owner_id' => Yii::$app->user->id]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Project created'));
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Project model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Project saved'));
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ProjectExportSetting model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionExportSettingUpdate($id)
    {
        $model = $this->findExportSettingModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Project export setting saved'));
            return $this->redirect(['export-settings', 'project_id' => $model->project_id]);
        }

        return $this->render('update_export_setting', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionExportSettingDelete($id)
    {
        $model = $this->findExportSettingModel($id);
        $model->delete();
        Yii::$app->session->addFlash('success', Yii::t('app', 'Project export setting saved'));
        return $this->redirect(['export-settings', 'project_id' => $model->project_id]);
    }

    /**
     * @param $project_id
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionGetAllSources($project_id)
    {
        $project = $this->findModel($project_id);
        Source::updateAll(['status' => Source::STATUS_WAITING], ['project_id' => $project_id]);
        /** @var Queue $queue */
        $queue = Yii::$app->get('queue');
        foreach ($project->sources as $source) {
            $queue->push(new SourceGetJob(['source_id' => $source->id]));
        }
        Yii::$app->session->addFlash('success', Yii::t('app', '{n} tasks queued',
            ['n' => count($project->sources)]));
        return $this->redirect(['view', 'id' => $project_id]);
    }

    /**
     * @param $project_id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionExportSettings($project_id)
    {
        $project = $this->findModel($project_id);
        $dataProvider = new ActiveDataProvider([
            'query' => ProjectExportSettings::find()->where(['project_id' => $project_id])
        ]);
        return $this->render('export_settings', [
            'project' => $project,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param $project_id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionCreateExportSetting($project_id)
    {
        $project = $this->findModel($project_id);
        $model = new ProjectExportSettings(['project_id' => $project_id]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Project export setting added'));
            return $this->redirect(['export-settings', 'project_id' => $project_id]);
        }
        return $this->render('create_export_setting', [
            'project' => $project,
            'model' => $model,
        ]);
    }

    /**
     * @param $project_id
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCreateAccessToken($project_id)
    {
        $project = $this->findModel($project_id);

        $model = new GoogleAuthForm(['project_id' => $project_id]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Project access token created'));
            return $this->redirect(['view', 'id' => $project->id]);
        }

        $client = new Google_Client();
        $client->setScopes(Google_Service_Docs::DOCUMENTS_READONLY);
        $client->setAuthConfig(Yii::getAlias('@common/config/credentials.json'));
        $client->setAccessType('offline');

        // Request authorization from the user.
        $authUrl = $client->createAuthUrl();

        return $this->render('create_access_token', [
            'model' => $model,
            'project' => $project,
            'authUrl' => $authUrl,
        ]);
    }

    /**
     * Deletes an existing Project model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->addFlash('success', Yii::t('app', 'Project deleted'));
        return $this->redirect(['index']);
    }

    /**
     * Finds the Project model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Project the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Project::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    private function findExportSettingModel(int $id)
    {
        if (($model = ProjectExportSettings::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}

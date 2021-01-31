<?php

namespace frontend\controllers;

use common\jobs\ChapterGetJob;
use common\models\chapter\Chapter;
use common\models\chapter\ChapterSearch;
use common\models\project\Project;
use common\models\project\ProjectPublicationSettings;
use common\models\project\ProjectSearch;
use frontend\models\GoogleAuthForm;
use frontend\models\ProjectPublicationForm;
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
                    'get-all-chapters' => ['POST'],
                    'publication-setting-delete' => ['POST'],
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
        $chapterSearchModel = new ChapterSearch(['project_id' => $id]);
        $chapterDataProvider = $chapterSearchModel->search(Yii::$app->request->queryParams);

        return $this->render('view', [
            'model' => $model,
            'chapterSearchModel' => $chapterSearchModel,
            'chapterDataProvider' => $chapterDataProvider,
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
     * Updates an existing ProjectPublicationSetting model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionPublicationSettingUpdate($id)
    {
        $model = $this->findPublicationSettingModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Project publication setting saved'));
            return $this->redirect(['publication-settings', 'project_id' => $model->project_id]);
        }

        return $this->render('update_publication_setting', [
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
    public function actionPublicationSettingDelete($id)
    {
        $model = $this->findPublicationSettingModel($id);
        $model->delete();
        Yii::$app->session->addFlash('success', Yii::t('app', 'Project publication setting saved'));
        return $this->redirect(['publication-settings', 'project_id' => $model->project_id]);
    }

    /**
     * @param $project_id
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionGetAllChapters($project_id)
    {
        $project = $this->findModel($project_id);
        $query = Chapter::find()->where(['project_id' => $project_id]);
        if (Yii::$app->request->isPost && ($id = Yii::$app->request->post('id'))) {
            $query->andWhere(['id' => $id]);
        }
        /** @var Queue $queue */
        $queue = Yii::$app->get('queue');
        $n = 0;
        foreach ($query->all() as $chapter) {
            /** @var Chapter $chapter */
            $chapter->warning_message = null;
            $chapter->status = Chapter::STATUS_WAITING;
            $chapter->save();
            $queue->push(new ChapterGetJob(['chapter_id' => $chapter->id]));
            $n++;
        }
        Yii::$app->session->addFlash('success', Yii::t('app', '{n} tasks queued', ['n' => $n]));
        return $this->redirect(['view', 'id' => $project_id]);
    }

    /**
     * @param $project_id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPublicationSettings($project_id)
    {
        $project = $this->findModel($project_id);
        $dataProvider = new ActiveDataProvider([
            'query' => ProjectPublicationSettings::find()->where(['project_id' => $project_id])
        ]);
        return $this->render('publication_settings', [
            'project' => $project,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param $project_id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionCreatePublicationSetting($project_id)
    {
        $project = $this->findModel($project_id);
        $model = new ProjectPublicationSettings(['project_id' => $project_id]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Project publication setting added'));
            return $this->redirect(['publication-settings', 'project_id' => $project_id]);
        }
        return $this->render('create_publication_setting', [
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
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPublish($id)
    {
        $this->findModel($id);
        $model = new ProjectPublicationForm(['project_id' => $id]);
        if ($model->load(Yii::$app->request->post()) && ($n = $model->save()) !== false) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{n} tasks queued', ['n' => $n]));
            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->render('publish', [
            'model' => $model,
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

    private function findPublicationSettingModel(int $id)
    {
        if (($model = ProjectPublicationSettings::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}

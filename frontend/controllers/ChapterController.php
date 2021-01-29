<?php

namespace frontend\controllers;

use common\jobs\ChapterGetJob;
use common\jobs\ChapterPublishJob;
use common\models\chapter\Chapter;
use common\models\chapter\ChapterPublication;
use common\models\project\Project;
use frontend\models\ChapterImportForm;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\queue\Queue;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ChapterController implements the CRUD actions for Chapter model.
 */
class ChapterController extends Controller
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
                    'get' => ['POST'],
                    'publish' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Displays a single Chapter model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Chapter model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param $project_id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCreate($project_id)
    {
        $this->findProjectModel($project_id);
        $model = new Chapter(['project_id' => $project_id]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Chapter added'));
            return $this->redirect(['/project/view', 'id' => $model->project_id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Import multiple chapters.
     * If import is successful, the browser will be redirected to the 'view' page.
     * @param $project_id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionImport($project_id)
    {
        $this->findProjectModel($project_id);
        $model = new ChapterImportForm(['project_id' => $project_id]);

        if ($model->load(Yii::$app->request->post()) && ($n = $model->save()) !== false) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{n} chapters imported', ['n' => $n]));
            return $this->redirect(['/project/view', 'id' => $model->project_id]);
        }

        return $this->render('import', [
            'model' => $model,
        ]);
    }

    /**
     * Get chapter from Google docs (aget)
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGet($id)
    {
        $model = $this->findModel($id);
        $model->status = Chapter::STATUS_WAITING;
        $model->warning_message = null;
        $model->save();
        /** @var Queue $queue */
        $queue = Yii::$app->get('queue');
        $queue->push(new ChapterGetJob(['chapter_id' => $id]));
        Yii::$app->session->addFlash('success', Yii::t('app', 'Chapter get queued'));
        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPublicationSettings($chapter_id)
    {
        $model = $this->findModel($chapter_id);
        $dataProvider = new ActiveDataProvider([
            'query' => ChapterPublication::find()->where(['chapter_id' => $chapter_id])
        ]);
        return $this->render('publication_settings', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * @param $chapter_id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionCreatePublicationSetting($chapter_id)
    {
        $project = $this->findModel($chapter_id);
        $model = new ChapterPublication(['chapter_id' => $chapter_id]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Chapter publication setting added'));
            return $this->redirect(['publication-settings', 'chapter_id' => $chapter_id]);
        }
        return $this->render('create_publication_setting', [
            'project' => $project,
            'model' => $model,
        ]);
    }

    /**
     * @param $chapter_id
     * @param $service_id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionPublish($chapter_id, $service_id=null)
    {
        $model = $this->findModel($chapter_id);
        /** @var Queue $queue */
        $queue = Yii::$app->get('queue');
        $queue->push(new ChapterPublishJob(['chapter_id' => $model->id, 'service_id' => $service_id]));
        Yii::$app->session->addFlash('success', Yii::t('app', 'Chapter publication queued'));
        return $this->redirect(['view', 'id' => $chapter_id]);
    }

    /**
     * Updates an existing Chapter model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Chapter saved'));
            return $this->redirect(['/project/view', 'id' => $model->project_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Chapter model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        Yii::$app->session->addFlash('success', Yii::t('app', 'Chapter deleted'));

        return $this->redirect(['/project/view', 'id' => $model->project_id]);
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
            Yii::$app->session->addFlash('success', Yii::t('app', 'Chapter publication setting saved'));
            return $this->redirect(['publication-settings', 'chapter_id' => $model->chapter_id]);
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
        Yii::$app->session->addFlash('success', Yii::t('app', 'Chapter publication setting saved'));
        return $this->redirect(['publication-settings', 'chapter_id' => $model->chapter_id]);
    }

    /**
     * Finds the Chapter model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Chapter the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Chapter::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    /**
     * Finds the Project model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Project the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findProjectModel($id)
    {
        if (($model = Project::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    private function findPublicationSettingModel(int $id)
    {
        if (($model = ChapterPublication::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}

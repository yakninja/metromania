<?php

namespace frontend\controllers;

use common\jobs\ChapterGetJob;
use common\models\project\Chapter;
use common\models\project\ChapterExport;
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
                    'put' => ['POST'],
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
    public function actionExportSettings($chapter_id)
    {
        $model = $this->findModel($chapter_id);
        $dataProvider = new ActiveDataProvider([
            'query' => ChapterExport::find()->where(['chapter_id' => $chapter_id])
        ]);
        return $this->render('export_settings', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * @param $chapter_id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionCreateExportSetting($chapter_id)
    {
        $project = $this->findModel($chapter_id);
        $model = new ChapterExport(['chapter_id' => $chapter_id]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Chapter export setting added'));
            return $this->redirect(['export-settings', 'chapter_id' => $chapter_id]);
        }
        return $this->render('create_export_setting', [
            'project' => $project,
            'model' => $model,
        ]);
    }

    public function actionPut($id)
    {
        $model = $this->findModel($id);
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
}

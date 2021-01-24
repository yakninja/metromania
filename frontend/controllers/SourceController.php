<?php

namespace frontend\controllers;

use common\jobs\SourceGetJob;
use common\models\project\Project;
use common\models\project\Source;
use frontend\models\SourceImportForm;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\queue\Queue;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * SourceController implements the CRUD actions for Source model.
 */
class SourceController extends Controller
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
     * Displays a single Source model.
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
     * Creates a new Source model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param $project_id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCreate($project_id)
    {
        $this->findProjectModel($project_id);
        $model = new Source(['project_id' => $project_id]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Source added'));
            return $this->redirect(['/project/view', 'id' => $model->project_id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Import multiple sources.
     * If import is successful, the browser will be redirected to the 'view' page.
     * @param $project_id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionImport($project_id)
    {
        $this->findProjectModel($project_id);
        $model = new SourceImportForm(['project_id' => $project_id]);

        if ($model->load(Yii::$app->request->post()) && ($n = $model->save()) !== false) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{n} sources imported', ['n' => $n]));
            return $this->redirect(['/project/view', 'id' => $model->project_id]);
        }

        return $this->render('import', [
            'model' => $model,
        ]);
    }

    /**
     * Get source from Google docs (aget)
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGet($id)
    {
        $model = $this->findModel($id);
        /** @var Queue $queue */
        $queue = Yii::$app->get('queue');
        $queue->push(new SourceGetJob(['source_id' => $id]));
        Yii::$app->session->addFlash('success', Yii::t('app', 'Source get queued'));
        return $this->redirect(['/project/view', 'id' => $model->project_id]);
    }

    public function actionPut($id)
    {
        $model = $this->findModel($id);
    }

    /**
     * Updates an existing Source model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Source saved'));
            return $this->redirect(['/project/view', 'id' => $model->project_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Source model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        Yii::$app->session->addFlash('success', Yii::t('app', 'Source deleted'));

        return $this->redirect(['/project/view', 'id' => $model->project_id]);
    }

    /**
     * Finds the Source model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Source the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Source::findOne($id)) !== null) {
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
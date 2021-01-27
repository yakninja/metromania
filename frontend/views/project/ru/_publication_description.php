<?php

use yii\bootstrap4\Html;

/** @var \frontend\models\ProjectPublicationForm $model */

?>
Публикуются главы со статусом <code>OK</code>, у которых заполнены настройки публикации
(в какой сервис публиковать и на какой URL). Кроме того, для проекта должны быть
<?= Html::a('настроены сервисы публикации', ['/project/publication-settings', 'project_id' => $model->project_id]) ?>
 (имя пользователя, пароль).

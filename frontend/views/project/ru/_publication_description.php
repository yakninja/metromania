<?php

use common\models\chapter\Chapter;
use yii\bootstrap4\Html;

/** @var \frontend\models\ProjectPublicationForm $model */
/** @var array $chapterStats */

?>
<p>Публикуются главы со статусом <code>OK</code>
    (<?= Yii::t('app', '{n} out of {total} chapters',
        ['n' => $chapterStats[Chapter::STATUS_OK], 'total' => $chapterStats['total']]) ?>),
    у которых заполнены настройки публикации (в какой сервис публиковать и на какой URL).</p>

<p>Кроме того, для проекта должны быть
    <?= Html::a('настроены сервисы публикации', ['/project/publication-settings', 'project_id' => $model->project_id]) ?>
    (имя пользователя, пароль).</p>

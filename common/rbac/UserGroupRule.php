<?php

namespace common\rbac;

use common\models\User;
use Yii;
use yii\rbac\Rule;

/**
 * Checks if user is logged in and active
 */
class UserGroupRule extends Rule
{
    public $name = 'userGroup';

    public function execute($user, $item, $params)
    {
        $user = Yii::$app->user;
        /** @var User $identity */
        $identity = $user->identity;

        return !$user->isGuest && $identity->isActive();
    }
}

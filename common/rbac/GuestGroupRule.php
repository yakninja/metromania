<?php

namespace common\rbac;

use yii\rbac\Rule;

/**
 * Checks if user is guest (always)
 */
class GuestGroupRule extends Rule
{
    public $name = 'guestGroup';

    public function execute($user, $item, $params)
    {
        return true;
    }
}
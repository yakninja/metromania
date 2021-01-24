<?php

namespace console\controllers;

use common\models\User;
use common\rbac\GuestGroupRule;
use common\rbac\UserGroupRule;
use Yii;
use yii\base\InvalidArgumentException;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\rbac\DbManager;

/**
 * Assign roles, init permissions
 *
 * @package console\controllers
 */
class RbacController extends Controller
{
    /**
     * @var DbManager
     */
    protected $auth;

    public function init()
    {
        parent::init();
        $this->auth = Yii::$app->authManager;
    }

    /**
     * Recreate permission tree. Will assign old roles (admin, moderator etc) to users, but not individual permissions,
     * so be careful about that
     *
     * @return int
     * @throws \Exception
     */
    public function actionInit()
    {
        $userCount = User::find()->count();
        if ($userCount && !$this->confirm("Are you sure? It will re-create permissions tree and revoke all individual permissions\n" .
                "(but it will re-assign all user roles like admin, moderator etc).")) {
            return ExitCode::OK;
        }

        $saveRoles = [
            'admin' => [],
        ];
        $safetyCommands = [];
        foreach ($saveRoles as $role => $value) {
            $saveRoles[$role] = $this->auth->getUserIdsByRole($role);
            if ($saveRoles[$role]) {
                $users = User::findAll(['id' => $saveRoles[$role]]);
                foreach ($users as $user) {
                    $safetyCommands[] = './yii rbac/assign ' . $role . ' ' . $user->email;
                }
            }
        }

        if ($safetyCommands) {
            echo "In case something goes wrong, current role assignments can be recreated using commands:\n" .
                implode("\n", $safetyCommands) . "\n";
        }

        echo "Removing all...\n";

        $this->auth->removeAll();

        echo "Creating base roles...\n";

        $guest = $this->auth->createRole('guest');
        $rule = new GuestGroupRule();
        $this->auth->add($rule);
        $guest->ruleName = $rule->name;
        $this->auth->add($guest);

        $user = $this->auth->createRole('user');
        $rule = new UserGroupRule;
        $this->auth->add($rule);
        $user->ruleName = $rule->name;
        $this->auth->add($user);
        $this->auth->addChild($user, $guest);

        $admin = $this->auth->createRole('admin');
        $this->auth->add($admin);
        $this->auth->addChild($admin, $user);

        echo "Creating permission tree...\n";

        $this->initUserPermissions();

        echo "Assigning saved roles...\n";

        foreach ($saveRoles as $roleName => $userIds) {
            $role = $this->auth->getRole($roleName);
            foreach ($userIds as $userId) {
                $user = User::findOne($userId);
                if ($user) {
                    echo "User: " . $user->email . ', Role: ' . $roleName . "\n";
                    $this->auth->assign($role, $user->id);
                }
            }
        }

        echo "done.\n";
        return ExitCode::OK;
    }

    /**
     * Standard CRUD permissions for model
     *
     * @param string $modelName
     * @param string $roleName if set, add permissions to this role, otherwise they are added to admin role only
     * @throws
     */
    private function initCRUDPermissions($modelName, $roleName = null)
    {
        $viewPermission = $this->auth->createPermission('view' . $modelName);
        $this->auth->add($viewPermission);

        $createPermission = $this->auth->createPermission('create' . $modelName);
        $this->auth->add($createPermission);
        $this->auth->addChild($createPermission, $viewPermission);

        $updatePermission = $this->auth->createPermission('update' . $modelName);
        $this->auth->add($updatePermission);
        $this->auth->addChild($updatePermission, $viewPermission);

        $deletePermission = $this->auth->createPermission('delete' . $modelName);
        $this->auth->add($deletePermission);
        $this->auth->addChild($deletePermission, $viewPermission);

        $managerRole = $this->auth->createRole(lcfirst($modelName) . 'Manager');
        $this->auth->add($managerRole);
        $this->auth->addChild($managerRole, $createPermission);
        $this->auth->addChild($managerRole, $updatePermission);
        $this->auth->addChild($managerRole, $deletePermission);

        $admin = $this->auth->getRole('admin');
        if ($roleName) {
            $role = $this->auth->getRole($roleName);
            $this->auth->addChild($role, $managerRole);
        } else {
            $this->auth->addChild($admin, $managerRole);
        }
    }

    private function initUserPermissions()
    {
        $this->initCRUDPermissions('User', 'admin');
    }

    /**
     * php yii rbac/assign admin admin@example.net
     *
     * @param $roleName
     * @param $email
     * @return int
     * @throws \Exception
     */
    public function actionAssign($roleName, $email)
    {
        /** @var User $user */
        $user = User::find()->where(['email' => $email])->one();
        if (!$user) {
            throw new InvalidArgumentException("There is no user \"$email\".");
        }

        $role = $this->auth->getRole($roleName);
        if (!$role) {
            throw new InvalidArgumentException("There is no role \"$roleName\".");
        }

        $this->auth->assign($role, $user->id);

        echo "done.\n";
        return ExitCode::OK;
    }

    /**
     * php yii rbac/revoke admin admin@example.net
     * php yii rbac/revoke all admin@example.net
     *
     * @param $roleName
     * @param $email
     * @return int
     */
    public function actionRevoke($roleName, $email)
    {
        /** @var User $user */
        $user = User::find()->where(['email' => $email])->one();
        if (!$user) {
            throw new InvalidArgumentException("There is no user \"$email\".");
        }

        if ($roleName == 'all') {
            $this->auth->revokeAll($user->id);
        } else {
            $role = $this->auth->getRole($roleName);
            if (!$role) {
                throw new InvalidArgumentException("There is no role \"$roleName\".");
            }
            $this->auth->revoke($role, $user->id);
        }

        echo "done.\n";
        return ExitCode::OK;
    }

}

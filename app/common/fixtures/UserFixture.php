<?php

declare(strict_types=1);

namespace common\fixtures;

use common\models\User;
use Yii;
use yii\test\ActiveFixture;

class UserFixture extends ActiveFixture
{
    public $modelClass = User::class;

    protected function getData(): array
    {
        return [
            'admin' => [
                'username' => 'admin',
                'email' => 'admin@test-task.local',
                'auth_key' => Yii::$app->security->generateRandomString(),
                'password_hash' => Yii::$app->security->generatePasswordHash('admin'),
                'status' => User::STATUS_ACTIVE,
                'created_at' => 1697303024,
                'updated_at' => 1697303024,
            ],
        ];
    }
}

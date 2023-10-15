<?php

declare(strict_types=1);

namespace backend\repositories;

use common\models\User;
use yii\db\Connection;

class UserRepository
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function findByUsername(string $username): ?User
    {
        /** @var User|null $user */
        $user = User::find()->andWhere([
            'username' => $username,
            'status' => User::STATUS_ACTIVE,
        ])->one($this->db);

        return $user;
    }
}

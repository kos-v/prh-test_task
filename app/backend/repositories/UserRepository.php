<?php

declare(strict_types=1);

namespace backend\repositories;

use common\models\User;

class UserRepository
{
    public function findByUsername(string $username): ?User
    {
        return User::findOne([
            'username' => $username,
            'status' => User::STATUS_ACTIVE,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace backend\services;

use backend\repositories\UserRepository;
use yii\base\Security as SecurityComponent;
use yii\web\User as UserComponent;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly SecurityComponent $securityComponent,
        private readonly UserComponent $userComponent,
        private readonly int $rememberMeTimeLen
    ) {
    }

    public function authenticateByLoginAndPassword(string $login, string $password, bool $rememberMe): bool
    {
        $user = $this->userRepository->findByUsername($login);
        if (!($user && $this->securityComponent->validatePassword($password, $user->password_hash))) {
            return false;
        }

        return $this->userComponent->login($user, $rememberMe ? $this->rememberMeTimeLen : 0);
    }

    public function logoutCurrentUser(): bool
    {
        return $this->userComponent->logout();
    }
}

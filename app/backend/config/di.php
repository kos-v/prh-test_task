<?php

use backend\repositories\AppleRepository;
use backend\repositories\UserRepository;

use backend\services\AuthService;

use yii\di\Container;

return [
    'definitions' => [
        // Services
        AuthService::class => function (Container $container) {
            return new AuthService(
                $container->get(UserRepository::class),
                Yii::$app->security,
                Yii::$app->user,
                Yii::$app->params['authRememberMeTimeLen']
            );
        },

        // Repositories
        AppleRepository::class => function (Container $container) {
            return new AppleRepository(Yii::$app->db);
        },
    ],
];
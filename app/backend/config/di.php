<?php

use backend\repositories\AppleRepository;
use backend\repositories\UserRepository;

use backend\services\AppleDnaService;
use backend\services\AppleGerminationService;
use backend\services\AppleConditionService;
use backend\services\AppleWorkflowService;
use backend\services\AppleGenerateService;
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
        AppleGenerateService::class => function (Container $container) {
            return new AppleGenerateService(
                $container->get(AppleDnaService::class),
                $container->get(AppleGerminationService::class),
                $container->get(AppleConditionService::class),
                $container->get(AppleWorkflowService::class),
                $container->get(AppleRepository::class),
            );
        },
        AppleWorkflowService::class => function (Container $container) {
            return new AppleWorkflowService($container->get(AppleRepository::class));
        },

        // Repositories
        AppleRepository::class => function (Container $container) {
            return new AppleRepository(Yii::$app->db);
        },
        UserRepository::class => function (Container $container) {
            return new UserRepository(Yii::$app->db);
        },
    ],
];
<?php

use common\repositories\AppleRepository;
use common\repositories\UserRepository;

use common\services\AppleConditionService;
use common\services\AppleDnaService;
use common\services\AppleGenerateService;
use common\services\AppleGerminationService;
use common\services\AppleWorkflowService;
use common\services\AuthService;

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
        AppleRepository::class => function () {
            return new AppleRepository(Yii::$app->db);
        },
        UserRepository::class => function () {
            return new UserRepository(Yii::$app->db);
        },
    ],
];
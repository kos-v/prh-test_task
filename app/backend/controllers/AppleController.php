<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\repositories\AppleRepository;
use yii\filters\AccessControl;
use yii\web\Controller;

class AppleController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(AppleRepository $repository): string
    {
        return $this->render('index', [
            'dataProvider' => $repository->findAllThroughDataProvider()
        ]);
    }
}

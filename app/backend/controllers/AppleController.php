<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\repositories\AppleRepository;
use backend\services\AppleService;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'regenerate' => ['post'],
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

    public function actionRegenerate(AppleService $appleService): Response
    {
        $appleService->regenerateAllByRandomQty();

        return $this->redirect('index');
    }
}

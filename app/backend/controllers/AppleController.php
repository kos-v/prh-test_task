<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\forms\EatForm;
use common\models\Apple;
use common\repositories\AppleRepository;
use common\services\AppleGenerateService;
use common\services\AppleWorkflowService;
use common\valueObjects\Percent;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;
use yii\web\Session;

class AppleController extends Controller
{
    public function __construct(
        $id,
        $module,
        $config,
        private readonly AppleRepository $appleRepository,
        private readonly AppleWorkflowService $appleWorkflowService
    ) {
        parent::__construct($id, $module, $config ?? []);
    }

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
                    'delete' => ['delete'],
                    'eat' => ['post'],
                    'fallToGround' => ['post'],
                    'regenerate' => ['post'],
                    'spoil' => ['post'],
                ],
            ],
        ];
    }

    public function actionDelete(int $id, Session $session): Response
    {
        $apple = $this->getAppleById($id);

        if (!$this->appleWorkflowService->canDelete($apple)) {
            $session->addFlash('error', "Failed to delete apple with id {$apple->id}");
        }

        $this->appleWorkflowService->delete($apple);

        return $this->redirect('index');
    }

    public function actionEat(int $id, Request $request, Session $session): Response
    {
        $apple = $this->getAppleById($id);

        $form = new EatForm();
        if ($form->load($request->post(), '') && $form->validate()) {
            $pieceSize = Percent::makeFromFloat($form->pieceSize);
            if (!$this->appleWorkflowService->canEat($apple, $pieceSize->toBankingFormat())) {
                $session->addFlash('error', "Failed to eat apple by id {$apple->id}");
            }

            $this->appleWorkflowService->eat($apple, $pieceSize->toBankingFormat());
        } else {
            $session->addFlash('error', "pieceSize: {$form->getFirstError('pieceSize')}");
        }

        return $this->redirect('index');
    }

    public function actionFallToGround(int $id, Session $session): Response
    {
        $apple = $this->getAppleById($id);

        if (!$this->appleWorkflowService->canFallToGround($apple)) {
            $session->addFlash('error', "Failed to fall to ground apple bu id {$apple->id}");
        }

        $this->appleWorkflowService->fallToGround($apple);

        return $this->redirect('index');
    }

    public function actionIndex(): string
    {
        return $this->render('index', [
            'dataProvider' => $this->appleRepository->findAllThroughDataProvider(),
            'appleWorkflowService' => $this->appleWorkflowService,
        ]);
    }

    public function actionRegenerate(AppleGenerateService $appleGenerateService): Response
    {
        $appleGenerateService->regenerateAllByRandomQty();

        return $this->redirect('index');
    }

    public function actionSpoil(int $id, Session $session): Response
    {
        $apple = $this->getAppleById($id);

        if (!$this->appleWorkflowService->canSpoil($apple)) {
            $session->addFlash('error', "Failed to spoil apple by id {$apple->id}");
        }

        $this->appleWorkflowService->spoil($apple);

        return $this->redirect('index');
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getAppleById(int $id): Apple
    {
        return $this->appleRepository->findOneById($id) ?? throw new NotFoundHttpException('Apple not found');
    }
}

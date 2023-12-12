<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\forms\LoginForm;
use common\services\AuthService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;
use yii\web\User;

class AuthController extends Controller
{
    public $layout = 'blank';

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
                        'actions' => ['login'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actionLogin(Request $request, User $user, AuthService $authService): Response|string
    {
        if (!$user->isGuest) {
            return $this->goHome();
        }

        $form = new LoginForm();
        if ($form->load($request->post()) && $form->validate()) {
            $isAuthenticated = $authService->authenticateByLogin(
                $form->username,
                $form->password,
                $form->rememberMe
            );

            if ($isAuthenticated) {
                return $this->goBack();
            }

            $form->addError('username', Yii::t('app/auth', 'Incorrect username or password.'));
        }

        $form->password = '';

        return $this->render('login', [
            'form' => $form,
        ]);
    }

    public function actionLogout(AuthService $authService): Response
    {
        $authService->logoutCurrentUser();

        return $this->goHome();
    }
}

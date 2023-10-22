<?php

use common\models\Apple;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\grid\SerialColumn;
use yii\helpers\Html;
use yii\i18n\Formatter;

/** @var backend\services\AppleWorkflowService $appleWorkflowService */
/** @var yii\web\View $this */
/** @var yii\data\BaseDataProvider $dataProvider */

$this->title = Yii::t('app/apples', 'Apples');
?>
<div class="apple-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(
            Yii::t('app/apples', 'Regenerate'),
            ['/apple/regenerate'],
            [
                'data' => ['method' => 'post'],
                'class' => 'btn btn-info btn-sm',
            ]
        ) ?>
    </p>

    <?= GridView::widget(config: [
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => SerialColumn::class],

            'id',
            [
                'attribute' => 'color',
                'label' => Yii::t('app/apples', 'Color'),
                'format' => 'html',
                'value' => static function (Apple $apple): string {
                    return Html::tag('div', '', [
                        'style' => [
                            'height' => '20px',
                            'width' => '60px',
                            'background-color' => $apple->color,
                        ],
                    ]);
                },
            ],
            [
                'attribute' => 'germination_datetime',
                'label' => Yii::t('app/apples', 'Germination datetime'),
                'format' => static fn(int $value, Formatter $formatter): string => $formatter->asDatetime($value),
            ],
            [
                'attribute' => 'fell_datetime',
                'label' => Yii::t('app/apples', 'Fell datetime'),
                'format' => static fn(int|null $value, Formatter $formatter): string => $value !== null
                    ? $formatter->asDatetime($value)
                    : $formatter->format($value, 'text')
            ],
            [
                'attribute' => 'integrity',
                'label' => Yii::t('app/apples', 'Integrity'),
                'value' => static fn(Apple $apple): float =>  $apple->getIntegrityAsPercent()->toFloat()
            ],
            [
                'attribute' => 'state',
                'label' => Yii::t('app/apples', 'State'),
                'format' => static fn(string $value, Formatter $formatter): string => \mb_strtoupper($value),
            ],

            [
                'class' => ActionColumn::class,
                'options' => ['class' => 'w-15'],
                'template' => '<div class="action-group">{fallToGround}{eat}{spoil}{delete}</div>',
                'buttons' => [
                    'fallToGround' => static function (string $url, Apple $apple) use ($appleWorkflowService): string {
                        $isDisabled = !$appleWorkflowService->canFallToGround($apple);

                        return Html::tag('div', Html::a(
                            Yii::t('app/apples', 'Drop to ground'),
                            ['/apple/fall-to-ground', 'id' => $apple->id],
                            [
                                'data' => ['method' => 'post'],
                                'class' => 'btn btn-info btn-sm w-100 ' . ($isDisabled ? 'disabled' : ''),
                            ]
                        ), ['class' => 'action-group__item-wrap']);
                    },
                    'eat' => function (string $url, Apple $apple) use ($appleWorkflowService): string {
                        $isDisabled = !$appleWorkflowService->canEat($apple, 1);

                        return Html::tag('div', $this->render('@backend/views/apple/_eat_form', [
                            'apple' => $apple,
                            'isDisabled' => $isDisabled,
                            'minPieceSize' => 0.01,
                            'maxPieceSize' => $apple->getIntegrityAsPercent()->toFloat(),
                        ], $this), ['class' => 'action-group__item-wrap']);
                    },
                    'spoil' => static function (string $url, Apple $apple) use ($appleWorkflowService): string {
                        $isDisabled = !$appleWorkflowService->canSpoil($apple);

                        return Html::tag('div', Html::a(
                            Yii::t('app/apples', 'To spoiled'),
                            ['/apple/spoil', 'id' => $apple->id],
                            [
                                'data' => ['method' => 'post'],
                                'class' => 'btn btn-warning btn-sm w-100 ' . ($isDisabled ? 'disabled' : '')
                            ]
                        ), ['class' => 'action-group__item-wrap']);
                    },
                    'delete' => function (string $url, Apple $apple) use ($appleWorkflowService): string {
                        $isDisabled = !$appleWorkflowService->canDelete($apple);

                        return Html::tag('div', Html::a(
                            Yii::t('app/apples', 'Delete'),
                            ['/apple/delete', 'id' => $apple->id],
                            [
                                'data' => ['method' => 'delete'],
                                'class' => 'btn btn-danger btn-sm w-100 ' . ($isDisabled ? 'disabled' : '')
                            ]
                        ), ['class' => 'action-group__item-wrap']);
                    },
                ],
            ],
        ],
    ]) ?>
</div>
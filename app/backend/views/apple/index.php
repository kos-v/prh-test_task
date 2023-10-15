<?php

use common\models\Apple;
use yii\i18n\Formatter;
use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\BaseDataProvider $dataProvider */

$this->title = Yii::t('app/apples', 'Apples');
?>
<div class="apple-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget(config: [
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

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
            ],
            [
                'attribute' => 'state',
                'label' => Yii::t('app/apples', 'State'),
                'format' => static fn(string $value, Formatter $formatter): string => \mb_strtoupper($value),
            ],
        ],
    ]); ?>
</div>
<?php

use common\models\Apple;
use yii\helpers\Html;

/** @var Apple $apple */
/** @var bool $isDisabled */
/** @var float $minPieceSize */
/** @var float $maxPieceSize */

?>
<?= Html::beginForm(['/apple/eat', 'id' => $apple->id]) ?>
    <div class="row g-2">
        <div class="col-6">
            <?= Html::input('number', 'pieceSize', $maxPieceSize, [
                'min' => $minPieceSize,
                'max' => $maxPieceSize,
                'step' => 0.01,
                'required' => true,
                'class' => 'form-control form-control-sm',
                'disabled' => $isDisabled,
            ]) ?>
        </div>
        <div class="col-6">
            <?= Html::submitButton(Yii::t('app/apples', 'Eat'), [
                'class' => 'btn btn-success btn-sm w-100 ' . ($isDisabled ? 'disabled' : '')
            ]) ?>
        </div>
    </div>
<?= Html::endForm() ?>
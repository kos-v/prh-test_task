<?php

declare(strict_types=1);

namespace backend\forms;

use yii\base\Model;

class EatForm extends Model
{
    public ?float $pieceSize = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['pieceSize', 'required'],
            ['pieceSize', 'double'],
        ];
    }
}

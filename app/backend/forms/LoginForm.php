<?php

declare(strict_types=1);

namespace backend\forms;

use yii\base\Model;

class LoginForm extends Model
{
    public ?string $username = null;
    public ?string $password = null;
    public ?bool $rememberMe = true;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['username', 'password'], 'required'],
            [['username', 'password'], 'string'],
            ['rememberMe', 'boolean'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace backend\services;

use common\models\Apple;
use UnexpectedValueException;

class AppleWorkflowService
{
    private const STATUS_ON_TREE = 'on_tree';

    public function canInit(Apple $apple): bool
    {
        return $apple->id === null;
    }

    public function doInit(Apple $apple): Apple
    {
        if (!$this->canInit($apple)) {
            throw new UnexpectedValueException('The apple cannot be initialized');
        }

        $apple->state = self::STATUS_ON_TREE;

        return $apple;
    }
}

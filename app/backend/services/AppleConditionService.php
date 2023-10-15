<?php

declare(strict_types=1);

namespace backend\services;

use common\models\Apple;

class AppleConditionService
{
    public function applyGreatCondition(Apple $apple): Apple
    {
        $apple->integrity = 100;
        return $apple;
    }
}

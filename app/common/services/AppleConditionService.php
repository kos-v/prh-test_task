<?php

declare(strict_types=1);

namespace common\services;

use common\models\Apple;
use common\valueObjects\Percent;

class AppleConditionService
{
    public function applyGreatCondition(Apple $apple): Apple
    {
        $apple->setIntegrityByPercent(Percent::makeFromFloat(100.0));
        return $apple;
    }
}

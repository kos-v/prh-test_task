<?php

declare(strict_types=1);

namespace backend\services;

use common\models\Apple;

use function mt_rand;
use function time;

class AppleGerminationService
{
    public function germinate(Apple $apple): Apple
    {
        $currentTime = time();
        $apple->germination_datetime = mt_rand($currentTime, $currentTime + 60 * 5);

        return $apple;
    }
}

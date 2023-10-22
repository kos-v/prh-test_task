<?php

declare(strict_types=1);

namespace common\services;

use common\models\Apple;

use function count;
use function mt_rand;

class AppleDnaService
{
    private const ALLOWED_COLORS = ['#5cb85c', '#d9534f', '#eef04e'];

    public function programGenes(Apple $apple): Apple
    {
        $apple->color = self::ALLOWED_COLORS[mt_rand(0, count(self::ALLOWED_COLORS) - 1)];
        return $apple;
    }
}

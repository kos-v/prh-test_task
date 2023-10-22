<?php

declare(strict_types=1);

namespace common\services;

use common\models\Apple;
use common\repositories\AppleRepository;
use InvalidArgumentException;

use function mt_rand;

class AppleGenerateService
{
    public function __construct(
        private readonly AppleDnaService $appleDnaService,
        private readonly AppleGerminationService $appleGerminationService,
        private readonly AppleConditionService $appleConditionService,
        private readonly AppleWorkflowService $appleWorkflowService,
        private readonly AppleRepository $appleRepository,
    ) {
    }

    public function regenerateAllByRandomQty(int $minQty = 3, int $maxQty = 30): void
    {
        if ($minQty < 1 || $maxQty < 1) {
            throw new InvalidArgumentException('The range of the number of values must be positive');
        }

        $this->appleRepository->removeAllWithSaveKeyIndex();
        $this->appleRepository->createList($this->generateApples(mt_rand($minQty, $maxQty)));
    }

    private function generateApples(int $count): iterable
    {
        for ($i = 0; $i < $count; $i++) {
            $apple = $this->appleDnaService->programGenes(new Apple());
            $apple = $this->appleGerminationService->germinate($apple);
            $apple = $this->appleConditionService->applyGreatCondition($apple);
            $apple = $this->appleWorkflowService->init($apple);

            yield $apple;
        }
    }
}

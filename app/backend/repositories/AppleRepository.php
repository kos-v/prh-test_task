<?php

declare(strict_types=1);

namespace backend\repositories;

use common\models\Apple;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\db\Connection;
use yii\helpers\ArrayHelper;

use function count;

use const SORT_ASC;

class AppleRepository
{
    public function __construct(private readonly Connection $db)
    {
    }

    /**
     * TODO: The method returns the entire amount of data, without using pagination and sorting.
     *       Pagination and sorting parameters must be passed through method parameters,
     *       I didn't have time to create abstractions and implementations.
     */
    public function findAllThroughDataProvider(): DataProviderInterface
    {
        return new ActiveDataProvider([
            'db' => $this->db,
            'query' => Apple::find(),
            'pagination' => false,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_ASC,
                ]
            ],
        ]);
    }

    public function createList(iterable $apples): void
    {
        $appleBatch = [];
        $appleBatchMaxSize = 100;

        foreach ($apples as $apple) {
            $appleBatch[] = $apple;

            if (count($appleBatch) === $appleBatchMaxSize) {
                $this->insertBatch($appleBatch);
                $appleBatch = [];
            }
        }

        if (count($appleBatch)) {
            $this->insertBatch($appleBatch);
        }
    }

    public function removeAllWithSaveKeyIndex(): int
    {
        return $this->db
            ->createCommand()
            ->delete(Apple::tableName())
            ->execute();
    }

    private function insertBatch(array $appleBatch): void
    {
        if (!count($appleBatch)) {
            return;
        }

        $this->db->createCommand()->batchInsert(
            Apple::tableName(),
            (new Apple())->attributes(),
            ArrayHelper::getColumn($appleBatch, 'attributes')
        )->execute();
    }
}

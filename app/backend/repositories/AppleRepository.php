<?php

namespace backend\repositories;

use common\models\Apple;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\db\Connection;

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
}

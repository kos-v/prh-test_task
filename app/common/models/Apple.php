<?php

declare(strict_types=1);

namespace common\models;

use common\valueObjects\Percent;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $color
 * @property int $germination_datetime
 * @property int|null $fell_datetime
 * @property int $integrity
 * @property string $state
 */
class Apple extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'apple';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['color', 'germination_datetime', 'integrity', 'state'], 'required'],
            [['germination_datetime', 'fell_datetime', 'integrity'], 'integer'],
            [['color'], 'string', 'max' => 7],
            [['state'], 'string', 'max' => 255],
        ];
    }

    public function getIntegrityAsPercent(): Percent
    {
        return new Percent($this->integrity ?? 0);
    }

    public function setIntegrityByPercent(Percent $percent): void
    {
        $this->integrity = $percent->toBankingFormat();
    }
}

<?php

declare(strict_types=1);

namespace common\valueObjects;

class Percent
{
    private const FRACTIONAL_LEN = 2;

    public function __construct(private readonly int $bankingValue)
    {

    }

    public static function makeFromFloat(float $value): static
    {
        return new static((int) ($value * 10 ** self::FRACTIONAL_LEN));
    }

    public function toBankingFormat(): int
    {
        return $this->bankingValue;
    }

    public function toFloat(): float
    {
        return $this->bankingValue / 10 ** self::FRACTIONAL_LEN;
    }
}

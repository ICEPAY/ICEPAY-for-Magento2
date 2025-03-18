<?php

declare(strict_types=1);

namespace Icepay\Payment\Data;

class Amount
{
    public function __construct(
        public readonly float $value,
        public readonly string $currency
    ) {}
}

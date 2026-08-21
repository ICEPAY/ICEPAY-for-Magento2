<?php

declare(strict_types=1);

namespace Icepay\Payment\Service\Icepay;

final class AmountInCents
{
    public function fromMajorUnits(float $amount): int
    {
        return (int) round($amount * 100);
    }
}

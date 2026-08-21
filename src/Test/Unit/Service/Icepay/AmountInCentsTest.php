<?php

declare(strict_types=1);

namespace Icepay\Payment\Test\Unit\Service\Icepay;

use Icepay\Payment\Service\Icepay\AmountInCents;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AmountInCentsTest extends TestCase
{
    #[Test]
    public function itConvertsMajorUnitsToCents(): void
    {
        $cents = (new AmountInCents())->fromMajorUnits(12.34);

        $this->assertSame(1234, $cents);
    }

    #[Test]
    public function itRoundsFloatingPointArtifactsToTheNearestCent(): void
    {
        $amountInCents = new AmountInCents();

        $this->assertSame(1999, $amountInCents->fromMajorUnits(19.99));
        $this->assertSame(29, $amountInCents->fromMajorUnits(0.29));
        $this->assertSame(5798, $amountInCents->fromMajorUnits(57.98));
    }

    #[Test]
    public function itConvertsWholeAmountsWithoutCents(): void
    {
        $cents = (new AmountInCents())->fromMajorUnits(100.0);

        $this->assertSame(10000, $cents);
    }
}

<?php

declare(strict_types=1);

namespace Icepay\Payment\Test\Fakes;

use Icepay\Payment\Service\Icepay\GetEnabledPaymentMethods;

class FakeGetEnabledPaymentMethods extends GetEnabledPaymentMethods
{
    private int $executeCallCount = 0;

    private function __construct(
        private readonly ?array $enabledMethodIds,
    ) {}

    public static function returning(?array $methodIds): self
    {
        return new self($methodIds);
    }

    public function execute(): ?array
    {
        $this->executeCallCount++;

        return $this->enabledMethodIds;
    }

    public function executeCallCount(): int
    {
        return $this->executeCallCount;
    }
}

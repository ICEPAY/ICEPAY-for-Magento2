<?php

declare(strict_types=1);

namespace Icepay\Payment\Test\Fakes;

use Icepay\Payment\Model\IcepayMethod;

class FakeIcepayMethod extends IcepayMethod
{
    public function __construct(
        private readonly string $methodCode,
    ) {}

    public function getCode(): string
    {
        return $this->methodCode;
    }
}

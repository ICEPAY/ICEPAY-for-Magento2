<?php

declare(strict_types=1);

namespace Icepay\Payment\Data;

class PaymentMethodCollection
{
    public function __construct(
        public readonly array $paymentMethods
    ) {}
}

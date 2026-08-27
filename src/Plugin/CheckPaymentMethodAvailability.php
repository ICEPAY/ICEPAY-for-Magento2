<?php

declare(strict_types=1);

namespace Icepay\Payment\Plugin;

use Icepay\Payment\Gateway\Commands\PaymentMethod;
use Icepay\Payment\Model\IcepayMethod;
use Icepay\Payment\Service\Icepay\GetEnabledPaymentMethods;

class CheckPaymentMethodAvailability
{
    public function __construct(
        private readonly GetEnabledPaymentMethods $getEnabledPaymentMethods,
    ) {}

    public function afterIsAvailable(IcepayMethod $subject, bool $isAvailable): bool
    {
        if ($isAvailable === false) {
            return false;
        }

        $enabledMethodIds = $this->getEnabledPaymentMethods->execute();

        if ($enabledMethodIds === null) {
            return true;
        }

        $method = PaymentMethod::tryFromCode($subject->getCode());

        return $method !== null && in_array($method->value, $enabledMethodIds, true);
    }
}

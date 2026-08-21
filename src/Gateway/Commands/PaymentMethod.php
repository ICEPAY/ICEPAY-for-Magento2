<?php

declare(strict_types=1);

namespace Icepay\Payment\Gateway\Commands;

use Magento\Sales\Api\Data\OrderPaymentInterface;

enum PaymentMethod: string
{
    case bancontact = 'bancontact';
    case ideal = 'ideal';
    case onlineueberweisen = 'onlineueberweisen';
    case card = 'card';
    case paypal = 'paypal';
    case EPS = 'eps';
    case SOFORT = 'sofort';

    public static function fromPayment(OrderPaymentInterface $payment): self
    {
        return self::fromCode((string)$payment->getMethod());
    }

    public static function fromCode(string $code): self
    {
        return self::tryFromCode($code)
            ?? throw new \ValueError(sprintf('"%s" is not a valid Icepay payment method code', $code));
    }

    public static function tryFromCode(string $code): ?self
    {
        if ($code === 'icepay_creditcard') {
            return self::card;
        }

        return self::tryFrom(str_replace('icepay_', '', $code));
    }
}

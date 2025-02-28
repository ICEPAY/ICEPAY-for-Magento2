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
        $method = $payment->getMethod();

        if ($method == 'icepay_creditcard') {
            return static::card;
        }

        return static::from(str_replace('icepay_', '', $method));
    }
}

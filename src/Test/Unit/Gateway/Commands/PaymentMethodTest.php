<?php

declare(strict_types=1);

namespace Icepay\Payment\Test\Unit\Gateway\Commands;

use Icepay\Payment\Gateway\Commands\PaymentMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PaymentMethodTest extends TestCase
{
    #[Test]
    #[DataProvider('magentoCodes')]
    public function itMapsTheMagentoCodeToTheIcepayMethod(string $code, PaymentMethod $expected): void
    {
        $this->assertSame($expected, PaymentMethod::fromCode($code));
    }

    public static function magentoCodes(): array
    {
        return [
            'bancontact' => ['icepay_bancontact', PaymentMethod::bancontact],
            'creditcard' => ['icepay_creditcard', PaymentMethod::card],
            'eps' => ['icepay_eps', PaymentMethod::EPS],
            'ideal' => ['icepay_ideal', PaymentMethod::ideal],
            'onlineueberweisen' => ['icepay_onlineueberweisen', PaymentMethod::onlineueberweisen],
            'paypal' => ['icepay_paypal', PaymentMethod::paypal],
            'sofort' => ['icepay_sofort', PaymentMethod::SOFORT],
        ];
    }

    #[Test]
    public function itThrowsForACodeThatIsNotAnIcepayMethod(): void
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('"checkmo" is not a valid Icepay payment method code');

        PaymentMethod::fromCode('checkmo');
    }

    #[Test]
    public function itThrowsForAnEmptyCode(): void
    {
        $this->expectException(\ValueError::class);

        PaymentMethod::fromCode('');
    }

    #[Test]
    public function itReturnsNullForACodeThatIsNotAnIcepayMethod(): void
    {
        $this->assertNull(PaymentMethod::tryFromCode('checkmo'));
    }
}

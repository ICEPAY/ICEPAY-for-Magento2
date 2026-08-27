<?php

declare(strict_types=1);

namespace Icepay\Payment\Test\Unit\Plugin;

use Icepay\Payment\Plugin\CheckPaymentMethodAvailability;
use Icepay\Payment\Test\Fakes\FakeGetEnabledPaymentMethods;
use Icepay\Payment\Test\Fakes\FakeIcepayMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CheckPaymentMethodAvailabilityTest extends TestCase
{
    #[Test]
    public function itLeavesTheMethodUnavailableWithoutAskingIcepayWhenMagentoAlreadyDisabledIt(): void
    {
        $getEnabledPaymentMethods = FakeGetEnabledPaymentMethods::returning(['ideal']);
        $plugin = new CheckPaymentMethodAvailability($getEnabledPaymentMethods);

        $isAvailable = $plugin->afterIsAvailable(new FakeIcepayMethod('icepay_ideal'), false);

        $this->assertFalse($isAvailable);
        $this->assertSame(0, $getEnabledPaymentMethods->executeCallCount());
    }

    #[Test]
    public function itKeepsTheMethodAvailableWhenIcepayHasItEnabled(): void
    {
        $plugin = new CheckPaymentMethodAvailability(FakeGetEnabledPaymentMethods::returning(['ideal', 'paypal']));

        $isAvailable = $plugin->afterIsAvailable(new FakeIcepayMethod('icepay_ideal'), true);

        $this->assertTrue($isAvailable);
    }

    #[Test]
    public function itMakesTheMethodUnavailableWhenIcepayDoesNotHaveItEnabled(): void
    {
        $plugin = new CheckPaymentMethodAvailability(FakeGetEnabledPaymentMethods::returning(['ideal', 'paypal']));

        $isAvailable = $plugin->afterIsAvailable(new FakeIcepayMethod('icepay_sofort'), true);

        $this->assertFalse($isAvailable);
    }

    #[Test]
    public function itMatchesTheCreditcardCodeWithTheCardMethodId(): void
    {
        $plugin = new CheckPaymentMethodAvailability(FakeGetEnabledPaymentMethods::returning(['card']));

        $isAvailable = $plugin->afterIsAvailable(new FakeIcepayMethod('icepay_creditcard'), true);

        $this->assertTrue($isAvailable);
    }

    #[Test]
    public function itKeepsTheMethodAvailableWhenTheEnabledMethodsCouldNotBeRetrieved(): void
    {
        $plugin = new CheckPaymentMethodAvailability(FakeGetEnabledPaymentMethods::returning(null));

        $isAvailable = $plugin->afterIsAvailable(new FakeIcepayMethod('icepay_sofort'), true);

        $this->assertTrue($isAvailable);
    }

    #[Test]
    public function itMakesTheMethodUnavailableWhenTheCodeIsNotAnIcepayMethod(): void
    {
        $plugin = new CheckPaymentMethodAvailability(FakeGetEnabledPaymentMethods::returning(['ideal']));

        $isAvailable = $plugin->afterIsAvailable(new FakeIcepayMethod('checkmo'), true);

        $this->assertFalse($isAvailable);
    }
}

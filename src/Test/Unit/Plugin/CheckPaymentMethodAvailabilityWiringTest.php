<?php

declare(strict_types=1);

namespace Icepay\Payment\Test\Unit\Plugin;

use Icepay\Payment\Model\IcepayMethod;
use Icepay\Payment\Plugin\CheckPaymentMethodAvailability;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CheckPaymentMethodAvailabilityWiringTest extends TestCase
{
    #[Test]
    public function itIsRegisteredAsAPluginOnTheIcepayMethod(): void
    {
        $plugins = simplexml_load_file(dirname(__DIR__, 3) . '/etc/di.xml')->xpath(
            '//type[@name="Icepay\Payment\Model\IcepayMethod"]'
            . '/plugin[@type="Icepay\Payment\Plugin\CheckPaymentMethodAvailability"]'
        );

        $this->assertCount(1, $plugins);
        $this->assertNotSame('true', (string)($plugins[0]['disabled'] ?? ''));
    }

    #[Test]
    public function itInterceptsAMethodThatExistsOnTheIcepayMethod(): void
    {
        $this->assertTrue(method_exists(CheckPaymentMethodAvailability::class, 'afterIsAvailable'));
        $this->assertTrue(method_exists(IcepayMethod::class, 'isAvailable'));
    }
}

<?php

declare(strict_types=1);

namespace Icepay\Payment\Test\Unit\Service\Icepay;

use ICEPAY\Checkout\Exceptions\InvalidSignature;
use ICEPAY\Checkout\Models\Status;
use Icepay\Payment\Service\Icepay\HandlePostback;
use Icepay\Payment\Test\Fakes\FakeConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class HandlePostbackTest extends TestCase
{
    private const BODY = '{"key":"abc123","status":"completed","reference":"000000001",'
        . '"amount":{"value":1999,"currency":"eur"}}';

    #[Test]
    public function itReturnsTheCheckoutForACorrectlySignedBody(): void
    {
        $service = new HandlePostback(FakeConfig::withCredentials());

        $checkout = $service->execute(self::BODY, $this->sign(self::BODY, 'secret'));

        $this->assertSame('abc123', $checkout->key);
        $this->assertSame(Status::completed, $checkout->status);
        $this->assertSame('000000001', $checkout->reference);
    }

    #[Test]
    public function itRejectsATamperedBody(): void
    {
        $service = new HandlePostback(FakeConfig::withCredentials());
        $tamperedBody = str_replace('1999', '1', self::BODY);

        $this->expectException(InvalidSignature::class);

        $service->execute($tamperedBody, $this->sign(self::BODY, 'secret'));
    }

    #[Test]
    public function itRejectsABodySignedWithAnotherSecret(): void
    {
        $service = new HandlePostback(FakeConfig::withCredentials());

        $this->expectException(InvalidSignature::class);

        $service->execute(self::BODY, $this->sign(self::BODY, 'another-secret'));
    }

    #[Test]
    public function itRejectsEveryBodyWhenNoMerchantSecretIsConfigured(): void
    {
        $service = new HandlePostback(FakeConfig::withoutCredentials());

        $this->expectException(InvalidSignature::class);

        $service->execute(self::BODY, $this->sign(self::BODY, ''));
    }

    private function sign(string $body, string $secret): string
    {
        return base64_encode(hash_hmac('sha256', $body, $secret, true));
    }
}

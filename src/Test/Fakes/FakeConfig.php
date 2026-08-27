<?php

declare(strict_types=1);

namespace Icepay\Payment\Test\Fakes;

use Icepay\Payment\Config;

class FakeConfig extends Config
{
    private function __construct(
        private readonly string $merchantId,
        private readonly ?string $merchantSecret,
    ) {}

    public static function withCredentials(): self
    {
        return new self('34914', 'secret');
    }

    public static function withoutCredentials(): self
    {
        return new self('', null);
    }

    public function getMerchantId(?int $storeId = null): string
    {
        return $this->merchantId;
    }

    public function getMerchantSecret(?int $storeId = null): ?string
    {
        return $this->merchantSecret;
    }
}

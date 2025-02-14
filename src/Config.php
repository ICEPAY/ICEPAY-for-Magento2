<?php

declare(strict_types=1);

namespace Icepay\Payment;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Config
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly EncryptorInterface $encryptor,
    ) {}

    private function getValue(string $path, ?int $storeId = null): mixed
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $storeId
        );
    }

    public function getMerchantId(?int $storeId = null): string
    {
        return (string) $this->getValue('payment/icepay/merchant_id', $storeId);
    }

    public function getMerchantSecret(?int $storeId = null): ?string
    {
        $value = $this->getValue('payment/icepay/merchant_secret', $storeId);
        return $value ? $this->encryptor->decrypt($value) : null;
    }
}

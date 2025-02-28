<?php

declare(strict_types=1);

namespace Icepay\Payment\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config implements ConfigInterface
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly \Icepay\Payment\Config $icepayConfig,
        private readonly string $methodCode,
    ) {}

    public function getValue($field, $storeId = null)
    {
        $storeId = $storeId == null ? null : (int)$storeId;
        if ($field == 'active' && !$this->hasRequiredFieldsSet($storeId)) {
            return false;
        }

        $methodCode = str_replace('icepay_', '', $this->methodCode);
        return $this->scopeConfig->getValue(
            sprintf('payment/icepay/%s/%s', $methodCode, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    private function hasRequiredFieldsSet(?int $storeId): bool
    {
        if (!$this->icepayConfig->isActive($storeId)) {
            return false;
        }

        if (!$this->icepayConfig->getMerchantId($storeId)) {
            return false;
        }

        if (!$this->icepayConfig->getMerchantSecret($storeId)) {
            return false;
        }

        return true;
    }

    // Required by the interface but never called.
    public function setMethodCode($methodCode)
    {
        return;
    }

    // Required by the interface but never called.
    public function setPathPattern($pathPattern)
    {
        return;
    }
}

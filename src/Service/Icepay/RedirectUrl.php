<?php

declare(strict_types=1);

namespace Icepay\Payment\Service\Icepay;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;

class RedirectUrl
{
    public function __construct(
        private readonly UrlInterface $url,
        private readonly EncryptorInterface $encryptor,
    ) {}

    public function execute(OrderInterface $order): string
    {
        $incrementId = $order->getIncrementId();
        $params = [
            'order_id' => base64_encode($this->encryptor->encrypt((string)$incrementId)),
            '_secure' => true,
        ];

        return $this->url->getUrl('icepay/payment/process', $params);
    }
}

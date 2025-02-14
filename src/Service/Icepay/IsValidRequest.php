<?php

declare(strict_types=1);

namespace Icepay\Payment\Service\Icepay;

use Icepay\Payment\Config;
use Magento\Framework\App\RequestInterface;

class IsValidRequest
{
    public function __construct(
        private readonly Config $config,
        private readonly RequestInterface $request,
    ) {}

    public function execute(): bool
    {
        $merchantSecret = $this->config->getMerchantSecret();
        $signature = $this->request->getHeader('ICEPAY-Signature');
        $body = $this->request->getContent();

        $calculatedSignature = base64_encode(hash_hmac('sha256', $body, $merchantSecret, true));

        return $signature === $calculatedSignature;
    }
}

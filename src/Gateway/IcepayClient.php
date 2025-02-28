<?php

declare(strict_types=1);

namespace Icepay\Payment\Gateway;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use Icepay\Payment\Config;

class IcepayClient
{
    public function __construct(
        private readonly ClientFactory $clientFactory,
        private readonly Config $config,
    ) {}


    public function create(): Client
    {
        $merchantId = $this->config->getMerchantId();
        $merchantSecret = $this->config->getMerchantSecret();

        return $this->clientFactory->create([
            'config' => [
                'base_uri' => 'https://checkout.icepay.com/api/',
                'auth' => [$merchantId, $merchantSecret],
            ],
        ]);
    }
}

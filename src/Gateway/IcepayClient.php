<?php

declare(strict_types=1);

namespace Icepay\Payment\Gateway;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\RequestOptions;
use ICEPAY\Checkout\CheckoutClient;
use ICEPAY\Checkout\HttpClient;
use Icepay\Payment\Config;

class IcepayClient
{
    public function __construct(
        private readonly ClientFactory $clientFactory,
        private readonly HttpFactory $httpFactory,
        private readonly Config $config,
    ) {}

    public function create(): CheckoutClient
    {
        return $this->checkoutClient(
            [],
            $this->config->getMerchantId(),
            (string) $this->config->getMerchantSecret()
        );
    }

    public function createWithTimeouts(int $connectTimeoutSeconds, int $requestTimeoutSeconds): CheckoutClient
    {
        return $this->checkoutClient(
            [
                RequestOptions::CONNECT_TIMEOUT => $connectTimeoutSeconds,
                RequestOptions::TIMEOUT => $requestTimeoutSeconds,
            ],
            $this->config->getMerchantId(),
            (string) $this->config->getMerchantSecret()
        );
    }

    public function createWithCredentials(string $merchantId, string $merchantSecret): CheckoutClient
    {
        return $this->checkoutClient([], $merchantId, $merchantSecret);
    }

    private function checkoutClient(array $guzzleConfig, string $merchantId, string $merchantSecret): CheckoutClient
    {
        $guzzle = $this->clientFactory->create(['config' => $guzzleConfig]);
        $httpClient = new HttpClient($guzzle, $this->httpFactory, $this->httpFactory);

        return (new CheckoutClient($httpClient))->withAuthorization($merchantId, $merchantSecret);
    }
}

<?php

declare(strict_types=1);

namespace Icepay\Payment\Service\Icepay;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Icepay\Payment\Config;
use Icepay\Payment\Gateway\IcepayClient;
use Magento\Framework\App\Cache\Type\Config as ConfigCache;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class GetEnabledPaymentMethods
{
    private const ENDPOINT = 'payments/methods';
    private const CACHE_KEY = 'icepay_enabled_payment_methods';
    private const CACHE_LIFETIME = 3600;
    private const FAILURE_CACHE_LIFETIME = 300;
    private const CONNECT_TIMEOUT_SECONDS = 2;
    private const REQUEST_TIMEOUT_SECONDS = 5;

    private bool $isLoaded = false;
    private ?array $methodIds = null;

    public function __construct(
        private readonly IcepayClient $client,
        private readonly Config $config,
        private readonly CacheInterface $cache,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
    ) {}

    public function execute(): ?array
    {
        if ($this->isLoaded === false) {
            $this->methodIds = $this->load();
            $this->isLoaded = true;
        }

        return $this->methodIds;
    }

    private function load(): ?array
    {
        if ($this->hasCredentials() === false) {
            return null;
        }

        $cached = $this->loadFromCache();

        if ($cached !== false) {
            return $cached;
        }

        $methodIds = $this->fetch();
        $this->saveToCache($methodIds);

        return $methodIds;
    }

    private function loadFromCache(): array|null|false
    {
        $cached = $this->cache->load(self::CACHE_KEY);

        if ($cached === false) {
            return false;
        }

        try {
            $methodIds = $this->serializer->unserialize($cached);
        } catch (\InvalidArgumentException $exception) {
            $this->logger->error(
                'The cached Icepay enabled payment methods are corrupt, fetching them again',
                ['exception' => $exception]
            );

            return false;
        }

        if ($methodIds !== null && is_array($methodIds) === false) {
            return false;
        }

        return $methodIds;
    }

    private function saveToCache(?array $methodIds): void
    {
        $this->cache->save(
            $this->serializer->serialize($methodIds),
            self::CACHE_KEY,
            [ConfigCache::CACHE_TAG],
            $methodIds === null ? self::FAILURE_CACHE_LIFETIME : self::CACHE_LIFETIME
        );
    }

    private function fetch(): ?array
    {
        try {
            $response = $this->client->create()->get(self::ENDPOINT, [
                RequestOptions::CONNECT_TIMEOUT => self::CONNECT_TIMEOUT_SECONDS,
                RequestOptions::TIMEOUT => self::REQUEST_TIMEOUT_SECONDS,
            ]);
            $methods = $this->serializer->unserialize((string)$response->getBody());
        } catch (GuzzleException | \InvalidArgumentException $exception) {
            $this->logger->error(
                'Unable to retrieve the payment methods that are enabled at Icepay',
                ['exception' => $exception]
            );

            return null;
        }

        if (is_array($methods) === false) {
            $this->logger->error(
                'Icepay returned an unexpected response for ' . self::ENDPOINT . ', expected a list of methods',
                ['response' => $methods]
            );

            return null;
        }

        return array_column($methods, 'id');
    }

    private function hasCredentials(): bool
    {
        return $this->config->getMerchantId() !== '' && ($this->config->getMerchantSecret() ?? '') !== '';
    }
}

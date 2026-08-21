<?php

declare(strict_types=1);

namespace Icepay\Payment\Service\Icepay;

use ICEPAY\Checkout\Exceptions\ApiException;
use ICEPAY\Checkout\Models\Response\PaymentMethod;
use Icepay\Payment\Config;
use Icepay\Payment\Gateway\IcepayClient;
use Magento\Framework\App\Cache\Type\Config as ConfigCache;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class GetEnabledPaymentMethods
{
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
            $methods = $this->client
                ->createWithTimeouts(self::CONNECT_TIMEOUT_SECONDS, self::REQUEST_TIMEOUT_SECONDS)
                ->getPaymentMethods();
        } catch (ApiException | \JsonException | \Error $exception) {
            $this->logger->error(
                'Unable to retrieve the payment methods that are enabled at Icepay',
                ['exception' => $exception]
            );

            return null;
        }

        return array_map(static fn (PaymentMethod $method): string => $method->id, $methods);
    }

    private function hasCredentials(): bool
    {
        return $this->config->getMerchantId() !== '' && ($this->config->getMerchantSecret() ?? '') !== '';
    }
}

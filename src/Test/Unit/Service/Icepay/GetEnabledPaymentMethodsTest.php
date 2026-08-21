<?php

declare(strict_types=1);

namespace Icepay\Payment\Test\Unit\Service\Icepay;

use GuzzleHttp\RequestOptions;
use Icepay\Payment\Config;
use Icepay\Payment\Service\Icepay\GetEnabledPaymentMethods;
use Icepay\Payment\Test\Fakes\FakeCache;
use Icepay\Payment\Test\Fakes\FakeConfig;
use Icepay\Payment\Test\Fakes\FakeIcepayClient;
use Icepay\Payment\Test\Fakes\FakeLogger;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetEnabledPaymentMethodsTest extends TestCase
{
    private const RESPONSE_BODY = '[{"id":"ideal","logo":"","description":"iDEAL | Wero"},'
        . '{"id":"paypal","logo":"","description":"PayPal"}]';
    private const CACHE_KEY = 'icepay_enabled_payment_methods';

    #[Test]
    public function itReturnsTheMethodIdsThatAreEnabledAtIcepay(): void
    {
        $methodIds = $this->service()->execute();

        $this->assertSame(['ideal', 'paypal'], $methodIds);
    }

    #[Test]
    public function itReturnsNullWithoutCallingIcepayWhenTheCredentialsAreMissing(): void
    {
        $client = FakeIcepayClient::unused();

        $methodIds = $this->service(client: $client, config: FakeConfig::withoutCredentials())->execute();

        $this->assertNull($methodIds);
        $this->assertSame(0, $client->requestCount());
    }

    #[Test]
    public function itReturnsNullAndLogsAnErrorWhenIcepayCannotBeReached(): void
    {
        $logger = new FakeLogger();

        $methodIds = $this->service(client: FakeIcepayClient::unreachable(), logger: $logger)->execute();

        $this->assertNull($methodIds);
        $this->assertCount(1, $logger->errorMessages());
    }

    #[Test]
    public function itReturnsNullAndLogsAnErrorWhenTheResponseIsNotAList(): void
    {
        $logger = new FakeLogger();

        $methodIds = $this->service(client: FakeIcepayClient::returning('"nonsense"'), logger: $logger)->execute();

        $this->assertNull($methodIds);
        $this->assertCount(1, $logger->errorMessages());
    }

    #[Test]
    public function itStoresTheMethodIdsInTheCacheForAnHour(): void
    {
        $cache = new FakeCache();

        $this->service(cache: $cache)->execute();

        $this->assertSame('["ideal","paypal"]', $cache->storedValue(self::CACHE_KEY));
        $this->assertSame(3600, $cache->storedLifetime(self::CACHE_KEY));
    }

    #[Test]
    public function itBrieflyCachesAFailureSoItDoesNotHammerAnUnreachableIcepay(): void
    {
        $cache = new FakeCache();

        $this->service(client: FakeIcepayClient::unreachable(), cache: $cache)->execute();

        $this->assertSame('null', $cache->storedValue(self::CACHE_KEY));
        $this->assertSame(300, $cache->storedLifetime(self::CACHE_KEY));
    }

    #[Test]
    public function itReturnsTheCachedMethodIdsWithoutCallingIcepay(): void
    {
        $client = FakeIcepayClient::unused();
        $cache = (new FakeCache())->withEntry(self::CACHE_KEY, '["bancontact"]');

        $methodIds = $this->service(client: $client, cache: $cache)->execute();

        $this->assertSame(['bancontact'], $methodIds);
        $this->assertSame(0, $client->requestCount());
    }

    #[Test]
    public function itReturnsNullWithoutCallingIcepayWhenAFailureWasCached(): void
    {
        $client = FakeIcepayClient::unused();
        $cache = (new FakeCache())->withEntry(self::CACHE_KEY, 'null');

        $methodIds = $this->service(client: $client, cache: $cache)->execute();

        $this->assertNull($methodIds);
        $this->assertSame(0, $client->requestCount());
    }

    #[Test]
    public function itFetchesFromIcepayWhenTheCachedValueIsCorrupt(): void
    {
        $logger = new FakeLogger();
        $cache = (new FakeCache())->withEntry(self::CACHE_KEY, '{corrupt');

        $methodIds = $this->service(cache: $cache, logger: $logger)->execute();

        $this->assertSame(['ideal', 'paypal'], $methodIds);
        $this->assertSame('["ideal","paypal"]', $cache->storedValue(self::CACHE_KEY));
        $this->assertCount(1, $logger->errorMessages());
    }

    #[Test]
    public function itFetchesFromIcepayWhenTheCachedValueIsNotAList(): void
    {
        $cache = (new FakeCache())->withEntry(self::CACHE_KEY, '"nonsense"');

        $methodIds = $this->service(cache: $cache)->execute();

        $this->assertSame(['ideal', 'paypal'], $methodIds);
    }

    #[Test]
    public function itCallsIcepayOnlyOnceWhenAskedRepeatedly(): void
    {
        $client = FakeIcepayClient::returning(self::RESPONSE_BODY);
        $service = $this->service(client: $client);

        $service->execute();
        $service->execute();

        $this->assertSame(['ideal', 'paypal'], $service->execute());
        $this->assertSame(1, $client->requestCount());
    }

    #[Test]
    public function itLimitsHowLongTheIcepayRequestMayTakeSoCheckoutCannotHang(): void
    {
        $client = FakeIcepayClient::returning(self::RESPONSE_BODY);

        $this->service(client: $client)->execute();

        $this->assertSame(2, $client->lastRequestOptions()[RequestOptions::CONNECT_TIMEOUT]);
        $this->assertSame(5, $client->lastRequestOptions()[RequestOptions::TIMEOUT]);
    }

    private function service(
        ?FakeIcepayClient $client = null,
        ?Config $config = null,
        ?FakeCache $cache = null,
        ?FakeLogger $logger = null,
    ): GetEnabledPaymentMethods {
        return new GetEnabledPaymentMethods(
            $client ?? FakeIcepayClient::returning(self::RESPONSE_BODY),
            $config ?? FakeConfig::withCredentials(),
            $cache ?? new FakeCache(),
            new Json(),
            $logger ?? new FakeLogger()
        );
    }
}

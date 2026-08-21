<?php

declare(strict_types=1);

namespace Icepay\Payment\Test\Fakes;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use ICEPAY\Checkout\CheckoutClient;
use ICEPAY\Checkout\HttpClient;
use Icepay\Payment\Gateway\IcepayClient;

class FakeIcepayClient extends IcepayClient
{
    private array $requestHistory = [];

    private function __construct(
        private readonly MockHandler $handler,
    ) {}

    public static function returning(string ...$responseBodies): self
    {
        return new self(new MockHandler(array_map(
            static fn (string $responseBody): Response => new Response(200, [], $responseBody),
            $responseBodies
        )));
    }

    public static function unreachable(): self
    {
        return new self(new MockHandler([
            new ConnectException('Connection refused', new Request('GET', 'payments/methods')),
        ]));
    }

    public static function unused(): self
    {
        return new self(new MockHandler([]));
    }

    public function create(): CheckoutClient
    {
        return $this->checkoutClientOnMockedTransport([]);
    }

    public function createWithTimeouts(int $connectTimeoutSeconds, int $requestTimeoutSeconds): CheckoutClient
    {
        return $this->checkoutClientOnMockedTransport([
            RequestOptions::CONNECT_TIMEOUT => $connectTimeoutSeconds,
            RequestOptions::TIMEOUT => $requestTimeoutSeconds,
        ]);
    }

    public function createWithCredentials(string $merchantId, string $merchantSecret): CheckoutClient
    {
        return $this->checkoutClientOnMockedTransport([]);
    }

    public function requestCount(): int
    {
        return count($this->requestHistory);
    }

    public function lastRequestOptions(): array
    {
        return $this->requestHistory[count($this->requestHistory) - 1]['options'] ?? [];
    }

    private function checkoutClientOnMockedTransport(array $guzzleConfig): CheckoutClient
    {
        $stack = HandlerStack::create($this->handler);
        $stack->push(Middleware::history($this->requestHistory));
        $factory = new HttpFactory();
        $guzzle = new Client(['handler' => $stack] + $guzzleConfig);

        return (new CheckoutClient(new HttpClient($guzzle, $factory, $factory)))
            ->withAuthorization('fake-merchant', 'fake-secret');
    }
}

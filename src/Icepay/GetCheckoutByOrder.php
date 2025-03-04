<?php

declare(strict_types=1);

namespace Icepay\Payment\Icepay;

use GuzzleHttp\ClientFactory;
use Icepay\Payment\Config;
use Icepay\Payment\Data\PaymentResponse;
use Icepay\Payment\Data\PaymentResponseBuilder;
use Icepay\Payment\Gateway\IcepayClient;
use Icepay\Payment\Logger;
use Magento\Sales\Api\Data\OrderInterface;

class GetCheckoutByOrder
{
    public function __construct(
        private readonly Logger $logger,
        private readonly IcepayClient $client,
        private readonly PaymentResponseBuilder $paymentResponseFactory,
    ) {}

    public function execute(OrderInterface $order): PaymentResponse
    {
        $payment = $order->getPayment();
        /** @var string $reference */
        $reference = $payment->getAdditionalInformation('icepay_reference');
        if ($reference === null) {
            throw new \InvalidArgumentException('Order does not have an Icepay reference');
        }

        $client = $this->client->create();
        $response = $client->get('https://checkout.icepay.com/api/payments/' . $reference);

        $responseBody = (string)$response->getBody();

        $this->logger->info('Received response for redirect', ['response' => $responseBody]);

        return $this->paymentResponseFactory->createFromJson($responseBody);
    }
}

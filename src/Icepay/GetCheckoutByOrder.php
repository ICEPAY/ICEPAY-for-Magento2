<?php

declare(strict_types=1);

namespace Icepay\Payment\Icepay;

use GuzzleHttp\ClientFactory;
use Icepay\Payment\Config;
use Icepay\Payment\Data\PaymentResponse;
use Icepay\Payment\Data\PaymentResponseFactory;
use Magento\Sales\Api\Data\OrderInterface;

class GetCheckoutByOrder
{
    public function __construct(
        private readonly Config $config,
        private readonly ClientFactory $clientFactory,
        private readonly PaymentResponseFactory $paymentResponseFactory,
    ) {}

    public function execute(OrderInterface $order): PaymentResponse
    {
        $payment = $order->getPayment();
        /** @var string $reference */
        $reference = $payment->getAdditionalInformation('icepay_reference');
        if ($reference === null) {
            throw new \InvalidArgumentException('Order does not have an Icepay reference');
        }

        $merchantId = $this->config->getMerchantId();
        $merchantSecret = $this->config->getMerchantSecret();

        /** @var \GuzzleHttp\Client $client */
        $client = $this->clientFactory->create();
        $response = $client->get('https://checkout.icepay.com/api/payments/' . $reference, [
            'auth' => [$merchantId, $merchantSecret],
        ]);

        $responseBody = (string)$response->getBody();

        return $this->paymentResponseFactory->createFromJson($responseBody);
    }
}

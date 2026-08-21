<?php

declare(strict_types=1);

namespace Icepay\Payment\Icepay;

use ICEPAY\Checkout\Models\Response\Checkout;
use Icepay\Payment\Gateway\IcepayClient;
use Icepay\Payment\Logger;
use Magento\Sales\Api\Data\OrderInterface;

class GetCheckoutByOrder
{
    public function __construct(
        private readonly Logger $logger,
        private readonly IcepayClient $client,
    ) {}

    public function execute(OrderInterface $order): Checkout
    {
        $payment = $order->getPayment();
        /** @var string $reference */
        $reference = $payment->getAdditionalInformation('icepay_reference');
        if ($reference === null) {
            throw new \InvalidArgumentException('Order does not have an Icepay reference');
        }

        $checkout = $this->client->create()->getCheckout($reference);

        $this->logger->info('Received response for redirect', [
            'response' => ['key' => $checkout->key, 'status' => $checkout->status->toString()],
        ]);

        return $checkout;
    }
}

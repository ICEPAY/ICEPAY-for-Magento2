<?php

declare(strict_types=1);

namespace Icepay\Payment\Gateway\Commands;

use GuzzleHttp\ClientFactory;
use Icepay\Payment\Config;
use Icepay\Payment\Data\PaymentResponseFactory;
use Icepay\Payment\Logger;
use Icepay\Payment\Service\Icepay\RedirectUrl;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote\Payment;
use Magento\Sales\Api\Data\OrderInterface;

class OrderCommand implements CommandInterface
{
    public function __construct(
        private readonly Logger $logger,
        private readonly UrlInterface $url,
        private readonly ClientFactory $clientFactory,
        private readonly Config $config,
        private readonly PaymentResponseFactory $paymentResponseFactory,
        private readonly RedirectUrl $redirectUrl,
    ) {}

    /**
     * @param array{amount: float, payment: PaymentInterface|Payment} $commandSubject
     * @return void
     */
    public function execute(array $commandSubject): void
    {
        /** @var PaymentDataObject $paymentDataObject */
        $paymentDataObject = $commandSubject['payment'];
        $payment = $paymentDataObject->getPayment();

        $payment->setIsTransactionPending(true);

        $merchantId = $this->config->getMerchantId();
        $merchantSecret = $this->config->getMerchantSecret();

        /** @var \GuzzleHttp\Client $client */
        $client = $this->clientFactory->create();

        $request = $this->getRequest($payment->getOrder());
        $this->logger->info('Sending request to Icepay', ['request' => $request]);
        $response = $client->post(
            'https://checkout.icepay.com/api/payments',
            [
                'auth' => [$merchantId, $merchantSecret],
                'json' => $request,
            ]
        );

        $responseBody = (string)$response->getBody();

        $this->logger->info('Received response from Icepay', ['response' => $responseBody]);

        $response = $this->paymentResponseFactory->createFromJson($responseBody);

        $payment->setTransactionId($response->key);
        $payment->setAdditionalInformation('icepay_reference', $response->key);
        $payment->setAdditionalInformation('icepay_redirect_url', $response->links->checkout->href);
    }

    private function getRequest(OrderInterface $order): array
    {
        $redirectUrl = $this->redirectUrl->execute($order);
        $webhookUrl = $this->url->getUrl('icepay/webhook/process', ['_secure' => true]);

        $method = PaymentMethod::fromPayment($order->getPayment());

        return [
            'reference' => $order->getIncrementId(),
            'paymentMethod' => [
                'type' => $method->value,
            ],
//            'description' => '',
            'amount' => [
                'value' => $order->getGrandTotal() * 100,
                'currency' => $order->getOrderCurrencyCode(),
            ],
            'redirectUrl' => $redirectUrl,
            'webhookUrl' => $webhookUrl,
            'customer' => [
                'email' => $order->getBillingAddress()->getEmail(),
            ]
        ];
    }
}

<?php

declare(strict_types=1);

namespace Icepay\Payment\Gateway\Commands;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\ClientException;
use Icepay\Payment\Gateway\IcepayClient;
use Icepay\Payment\Logger;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\Data\InvoiceInterface;

class RefundCommand implements CommandInterface
{
    public function __construct(
        private readonly Logger $logger,
        private readonly IcepayClient $client,
    ) {}

    /**
     * @param array{
     *     amount: string,
     *     payment: array{
     *         order: OrderAdapterInterface,
     *         payment: PaymentDataObjectInterface
     *     }
     * } $commandSubject
     */
    public function execute(array $commandSubject): void
    {
        $payment = $commandSubject['payment']->getPayment();
        $invoice = $payment->getCreditmemo()->getInvoice();
        $reference = $payment->getAdditionalInformation('icepay_reference');

        try {
            $response = $this->client->create()->post(
                'payments/' . $reference . '/refund',
                [
                    'json' => $this->getRequest($commandSubject['amount'], $invoice),
                ]
            );
        } catch (ClientException $exception) {
            $contents = $exception->getResponse()->getBody()->getContents();
            $json = json_decode($contents, true);
            throw new LocalizedException(__('Unable to refund the payment: %1', $json['message']));
        }

        $json = json_decode((string)$response->getBody(), true);
        $payment->setTransactionId($json['key']);
    }

    private function getRequest(string $amount, InvoiceInterface $invoice): array
    {
        $amount = (int)round(floatval($amount) * 100, 0);
        $description = __('Refund for invoice #%1', $invoice->getIncrementId());

        return [
            'reference' => $description,
            'description' => $description,
            'amount' => [
                'value' => $amount,
            ],
        ];
    }
}

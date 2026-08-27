<?php

declare(strict_types=1);

namespace Icepay\Payment\Gateway\Commands;

use ICEPAY\Checkout\Exceptions\ApiException;
use ICEPAY\Checkout\Models\Request\Refund as RefundRequest;
use Icepay\Payment\Gateway\IcepayClient;
use Icepay\Payment\Logger;
use Icepay\Payment\Service\Icepay\AmountInCents;
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
        private readonly AmountInCents $amountInCents,
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

        $request = $this->getRequest((string) $commandSubject['amount'], $invoice);
        $this->logger->info('Sending refund request to Icepay', ['request' => $request->jsonSerialize()]);

        try {
            $response = $this->client->create()->refund($request, (string) $reference);
        } catch (ApiException $exception) {
            throw new LocalizedException(__('Unable to refund the payment: %1', $exception->getMessage()));
        }

        $payment->setTransactionId($response->key);
    }

    private function getRequest(string $amount, InvoiceInterface $invoice): RefundRequest
    {
        $description = (string) __('Refund for invoice #%1', $invoice->getIncrementId());

        return new RefundRequest(
            $description,
            $this->amountInCents->fromMajorUnits((float) $amount),
            $description
        );
    }
}

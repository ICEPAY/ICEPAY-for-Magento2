<?php

declare(strict_types=1);

namespace Icepay\Payment\Gateway\Commands;

use ICEPAY\Checkout\Exceptions\ApiException;
use ICEPAY\Checkout\Models\Amount;
use ICEPAY\Checkout\Models\Links;
use ICEPAY\Checkout\Models\Request\Checkout as CheckoutRequest;
use Icepay\Payment\Gateway\IcepayClient;
use Icepay\Payment\Logger;
use Icepay\Payment\Service\Icepay\AmountInCents;
use Icepay\Payment\Service\Icepay\RedirectUrl;
use Magento\Framework\Exception\LocalizedException;
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
        private readonly IcepayClient $client,
        private readonly AmountInCents $amountInCents,
        private readonly RedirectUrl $redirectUrl,
    ) {}

    /**
     * @param array{amount: float, payment: PaymentInterface|Payment} $commandSubject
     * @return void
     */
    public function execute(array $commandSubject): void
    {
        try {
            $this->createIcepayCheckout($commandSubject);
        } catch (ApiException $exception) {
            $this->logger->error($exception->getMessage());

            throw new LocalizedException(__($exception->getMessage()), $exception);
        }
    }

    public function createIcepayCheckout(array $commandSubject): void
    {
        /** @var PaymentDataObject $paymentDataObject */
        $paymentDataObject = $commandSubject['payment'];
        $payment = $paymentDataObject->getPayment();

        $payment->setIsTransactionPending(true);

        $request = $this->getRequest($payment->getOrder());
        $this->logger->info('Sending request to Icepay', ['request' => $request->jsonSerialize()]);
        $response = $this->client->create()->createCheckout($request);

        $this->logger->info('Received response from Icepay', [
            'response' => ['key' => $response->key, 'status' => $response->status->toString()],
        ]);

        $payment->setTransactionId($response->key);
        $payment->setAdditionalInformation('icepay_reference', $response->key);
        $payment->setAdditionalInformation('icepay_redirect_url', $this->getRedirectUrl($response->links));
    }

    private function getRedirectUrl(Links $links): string
    {
        return $links->direct ?? $links->checkout;
    }

    private function getRequest(OrderInterface $order): CheckoutRequest
    {
        $request = new CheckoutRequest(
            reference: $order->getIncrementId(),
            amount: new Amount(
                $this->amountInCents->fromMajorUnits((float) $order->getGrandTotal()),
                $order->getOrderCurrencyCode()
            ),
            redirectUrl: $this->redirectUrl->execute($order),
            webhookUrl: $this->url->getUrl('icepay/webhook/process', ['_secure' => true]),
            paymentMethod: PaymentMethod::fromPayment($order->getPayment())->value,
        );

        return $request->withCustomerEmail((string) $order->getBillingAddress()->getEmail());
    }
}

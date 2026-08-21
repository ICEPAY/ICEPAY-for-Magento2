<?php

declare(strict_types=1);

namespace Icepay\Payment\Service\Icepay;

use ICEPAY\Checkout\Models\Response\Checkout;
use ICEPAY\Checkout\Models\Status;
use Icepay\Payment\Service\GetOrderByIncrementId;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;

class ProcessResponse
{
    public function __construct(
        private readonly GetOrderByIncrementId $getOrderByIncrementId,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly LoggerInterface $logger,
    ) {}

    public function execute(Checkout $checkout): void
    {
        $order = $this->getOrderByIncrementId->execute($checkout->reference);

        if ($checkout->status === Status::completed) {
            $this->handleCompleteResponse($order, $checkout);
            return;
        }

        if ($checkout->status === Status::unknown) {
            $this->logger->warning('Icepay sent a status this module does not recognize, ignoring it', [
                'reference' => $checkout->reference,
            ]);
            return;
        }

        $order->setState(Order::STATE_PENDING_PAYMENT);
        $order->cancel();
        $this->orderRepository->save($order);
    }

    public function handleCompleteResponse(OrderInterface $order, Checkout $checkout): void
    {
        if (in_array($order->getState(), [Order::STATE_PROCESSING, Order::STATE_COMPLETE])) {
            return;
        }

        $order->setState(Order::STATE_PROCESSING);

        /** @var PaymentInterface|Payment $payment */
        $payment = $order->getPayment();
        $payment->setTransactionId($checkout->key);
        $payment->setCurrencyCode(strtoupper((string) $checkout->amount->currency));
        $payment->setIsTransactionClosed(true);
        $payment->registerCaptureNotification($checkout->amount->value / 100, true);

        $this->orderRepository->save($order);
    }
}

<?php

declare(strict_types=1);

namespace Icepay\Payment\Service\Icepay;

use Icepay\Payment\Data\PaymentResponse;
use Icepay\Payment\Data\Status;
use Icepay\Payment\Service\GetOrderByIncrementId;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

class ProcessResponse
{
    public function __construct(
        private readonly GetOrderByIncrementId $getOrderByIncrementId,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {}

    public function execute(PaymentResponse $paymentResponse): void
    {
        $order = $this->getOrderByIncrementId->execute($paymentResponse->reference);

        if ($paymentResponse->status === Status::COMPLETED) {
            $this->handleCompleteResponse($order, $paymentResponse);
            return;
        }

        $order->setState(Order::STATE_PENDING_PAYMENT);
        $order->cancel();
        $this->orderRepository->save($order);
    }

    public function handleCompleteResponse(OrderInterface $order, PaymentResponse $paymentResponse): void
    {
        if (in_array($order->getState(), [Order::STATE_PROCESSING, Order::STATE_COMPLETE])) {
            return;
        }

        $order->setState(Order::STATE_PROCESSING);

        /** @var PaymentInterface|Payment $payment */
        $payment = $order->getPayment();
        $payment->setTransactionId($paymentResponse->key);
        $payment->setCurrencyCode(strtoupper($paymentResponse->amount->currency));
        $payment->setIsTransactionClosed(true);
        $payment->registerCaptureNotification($paymentResponse->amount->value, true);

        $this->orderRepository->save($order);
    }
}

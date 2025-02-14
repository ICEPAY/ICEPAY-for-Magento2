<?php

declare(strict_types=1);

namespace Icepay\Payment\Controller\Payment;

use Icepay\Payment\Data\Status;
use Icepay\Payment\Icepay\GetCheckoutByOrder;
use Icepay\Payment\Service\GetOrderByIncrementId;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NotFoundException;

class Process implements HttpGetActionInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly EncryptorInterface $encryptor,
        private readonly GetOrderByIncrementId $getOrderByIncrementId,
        private readonly ResultFactory $resultFactory,
        private readonly Session $checkoutSession,
        private readonly GetCheckoutByOrder $getCheckoutByOrder,
    ) {}

    public function execute()
    {
        $orderIdParam = $this->request->getParam('order_id');
        if ($orderIdParam === null) {
            throw new NotFoundException(__('Order ID not found'));
        }

        $incrementId = $this->encryptor->decrypt(base64_decode($orderIdParam));
        $order = $this->getOrderByIncrementId->execute($incrementId);

        $result = $this->getCheckoutByOrder->execute($order);

        $response = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($result->status != Status::COMPLETED) {
            $this->checkoutSession->restoreQuote();

            return $response->setUrl('/checkout/cart');
        }

        $this->checkoutSession->setLastRealOrder($order)
            ->setLastOrderId($order->getId())
            ->setLastQuoteId($order->getQuoteId())
            ->setLastSuccessQuoteId($order->getQuoteId())
        ;

        return $response
            ->setUrl('/checkout/onepage/success');
    }
}

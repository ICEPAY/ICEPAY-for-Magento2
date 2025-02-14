<?php

declare(strict_types=1);

namespace Icepay\Payment\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Redirect implements HttpGetActionInterface
{
    public function __construct(
        private readonly Session $checkoutSession,
        private readonly ResultFactory $resultFactory,
    ) {}


    /**
     * This class redirects the user form Magento to Icepay
     */
    public function execute(): \Magento\Framework\Controller\Result\Redirect
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $payment = $order->getPayment();

        /** @var string $redirectUrl */
        $redirectUrl = $payment->getAdditionalInformation('icepay_redirect_url');

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
            ->setUrl($redirectUrl);
    }
}

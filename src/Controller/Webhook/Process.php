<?php

declare(strict_types=1);

namespace Icepay\Payment\Controller\Webhook;

use ICEPAY\Checkout\Exceptions\InvalidSignature;
use Icepay\Payment\Logger;
use Icepay\Payment\Service\Icepay\HandlePostback;
use Icepay\Payment\Service\Icepay\ProcessResponse;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

class Process implements HttpGetActionInterface, HttpPostActionInterface, CsrfAwareActionInterface
{
    private const SIGNATURE_HEADER = 'ICEPAY-Signature';

    public function __construct(
        private readonly RequestInterface $request,
        private readonly ResultFactory $resultFactory,
        private readonly HandlePostback $handlePostback,
        private readonly ProcessResponse $processResponse,
        private readonly Logger $logger
    ) {}

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        $body = $this->request->getContent();

        try {
            $checkout = $this->handlePostback->execute(
                $body,
                (string) $this->request->getHeader(self::SIGNATURE_HEADER)
            );
        } catch (InvalidSignature) {
            $result->setHttpResponseCode(403);
            return $result;
        }

        $this->logger->info('Received webhook', ['body' => $body]);
        $this->processResponse->execute($checkout);

        $result->setContents('OK');

        return $result;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}

<?php

declare(strict_types=1);

namespace Icepay\Payment\Controller\Webhook;

use Icepay\Payment\Config;
use Icepay\Payment\Data\PaymentResponseFactory;
use Icepay\Payment\Logger;
use Icepay\Payment\Service\Icepay\IsValidRequest;
use Icepay\Payment\Service\Icepay\ProcessResponse;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

class Process implements HttpGetActionInterface, HttpPostActionInterface, CsrfAwareActionInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly ResultFactory $resultFactory,
        private readonly PaymentResponseFactory $paymentResponseFactory,
        private readonly IsValidRequest $isValidRequest,
        private readonly ProcessResponse $processResponse,
        private readonly Logger $logger
    ) {}

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        if (!$this->isValidRequest->execute()) {
            $result->setHttpResponseCode(403);
            return $result;
        }

        $body = $this->request->getContent();
        $this->logger->info('Received webhook', ['body' => $body]);
        $response = $this->paymentResponseFactory->createFromJson($body);

        $this->processResponse->execute($response);

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

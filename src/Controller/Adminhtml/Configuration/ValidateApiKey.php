<?php

declare(strict_types=1);

namespace Icepay\Payment\Controller\Adminhtml\Configuration;

use ICEPAY\Checkout\Exceptions\ApiException;
use ICEPAY\Checkout\Models\Response\PaymentMethod;
use Icepay\Payment\Config;
use Icepay\Payment\Gateway\IcepayClient;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class ValidateApiKey implements HttpPostActionInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly JsonFactory $resultJsonFactory,
        private readonly Config $config,
        private readonly IcepayClient $client,
    ) {}

    public function execute()
    {
        $response = $this->resultJsonFactory->create();
        $merchantId = $this->getMerchantValue('id', fn () => $this->config->getMerchantId());
        $merchantSecret = $this->getMerchantValue('secret', fn () => $this->config->getMerchantSecret());

        if ($merchantId === null || $merchantSecret === null) {
            return $response->setData([
                'success' => false,
                'error' => __('Please set the Merchant ID and Merchant Secret before trying again.'),
            ]);
        }

        try {
            $methods = $this->client->createWithCredentials($merchantId, $merchantSecret)->getPaymentMethods();
        } catch (ApiException $exception) {
            return $response->setData([
                'success' => false,
                'error' => $this->isCausedByInvalidCredentials($exception)
                    ? __('Invalid Merchant ID or Merchant Secret.')
                    : $exception->getMessage(),
            ]);
        } catch (\JsonException $exception) {
            return $response->setData([
                'success' => false,
                'error' => $exception->getMessage(),
            ]);
        }

        usort($methods, function (PaymentMethod $a, PaymentMethod $b) {
            return strcasecmp($a->description, $b->description);
        });

        return $response->setData([
            'success' => true,
            'methods' => array_map(static fn (PaymentMethod $method): array => [
                'id' => $method->id,
                'logo' => $method->logo,
                'description' => $method->description,
            ], $methods),
        ]);
    }

    private function isCausedByInvalidCredentials(ApiException $exception): bool
    {
        return $exception->getCode() >= 400 && $exception->getCode() < 500;
    }

    private function getMerchantValue(string $name, callable $defaultValue): ?string
    {
        $value = $this->request->getParam('merchant' . ucfirst($name));
        if ($value !== '' && $value !== '******') {
            return $value;
        }

        return $defaultValue();
    }
}

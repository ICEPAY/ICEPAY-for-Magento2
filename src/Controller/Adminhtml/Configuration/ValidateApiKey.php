<?php

declare(strict_types=1);

namespace Icepay\Payment\Controller\Adminhtml\Configuration;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\ClientException;
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
        private readonly ClientFactory $clientFactory,
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
            $serverResponse = $this->client->create()->get(
                'payments/methods',
                [
                    'auth' => [$merchantId, $merchantSecret],
                ]
            );
        } catch (ClientException) {
            return $response->setData([
                'success' => false,
                'error' => __('Invalid Merchant ID or Merchant Secret.'),
            ]);
        } catch (\Exception $exception) {
            return $response->setData([
                'success' => false,
                'error' => $exception->getMessage(),
            ]);
        }

        $contents = json_decode($serverResponse->getBody()->getContents());

        usort($contents, function ($a, $b) {
            return strcasecmp($a->description, $b->description);
        });

        return $response->setData([
            'success' => true,
            'methods' => $contents,
        ]);
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

<?php

declare(strict_types=1);

namespace Icepay\Payment\Service\Icepay;

use ICEPAY\Checkout\Exceptions\InvalidSignature;
use ICEPAY\Checkout\Models\Response\Checkout;
use ICEPAY\Checkout\PostbackHandler;
use Icepay\Payment\Config;

final class HandlePostback
{
    public function __construct(
        private readonly Config $config,
    ) {}

    public function execute(string $body, string $signature): Checkout
    {
        $merchantSecret = $this->config->getMerchantSecret();
        if ($merchantSecret === null || $merchantSecret === '') {
            throw new InvalidSignature('No merchant secret is configured, unable to verify the postback signature.');
        }

        return (new PostbackHandler($merchantSecret))->handle($body, $signature);
    }
}

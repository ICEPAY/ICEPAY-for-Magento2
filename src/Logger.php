<?php

declare(strict_types=1);

namespace Icepay\Payment;

use DateTimeZone;
use Magento\Framework\Logger\Monolog;
use Monolog\DateTimeImmutable;

class Logger extends Monolog
{
    public function __construct(
        private readonly Config $config,
        string $name,
        array $handlers = [],
        array $processors = [],
        ?DateTimeZone $timezone = null
    ) {
        parent::__construct($name, $handlers, $processors, $timezone);
    }


    public function addRecord(
        int $level,
        string $message,
        array $context = [],
        ?DateTimeImmutable $datetime = null
    ): bool {
        if (!$this->config->isDebugEnabled()) {
            return false;
        }

        return parent::addRecord($level, $message, $context, $datetime);
    }
}

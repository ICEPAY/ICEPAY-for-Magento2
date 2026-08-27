<?php

declare(strict_types=1);

namespace Icepay\Payment\Test\Fakes;

use Psr\Log\AbstractLogger;

class FakeLogger extends AbstractLogger
{
    private array $records = [];

    public function log($level, $message, array $context = []): void
    {
        $this->records[] = ['level' => (string)$level, 'message' => (string)$message, 'context' => $context];
    }

    public function errorMessages(): array
    {
        return array_column(
            array_values(array_filter(
                $this->records,
                static fn (array $record): bool => $record['level'] === 'error'
            )),
            'message'
        );
    }
}

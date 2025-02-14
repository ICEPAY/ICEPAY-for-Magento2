<?php

declare(strict_types=1);

namespace Icepay\Payment\Data;

use DateTimeImmutable;

class PaymentResponse
{
    public function __construct(
        public readonly string $key,
        public readonly Status $status,
        public readonly Amount $amount,
        public readonly PaymentMethodCollection $paymentMethod,
        public readonly ?string $description,
        public readonly string $reference,
        public readonly bool $isTest,
        public readonly array $refunds,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $expiresAt,
        public readonly DateTimeImmutable $updatedAt,
        public readonly array $meta,
        public readonly LinkCollection $links,
    ) {}
}

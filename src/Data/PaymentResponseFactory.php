<?php

declare(strict_types=1);

namespace Icepay\Payment\Data;

use Magento\Framework\ObjectManager\ObjectManager;

class PaymentResponseFactory
{
    public function __construct(
        private readonly ObjectManager $objectManager,
        private readonly AmountFactory $amountFactory,
        private readonly LinkCollectionFactory $linkCollectionFactory,
        private readonly LinkFactory $linkFactory,
    ) {}

    public function createFromJson(string $json): PaymentResponse
    {
        $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        $amount = $data['amount']['value'];
        if ($amount != 0) {
            $amount = $amount / 100;
        }

        return $this->objectManager->create(
            PaymentResponse::class,
            [
                'key' => $data['key'],
                'status' => Status::from($data['status']),
                'amount' => $this->amountFactory->create([
                    'value' => $amount,
                    'currency' => $data['amount']['currency']
                ]),
                'paymentMethod' => $this->objectManager->create(
                    PaymentMethodCollection::class,
                    ['paymentMethods' => $data['paymentMethod'] ?? []]
                ),
                'description' => $data['description'],
                'reference' => $data['reference'],
                'isTest' => $data['isTest'],
                'refunds' => $data['refunds'],
                'createdAt' => new \DateTimeImmutable($data['createdAt']),
                'expiresAt' => new \DateTimeImmutable($data['expiresAt']),
                'updatedAt' => new \DateTimeImmutable($data['updatedAt']),
                'meta' => $data['meta'],
                'links' => $this->linkCollectionFactory->create([
                    'checkout' => $this->linkFactory->create(['href' => $data['links']['checkout']]),
                    'documentation' => $this->linkFactory->create(['href' => $data['links']['documentation']]),
                ])
            ]
        );
    }
}

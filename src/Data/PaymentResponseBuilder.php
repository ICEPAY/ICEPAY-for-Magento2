<?php

declare(strict_types=1);

namespace Icepay\Payment\Data;


class PaymentResponseBuilder
{
    public function __construct(
        private readonly PaymentResponseFactory $paymentResponseFactory,
        private readonly LinkCollectionFactory $linkCollectionFactory,
        private readonly AmountFactory $amountFactory,
        private readonly LinkFactory $linkFactory,
        private readonly PaymentMethodCollectionFactory $paymentMethodCollectionFactory,
    ) {}

    public function createFromJson(string $json): PaymentResponse
    {
        $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        $amount = $data['amount']['value'];
        if ($amount != 0) {
            $amount = $amount / 100;
        }

        return $this->paymentResponseFactory->create([
            'key' => $data['key'],
            'status' => Status::from($data['status']),
            'amount' => $this->amountFactory->create([
                'value' => $amount,
                'currency' => $data['amount']['currency']
            ]),
            'paymentMethod' => $this->paymentMethodCollectionFactory->create([
                'paymentMethods' => $data['paymentMethod'] ?? []
            ]),
            'description' => $data['description'],
            'reference' => $data['reference'],
            'isTest' => $data['isTest'],
            'refunds' => $data['refunds'],
            'createdAt' => new \DateTimeImmutable($data['createdAt']),
            'expiresAt' => new \DateTimeImmutable($data['expiresAt']),
            'updatedAt' => new \DateTimeImmutable($data['updatedAt']),
            'meta' => $data['meta'],
            'links' => $this->getLinks($data['links'])
        ]);
    }

    public function getLinks(array $links): LinkCollection
    {
        $data = [
            'direct' => null,
            'checkout' => $this->linkFactory->create(['href' => $links['checkout']]),
            'documentation' => $this->linkFactory->create(['href' => $links['documentation']]),
        ];

        if (array_key_exists('direct', $links)) {
            $data['direct'] = $this->linkFactory->create(['href' => $links['direct']]);
        }

        return $this->linkCollectionFactory->create($data);
    }
}

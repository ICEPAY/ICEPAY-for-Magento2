<?php

declare(strict_types=1);

namespace Icepay\Payment\Service;

use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class GetOrderByIncrementId
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
    ) {}

    public function execute(string $incrementId): OrderInterface
    {
        $criteria = $this->searchCriteriaBuilderFactory->create();
        $criteria->addFilter('increment_id', $incrementId, 'eq');

        $orders = $this->orderRepository->getList($criteria->create())->getItems();
        $order = array_shift($orders);

        if ($order === null || $order->getId() === null) {
            throw new NotFoundException(__('Order not found'));
        }

        return $order;
    }
}

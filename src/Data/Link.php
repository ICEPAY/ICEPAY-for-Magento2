<?php

declare(strict_types=1);

namespace Icepay\Payment\Data;

class Link
{
    public function __construct(
        public readonly string $href,
    ) {
        if (!filter_var($this->href, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL');
        }
    }
}

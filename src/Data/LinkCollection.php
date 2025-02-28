<?php

declare(strict_types=1);

namespace Icepay\Payment\Data;

class LinkCollection
{
    public function __construct(
        public readonly Link $checkout,
        public readonly Link $documentation,
        public readonly ?Link $direct,
    ) {}
}

<?php

declare(strict_types=1);

namespace Icepay\Payment\Data;

enum Status: string
{
    case STARTED = 'started';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
    case PENDING = 'pending';
}

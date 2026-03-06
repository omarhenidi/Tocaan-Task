<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class PaymentStatus extends Enum
{
    public const Pending = 'pending';
    public const Successful = 'successful';
    public const Failed = 'failed';
    public const UnPaid = 'un_paid';
    public const Paid = 'paid';
}

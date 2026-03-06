<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class PaymentMethod extends Enum
{
    public const CreditCard = 'credit_card';
    public const PayPal = 'paypal';
    public const Cash = 'cash';
}

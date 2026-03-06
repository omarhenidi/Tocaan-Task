<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class OrderStatus extends Enum
{
    public const Pending = 'pending';
    public const Confirmed = 'confirmed';
    public const Processing = 'processing';
    public const Departed = 'departed';
    public const OnDelivery = 'on_delivery';
    public const ArrivedDestination = 'arrived_destination';
    public const Delivered = 'delivered';
    public const Cancelled = 'cancelled';
}

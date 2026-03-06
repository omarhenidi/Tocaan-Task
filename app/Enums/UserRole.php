<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class UserRole extends Enum
{
    public const Admin =   'admin';

    public const Client = 'client';
}

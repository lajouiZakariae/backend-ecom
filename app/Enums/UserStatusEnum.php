<?php

namespace App\Enums;

use App\Traits\BaseEnum;

enum UserStatusEnum: string
{
    use BaseEnum;

    case ACTIVE = 'active';

    case BANNED = 'banned';
}

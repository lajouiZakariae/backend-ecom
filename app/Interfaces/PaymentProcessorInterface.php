<?php

namespace App\Interfaces;

use App\Models\User;

interface PaymentProcessorInterface
{
    public function payAmountForUser(User $user, float $amount, array $options = []): mixed;
}

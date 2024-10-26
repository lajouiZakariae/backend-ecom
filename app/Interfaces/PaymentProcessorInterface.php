<?php

namespace App\Interfaces;

use App\Models\User;

interface PaymentProcessorInterface
{
    public function payPlanPriceForUser(User $user, float $amount, array $options = []): mixed;
}

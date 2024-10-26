<?php

namespace App\Services\Payment;

use App\Interfaces\PaymentProcessorInterface;
use App\Models\User;

class StripePaymentProcessor implements PaymentProcessorInterface
{
    public function payPlanPriceForUser(User $user, float $amount, array $options = []): mixed
    {
        $user->createOrGetStripeCustomer();

        // dd($user->s);

        $paymentMethod = $user->addPaymentMethod($options['payment_method']);

        $paymentIntent = $user->pay($amount, [
            'payment_method' => $paymentMethod->id,
            'confirm' => true,
            'off_session' => true
        ]);

        return $paymentIntent;
    }
}
